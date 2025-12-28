<?php
/**
 * MCP Transport class (STDIO).
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Core;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WordpressMcpLite\Utils\ErrorHandler;

/**
 * Class McpTransport
 *
 * Handles STDIO transport for MCP protocol using WordPress REST API.
 */
class McpTransport {

	/**
	 * The MCP server instance.
	 *
	 * @var McpServer
	 */
	private McpServer $mcp;

	/**
	 * Constructor.
	 *
	 * @param McpServer $mcp The MCP server instance.
	 */
	public function __construct(McpServer $mcp) {
		$this->mcp = $mcp;
		add_action('rest_api_init', array($this, 'register_routes'));
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'wp/v2',
			'/wpmcp',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'handle_request'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);
	}

	/**
	 * Check if user has permission to access MCP API.
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission() {
		if (!is_user_logged_in()) {
			return new WP_Error(
				'rest_forbidden',
				'You must be logged in to access the MCP API.',
				array('status' => 401)
			);
		}
		return true;
	}

	/**
	 * Handle MCP request.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_request(WP_REST_Request $request) {
		$message = $request->get_json_params();

		if (empty($message) || !isset($message['method'])) {
			return new WP_Error(
				'invalid_request',
				'Invalid request: method parameter is required.',
				array('status' => 400)
			);
		}

		$method = $message['method'];
		$params = $message['params'] ?? $message;

		try {
			$result = $this->route_request($method, $params);

			if (isset($result['error'])) {
				return $this->format_error_response($result);
			}

			return $this->format_success_response($result);
		} catch (\Throwable $e) {
			ErrorHandler::log_error('Request handling error: ' . $e->getMessage());
			return new WP_Error(
				'handler_error',
				'Handler error occurred: ' . $e->getMessage(),
				array('status' => 500)
			);
		}
	}

	/**
	 * Route request to appropriate handler.
	 *
	 * @param string $method The MCP method name.
	 * @param array  $params The method parameters.
	 * @return array
	 */
	private function route_request(string $method, array $params): array {
		switch ($method) {
			case 'initialize':
			case 'init':
				return $this->handle_initialize();

			case 'tools/list':
				return $this->handle_tools_list();

			case 'tools/call':
				return $this->handle_tools_call($params);

			case 'resources/list':
				return $this->handle_resources_list();

			case 'resources/read':
				return $this->handle_resources_read($params);

			case 'prompts/list':
				return $this->handle_prompts_list();

			case 'prompts/get':
				return $this->handle_prompts_get($params);

			case 'ping':
				return array('result' => array('pong' => true));

			default:
				return array(
					'error' => array(
						'code'    => 'invalid_method',
						'message' => 'Invalid method: ' . $method,
						'data'    => array('status' => 400),
					),
				);
		}
	}

	/**
	 * Handle initialize request.
	 *
	 * @return array
	 */
	private function handle_initialize(): array {
		return array(
			'result' => array(
				'protocolVersion' => '2024-11-05',
				'capabilities'    => array(
					'tools'     => array(),
					'resources' => array(),
					'prompts'   => array(),
				),
				'serverInfo'      => array(
					'name'    => 'WordPress MCP Lite',
					'version' => WORDPRESS_MCP_LITE_VERSION,
				),
			),
		);
	}

	/**
	 * Handle tools/list request.
	 *
	 * @return array
	 */
	private function handle_tools_list(): array {
		$tools = $this->mcp->get_tools();

		return array(
			'result' => array(
				'tools' => $tools,
			),
		);
	}

	/**
	 * Handle tools/call request.
	 *
	 * @param array $params The request parameters.
	 * @return array
	 */
	private function handle_tools_call(array $params): array {
		if (!isset($params['name'])) {
			return array(
				'error' => array(
					'code'    => 'invalid_params',
					'message' => 'Tool name is required',
					'data'    => array('status' => 400),
				),
			);
		}

		$tool_name = $params['name'];
		$arguments = $params['arguments'] ?? array();

		$tool = $this->mcp->get_tool_by_name($tool_name);
		if (!$tool) {
			return array(
				'error' => array(
					'code'    => 'tool_not_found',
					'message' => 'Tool not found: ' . $tool_name,
					'data'    => array('status' => 404),
				),
			);
		}

		$callbacks = $this->mcp->get_tools_callbacks();
		if (!isset($callbacks[$tool_name])) {
			return array(
				'error' => array(
					'code'    => 'callback_not_found',
					'message' => 'Callback not found for tool: ' . $tool_name,
					'data'    => array('status' => 500),
				),
			);
		}

		$callback_data = $callbacks[$tool_name];
		$callback = $callback_data['callback'];
		$permission_callback = $callback_data['permission_callback'];

		// Check permissions.
		if (is_callable($permission_callback) && !call_user_func($permission_callback)) {
			return array(
				'error' => array(
					'code'    => 'permission_denied',
					'message' => 'You do not have permission to call this tool',
					'data'    => array('status' => 403),
				),
			);
		}

		// Check if using REST alias.
		if (isset($callback_data['rest_alias'])) {
			return $this->handle_rest_alias_call($callback_data['rest_alias'], $arguments);
		}

		// Use custom callback.
		if (!is_callable($callback)) {
			return array(
				'error' => array(
					'code'    => 'invalid_callback',
					'message' => 'Tool callback is not callable',
					'data'    => array('status' => 500),
				),
			);
		}

		try {
			$result = call_user_func($callback, $arguments);
			return array('result' => array('content' => $result));
		} catch (\Throwable $e) {
			ErrorHandler::log_error("Tool execution error for {$tool_name}: " . $e->getMessage());
			return array(
				'error' => array(
					'code'    => 'tool_execution_error',
					'message' => 'Tool execution failed: ' . $e->getMessage(),
					'data'    => array('status' => 500),
				),
			);
		}
	}

	/**
	 * Handle REST alias call.
	 *
	 * @param array $rest_alias The REST alias configuration.
	 * @param array $arguments The tool arguments.
	 * @return array
	 */
	private function handle_rest_alias_call(array $rest_alias, array $arguments): array {
		$route = $rest_alias['route'];
		$method = $rest_alias['method'];

		// Get REST server and routes.
		$server = rest_get_server();
		$routes = $server->get_routes();
		$rest_route = $routes[$route] ?? null;

		if (!$rest_route) {
			return array(
				'error' => array(
					'code'    => 'route_not_found',
					'message' => 'REST route not found: ' . $route,
					'data'    => array('status' => 404),
				),
			);
		}

		// Find the matching endpoint.
		$endpoint = null;
		foreach ($rest_route as $ep) {
			if (isset($ep['methods'][$method]) && true === $ep['methods'][$method]) {
				$endpoint = $ep;
				break;
			}
		}

		if (!$endpoint) {
			return array(
				'error' => array(
					'code'    => 'method_not_found',
					'message' => 'Method not found: ' . $method,
					'data'    => array('status' => 404),
				),
			);
		}

		// Build the request URL with path parameters.
		$request_url = $route;
		if (preg_match_all('/\(?P<(\w+)>[^)]+\)/', $route, $matches)) {
			foreach ($matches[1] as $param_name) {
				if (isset($arguments[$param_name])) {
					$request_url = str_replace("(?P<{$param_name}>[\\d]+)", (string) $arguments[$param_name], $request_url);
					unset($arguments[$param_name]);
				}
			}
		}

		// Create the REST request.
		$request = new WP_REST_Request($method, $request_url);

		// Set all parameters.
		foreach ($arguments as $key => $value) {
			$request->set_param($key, $value);
		}

		// For POST/PUT/PATCH, set the body.
		if (in_array($method, array('POST', 'PUT', 'PATCH'), true)) {
			$request->set_body(wp_json_encode($arguments));
			$request->add_header('Content-Type', 'application/json');
		}

		// Execute using the callback directly.
		$callback = $endpoint['callback'];
		$permission_callback = $endpoint['permission_callback'];

		// Check permissions.
		if (is_callable($permission_callback)) {
			$allowed = call_user_func($permission_callback, $request);
			if (is_wp_error($allowed)) {
				return array(
					'error' => array(
						'code'    => $allowed->get_error_code(),
						'message' => $allowed->get_error_message(),
						'data'    => $allowed->get_error_data(),
					),
				);
			}
			if (false === $allowed) {
				return array(
					'error' => array(
						'code'    => 'rest_forbidden',
						'message' => 'Sorry, you are not allowed to do that.',
						'data'    => array('status' => 403),
					),
				);
			}
		}

		// Execute the callback.
		try {
			$response = call_user_func($callback, $request);

			if (is_wp_error($response)) {
				return array(
					'error' => array(
						'code'    => $response->get_error_code(),
						'message' => $response->get_error_message(),
						'data'    => $response->get_error_data(),
					),
				);
			}

			// Ensure response is a WP_REST_Response.
			if (!($response instanceof WP_REST_Response)) {
				$response = rest_ensure_response($response);
			}

			$data = $response->get_data();
			return array('result' => array('content' => $data));
		} catch (\Throwable $e) {
			ErrorHandler::log_error('REST callback error: ' . $e->getMessage());
			return array(
				'error' => array(
					'code'    => 'rest_callback_error',
					'message' => 'REST callback failed: ' . $e->getMessage(),
					'data'    => array('status' => 500),
				),
			);
		}
	}

	/**
	 * Handle resources/list request.
	 *
	 * @return array
	 */
	private function handle_resources_list(): array {
		$resources = $this->mcp->get_resources();

		return array(
			'result' => array(
				'resources' => array_values($resources),
			),
		);
	}

	/**
	 * Handle resources/read request.
	 *
	 * @param array $params The request parameters.
	 * @return array
	 */
	private function handle_resources_read(array $params): array {
		if (!isset($params['uri'])) {
			return array(
				'error' => array(
					'code'    => 'invalid_params',
					'message' => 'Resource URI is required',
					'data'    => array('status' => 400),
				),
			);
		}

		$uri = $params['uri'];
		$callbacks = $this->mcp->get_resource_callbacks();

		if (!isset($callbacks[$uri])) {
			return array(
				'error' => array(
					'code'    => 'resource_not_found',
					'message' => 'Resource not found: ' . $uri,
					'data'    => array('status' => 404),
				),
			);
		}

		try {
			$callback = $callbacks[$uri];
			$content = call_user_func($callback);

			return array(
				'result' => array(
					'contents' => array(
						array(
							'uri'      => $uri,
							'mimeType' => 'text/plain',
							'text'     => is_string($content) ? $content : wp_json_encode($content),
						),
					),
				),
			);
		} catch (\Throwable $e) {
			ErrorHandler::log_error("Resource read error for {$uri}: " . $e->getMessage());
			return array(
				'error' => array(
					'code'    => 'resource_read_error',
					'message' => 'Failed to read resource: ' . $e->getMessage(),
					'data'    => array('status' => 500),
				),
			);
		}
	}

	/**
	 * Handle prompts/list request.
	 *
	 * @return array
	 */
	private function handle_prompts_list(): array {
		$prompts = $this->mcp->get_prompts();

		return array(
			'result' => array(
				'prompts' => array_values($prompts),
			),
		);
	}

	/**
	 * Handle prompts/get request.
	 *
	 * @param array $params The request parameters.
	 * @return array
	 */
	private function handle_prompts_get(array $params): array {
		if (!isset($params['name'])) {
			return array(
				'error' => array(
					'code'    => 'invalid_params',
					'message' => 'Prompt name is required',
					'data'    => array('status' => 400),
				),
			);
		}

		$name = $params['name'];
		$arguments = $params['arguments'] ?? array();

		$messages = $this->mcp->get_prompt_messages($name);

		if (null === $messages) {
			return array(
				'error' => array(
					'code'    => 'prompt_not_found',
					'message' => 'Prompt not found: ' . $name,
					'data'    => array('status' => 404),
				),
			);
		}

		// Replace arguments in messages.
		$processed_messages = $messages;
		foreach ($arguments as $key => $value) {
			foreach ($processed_messages as &$message) {
				if (isset($message['content'])) {
					$message['content'] = str_replace('{{$' . $key . '}}', $value, $message['content']);
				}
			}
		}

		return array(
			'result' => array(
				'messages' => $processed_messages,
			),
		);
	}

	/**
	 * Format success response.
	 *
	 * @param array $result The result data.
	 * @return WP_REST_Response
	 */
	private function format_success_response(array $result): WP_REST_Response {
		return rest_ensure_response($result);
	}

	/**
	 * Format error response.
	 *
	 * @param array $error The error data.
	 * @return WP_Error
	 */
	private function format_error_response(array $error): WP_Error {
		$error_details = $error['error'] ?? $error;

		return new WP_Error(
			$error_details['code'] ?? 'handler_error',
			$error_details['message'] ?? 'Handler error occurred',
			array('status' => $error_details['data']['status'] ?? 500)
		);
	}
}
