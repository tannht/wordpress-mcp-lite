<?php
/**
 * Media Tools class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Tools;

use WordpressMcpLite\Core\RegisterTool;

/**
 * Class MediaTools
 *
 * Registers MCP tools for WordPress media.
 */
class MediaTools {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action('wordpress_mcp_init', array($this, 'register_tools'));
	}

	/**
	 * Register the tools.
	 */
	public function register_tools(): void {
		new RegisterTool(
			array(
				'name'        => 'wp_media_search',
				'description' => 'Search and filter WordPress media items with pagination',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/media',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Search Media',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_get_media',
				'description' => 'Get a WordPress media item by ID',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/media/(?P<id>[\d]+)',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Get Media',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_add_media',
				'description' => 'Upload a new media file to WordPress',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wp/v2/media',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Media',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_delete_media',
				'description' => 'Delete a WordPress media item by ID',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wp/v2/media/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Media',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);
	}
}
