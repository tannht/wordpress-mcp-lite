<?php
/**
 * MCP Server Registry class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Core;

use WordpressMcpLite\Tools\PostsTools;
use WordpressMcpLite\Tools\PagesTools;
use WordpressMcpLite\Tools\UsersTools;
use WordpressMcpLite\Tools\MediaTools;
use WordpressMcpLite\Tools\SettingsTools;
use WordpressMcpLite\Tools\WooCommerceTools;

/**
 * Class McpServer
 *
 * Main registry singleton for WordPress MCP Lite.
 * Manages tools, resources, and prompts registration.
 */
class McpServer {

	/**
	 * The registered tools.
	 *
	 * @var array
	 */
	private array $tools = array();

	/**
	 * The tool callbacks.
	 *
	 * @var array
	 */
	private array $tools_callbacks = array();

	/**
	 * The registered resources.
	 *
	 * @var array
	 */
	private array $resources = array();

	/**
	 * The resource callbacks.
	 *
	 * @var array
	 */
	private array $resource_callbacks = array();

	/**
	 * The registered prompts.
	 *
	 * @var array
	 */
	private array $prompts = array();

	/**
	 * The prompt messages.
	 *
	 * @var array
	 */
	private array $prompts_messages = array();

	/**
	 * The namespace.
	 *
	 * @var string
	 */
	private string $namespace = 'wpmcp/v1';

	/**
	 * The singleton instance.
	 *
	 * @var ?McpServer
	 */
	private static ?McpServer $instance = null;

	/**
	 * Whether the initialization has been triggered.
	 *
	 * @var bool
	 */
	private bool $has_triggered_init = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_default_tools();

		// Register the MCP assets on rest_api_init.
		add_action('rest_api_init', array($this, 'wordpress_mcp_init'), 20000);
	}

	/**
	 * Initialize the plugin and trigger the init action.
	 */
	public function wordpress_mcp_init(): void {
		if (!$this->has_triggered_init) {
			do_action('wordpress_mcp_init', $this);
			$this->has_triggered_init = true;
		}
	}

	/**
	 * Initialize the default tools.
	 */
	private function init_default_tools(): void {
		new PostsTools();
		new PagesTools();
		new UsersTools();
		new MediaTools();
		new SettingsTools();
		new WooCommerceTools();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return McpServer
	 */
	public static function instance(): McpServer {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register a tool.
	 *
	 * @param array $args The tool arguments.
	 * @return void
	 */
	public function register_tool(array $args): void {
		$name = $args['name'];

		// Store the callback separately.
		$this->tools_callbacks[$name] = array(
			'callback'            => $args['callback'] ?? null,
			'permission_callback' => $args['permission_callback'] ?? '__return_true',
			'rest_alias'          => $args['rest_alias'] ?? null,
		);

		// Remove callback from args for storage.
		unset($args['callback']);
		unset($args['permission_callback']);
		unset($args['rest_alias']);

		$this->tools[] = $args;
	}

	/**
	 * Register a resource.
	 *
	 * @param array $args The resource arguments.
	 * @return void
	 */
	public function register_resource(array $args): void {
		$uri = $args['uri'];
		$this->resources[$uri] = $args;
	}

	/**
	 * Register a resource callback.
	 *
	 * @param string   $uri The resource URI.
	 * @param callable $callback The callback function.
	 * @return void
	 */
	public function register_resource_callback(string $uri, callable $callback): void {
		$this->resource_callbacks[$uri] = $callback;
	}

	/**
	 * Register a prompt.
	 *
	 * @param array $prompt The prompt definition.
	 * @param array $messages The prompt messages.
	 * @return void
	 */
	public function register_prompt(array $prompt, array $messages): void {
		$name = $prompt['name'];
		$this->prompts[$name] = $prompt;
		$this->prompts_messages[$name] = $messages;
	}

	/**
	 * Get all registered tools.
	 *
	 * @return array
	 */
	public function get_tools(): array {
		return $this->tools;
	}

	/**
	 * Get all tool callbacks.
	 *
	 * @return array
	 */
	public function get_tools_callbacks(): array {
		return $this->tools_callbacks;
	}

	/**
	 * Get a tool by name.
	 *
	 * @param string $name The tool name.
	 * @return array|null
	 */
	public function get_tool_by_name(string $name): ?array {
		foreach ($this->tools as $tool) {
			if ($tool['name'] === $name) {
				return $tool;
			}
		}
		return null;
	}

	/**
	 * Get all registered resources.
	 *
	 * @return array
	 */
	public function get_resources(): array {
		return $this->resources;
	}

	/**
	 * Get all resource callbacks.
	 *
	 * @return array
	 */
	public function get_resource_callbacks(): array {
		return $this->resource_callbacks;
	}

	/**
	 * Get all registered prompts.
	 *
	 * @return array
	 */
	public function get_prompts(): array {
		return $this->prompts;
	}

	/**
	 * Get prompt messages by name.
	 *
	 * @param string $name The prompt name.
	 * @return array|null
	 */
	public function get_prompt_messages(string $name): ?array {
		return $this->prompts_messages[$name] ?? null;
	}

	/**
	 * Get the namespace.
	 *
	 * @return string
	 */
	public function get_namespace(): string {
		return $this->namespace;
	}
}
