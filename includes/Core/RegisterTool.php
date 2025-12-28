<?php
/**
 * Register MCP Tool class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Core;

use InvalidArgumentException;
use WordpressMcpLite\Utils\ErrorHandler;

/**
 * Class RegisterTool
 *
 * Handles registration of MCP tools with automatic REST API schema generation.
 */
class RegisterTool {

	/**
	 * The tool arguments.
	 *
	 * @var array
	 */
	private array $args;

	/**
	 * Constructor.
	 *
	 * @param array $args The tool arguments.
	 * @throws InvalidArgumentException|\RuntimeException When arguments are invalid.
	 */
	public function __construct(array $args) {
		if (!doing_action('wordpress_mcp_init')) {
			throw new \RuntimeException('RegisterTool can only be used within the wordpress_mcp_init action.');
		}

		$this->args = $args;
		$this->validate_arguments();
		$this->register_tool();
	}

	/**
	 * Register the tool.
	 *
	 * @return void
	 */
	private function register_tool(): void {
		if (!empty($this->args['rest_alias'])) {
			$this->get_args_from_rest_api();
		} else {
			WPMCP_LITE()->register_tool($this->args);
		}
	}

	/**
	 * Generate arguments from REST API endpoint.
	 *
	 * @return void
	 */
	private function get_args_from_rest_api(): void {
		$method = $this->args['rest_alias']['method'];
		$route = $this->args['rest_alias']['route'];

		$routes = rest_get_server()->get_routes();
		$rest_route = $routes[$route] ?? null;

		if (!$rest_route) {
			ErrorHandler::log_error("The route does not exist: {$route} {$method}. Skipping registration.");
			return;
		}

		$rest_api = null;
		foreach ($rest_route as $endpoint) {
			if (isset($endpoint['methods'][$method]) && true === $endpoint['methods'][$method]) {
				$rest_api = $endpoint;
				break;
			}
		}

		if (!$rest_api) {
			ErrorHandler::log_error("The method does not exist: {$method} in {$route}. Skipping registration.");
			return;
		}

		// Convert REST API args to MCP input schema.
		$input_schema = array(
			'type'       => 'object',
			'properties' => array(),
			'required'   => array(),
		);

		foreach ($rest_api['args'] as $arg_name => $arg_schema) {
			if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $arg_name)) {
				ErrorHandler::log_error("Invalid parameter name: {$arg_name} in {$route} {$method}. Skipped.");
				continue;
			}

			$type = $arg_schema['type'];
			if (is_array($type)) {
				$type = reset($type);
			}

			$input_schema['properties'][$arg_name] = array(
				'type'        => $type,
				'description' => $this->get_description_with_fallback($arg_schema['description'] ?? null, $arg_name),
			);

			if (isset($arg_schema['items'])) {
				$input_schema['properties'][$arg_name]['items'] = $arg_schema['items'];
			}

			if (isset($arg_schema['enum'])) {
				$input_schema['properties'][$arg_name]['enum'] = array_values(array_unique($arg_schema['enum'], SORT_REGULAR));
			}

			if (isset($arg_schema['default']) && !empty($arg_schema['default'])) {
				$input_schema['properties'][$arg_name]['default'] = $arg_schema['default'];
			}

			if (isset($arg_schema['format'])) {
				$input_schema['properties'][$arg_name]['format'] = $arg_schema['format'];
			}

			if (isset($arg_schema['minimum'])) {
				$input_schema['properties'][$arg_name]['minimum'] = $arg_schema['minimum'];
			}

			if (isset($arg_schema['maximum'])) {
				$input_schema['properties'][$arg_name]['maximum'] = $arg_schema['maximum'];
			}

			if (isset($arg_schema['required']) && true === $arg_schema['required']) {
				$input_schema['required'][] = $arg_name;
			}
		}

		if (empty($input_schema['properties'])) {
			unset($input_schema['properties']);
		}

		if (empty($input_schema['required'])) {
			unset($input_schema['required']);
		}

		// Apply schema replacements if provided.
		if (isset($this->args['rest_alias']['inputSchemaReplacements'])) {
			$input_schema = $this->apply_modifications($input_schema, $this->args['rest_alias']['inputSchemaReplacements']);
		}

		$this->args['inputSchema'] = $input_schema;
		$this->args['callback'] = $rest_api['callback'];
		$this->args['permission_callback'] = $rest_api['permission_callback'];

		WPMCP_LITE()->register_tool($this->args);
	}

	/**
	 * Apply modifications to input schema.
	 *
	 * @param array $input_schema The original schema.
	 * @param array $modifications The modifications to apply.
	 * @return array
	 */
	private function apply_modifications(array $input_schema, array $modifications): array {
		$result = array_replace_recursive($input_schema, $modifications);

		if (isset($result['required']) && !is_array($result['required'])) {
			$result['required'] = array();
		}

		return $this->remove_null_recursive($result);
	}

	/**
	 * Recursively remove null values from array.
	 *
	 * @param array $data The array to clean.
	 * @return array
	 */
	private function remove_null_recursive(array $data): array {
		foreach ($data as $key => &$value) {
			if (is_array($value)) {
				$value = $this->remove_null_recursive($value);
			} elseif (is_null($value)) {
				unset($data[$key]);
			}
		}
		unset($value);
		return $data;
	}

	/**
	 * Get description with fallback.
	 *
	 * @param string|null $description The original description.
	 * @param string      $arg_name The argument name.
	 * @return string
	 */
	private function get_description_with_fallback(?string $description, string $arg_name): string {
		if (!empty($description)) {
			return $description;
		}

		$tool_name = $this->args['name'] ?? 'tool';
		return "Parameter '{$arg_name}' for {$tool_name}";
	}

	/**
	 * Validate the tool arguments.
	 *
	 * @return void
	 * @throws InvalidArgumentException When validation fails.
	 */
	private function validate_arguments(): void {
		if (!isset($this->args['name'])) {
			throw new InvalidArgumentException('The name is required.');
		}

		if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $this->args['name'])) {
			throw new InvalidArgumentException('The name must be a string between 1 and 64 characters matching pattern ^[a-zA-Z0-9_-]{1,64}$.');
		}

		if (!isset($this->args['description'])) {
			throw new InvalidArgumentException('The description is required.');
		}

		if (!isset($this->args['type'])) {
			throw new InvalidArgumentException('The type is required.');
		}

		$valid_types = array('create', 'read', 'update', 'delete', 'action');
		if (!in_array($this->args['type'], $valid_types, true)) {
			throw new InvalidArgumentException('The type must be one of: ' . implode(', ', $valid_types));
		}

		// If rest_alias is provided, validate it.
		if (isset($this->args['rest_alias'])) {
			$this->validate_rest_alias();
			return;
		}

		// Otherwise, callback and inputSchema are required.
		if (!isset($this->args['callback'])) {
			throw new InvalidArgumentException('The callback is required when not using rest_alias.');
		}

		if (!is_callable($this->args['callback'])) {
			throw new InvalidArgumentException('The callback must be callable.');
		}

		if (empty($this->args['permission_callback'])) {
			throw new InvalidArgumentException('The permission_callback is required.');
		}

		if (!is_callable($this->args['permission_callback'])) {
			throw new InvalidArgumentException('The permission_callback must be callable.');
		}

		$this->validate_input_schema();
	}

	/**
	 * Validate REST alias arguments.
	 *
	 * @return void
	 * @throws InvalidArgumentException When validation fails.
	 */
	private function validate_rest_alias(): void {
		if (!isset($this->args['rest_alias']['route'])) {
			throw new InvalidArgumentException('The route is required in rest_alias.');
		}

		if (!isset($this->args['rest_alias']['method'])) {
			throw new InvalidArgumentException('The method is required in rest_alias.');
		}

		$valid_methods = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE');
		if (!in_array($this->args['rest_alias']['method'], $valid_methods, true)) {
			throw new InvalidArgumentException('The method must be one of: ' . implode(', ', $valid_methods));
		}
	}

	/**
	 * Validate input schema.
	 *
	 * @return void
	 * @throws InvalidArgumentException When validation fails.
	 */
	private function validate_input_schema(): void {
		if (empty($this->args['inputSchema'])) {
			throw new InvalidArgumentException('The inputSchema is required when not using rest_alias.');
		}

		if (!isset($this->args['inputSchema']['type']) || 'object' !== $this->args['inputSchema']['type']) {
			throw new InvalidArgumentException('The inputSchema must be an object type.');
		}

		if (isset($this->args['inputSchema']['properties'])) {
			if (!is_array($this->args['inputSchema']['properties'])) {
				throw new InvalidArgumentException('The inputSchema properties field must be an array.');
			}

			foreach ($this->args['inputSchema']['properties'] as $property_name => $property) {
				if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $property_name)) {
					throw new InvalidArgumentException("Property name '{$property_name}' must match pattern '^[a-zA-Z0-9_-]{1,64}$'.");
				}

				if (!isset($property['type'])) {
					throw new InvalidArgumentException("Property '{$property_name}' must have a type field.");
				}

				$valid_types = array('string', 'number', 'integer', 'boolean', 'array', 'object', 'null');
				if (!in_array($property['type'], $valid_types, true)) {
					throw new InvalidArgumentException("Property '{$property_name}' has invalid type '{$property['type']}'.");
				}

				if ('array' === $property['type'] && !isset($property['items'])) {
					throw new InvalidArgumentException("Array property '{$property_name}' must have an items field.");
				}
			}
		}

		if (isset($this->args['inputSchema']['required'])) {
			if (!is_array($this->args['inputSchema']['required'])) {
				throw new InvalidArgumentException('The required field must be an array.');
			}

			foreach ($this->args['inputSchema']['required'] as $required_property) {
				if (!is_string($required_property) || empty($required_property)) {
					throw new InvalidArgumentException('Required field names must be non-empty strings.');
				}
			}

			if (isset($this->args['inputSchema']['properties'])) {
				foreach ($this->args['inputSchema']['required'] as $required_property) {
					if (!isset($this->args['inputSchema']['properties'][$required_property])) {
						throw new InvalidArgumentException("Required property '{$required_property}' does not exist in properties.");
					}
				}
			}
		}
	}
}
