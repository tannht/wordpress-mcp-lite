<?php
/**
 * Plugin name:       WordPress MCP Lite
 * Description:       A lightweight WordPress MCP plugin providing AI-accessible interfaces to WordPress data through Model Context Protocol. Supports Posts, Pages, Users, Media, Settings, and WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       wordpress-mcp-lite
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

use WordpressMcpLite\Core\McpServer;
use WordpressMcpLite\Core\McpTransport;
use WordpressMcpLite\Auth\JwtAuth;

define('WORDPRESS_MCP_LITE_VERSION', '1.0.0');
define('WORDPRESS_MCP_LITE_PATH', plugin_dir_path(__FILE__));
define('WORDPRESS_MCP_LITE_URL', plugin_dir_url(__FILE__));

// Check if Composer autoloader exists.
if (!file_exists(WORDPRESS_MCP_LITE_PATH . 'vendor/autoload.php')) {
	wp_die(
		sprintf(
			'Please run <code>composer install</code> in the plugin directory: <code>%s</code>',
			esc_html(WORDPRESS_MCP_LITE_PATH)
		)
	);
}

require_once WORDPRESS_MCP_LITE_PATH . 'vendor/autoload.php';

/**
 * Get the WordPress MCP Lite instance.
 *
 * @return McpServer
 */
function WPMCP_LITE() {
	return McpServer::instance();
}

/**
 * Initialize the plugin.
 */
function wordpress_mcp_lite_init() {
	$mcp = WPMCP_LITE();

	// Initialize the STDIO transport.
	new McpTransport($mcp);

	// Initialize the JWT authentication.
	new JwtAuth();
}

// Initialize the plugin.
add_action('init', 'wordpress_mcp_lite_init');
