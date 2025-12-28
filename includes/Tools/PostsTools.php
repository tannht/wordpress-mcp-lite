<?php
/**
 * Posts Tools class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Tools;

use WordpressMcpLite\Core\RegisterTool;

/**
 * Class PostsTools
 *
 * Registers MCP tools for WordPress posts, categories, and tags.
 */
class PostsTools {

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
		// Posts.
		new RegisterTool(
			array(
				'name'        => 'wp_posts_search',
				'description' => 'Search and filter WordPress posts with pagination',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/posts',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Search Posts',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_get_post',
				'description' => 'Get a WordPress post by ID',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/posts/(?P<id>[\d]+)',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Get Post',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_add_post',
				'description' => 'Add a new WordPress post',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'                   => '/wp/v2/posts',
					'method'                  => 'POST',
					'inputSchemaReplacements' => array(
						'properties' => array(
							'title'   => array(
								'type' => 'string',
							),
							'content' => array(
								'type'        => 'string',
								'description' => 'The content of the post in Gutenberg block format',
							),
							'excerpt' => array(
								'type' => 'string',
							),
							'status'  => array(
								'type'        => 'string',
								'description' => 'Post status (draft, publish, etc.)',
								'enum'        => array('draft', 'publish', 'pending', 'private', 'future'),
							),
						),
						'required'   => array('title', 'content'),
					),
				),
				'annotations' => array(
					'title'           => 'Add Post',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_update_post',
				'description' => 'Update a WordPress post by ID',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wp/v2/posts/(?P<id>[\d]+)',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Update Post',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_delete_post',
				'description' => 'Delete a WordPress post by ID',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wp/v2/posts/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Post',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		// Categories.
		new RegisterTool(
			array(
				'name'        => 'wp_list_categories',
				'description' => 'List all WordPress post categories',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/categories',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'List Categories',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_add_category',
				'description' => 'Add a new WordPress post category',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wp/v2/categories',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Category',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_update_category',
				'description' => 'Update a WordPress post category',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wp/v2/categories/(?P<id>[\d]+)',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Update Category',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_delete_category',
				'description' => 'Delete a WordPress post category',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wp/v2/categories/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Category',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		// Tags.
		new RegisterTool(
			array(
				'name'        => 'wp_list_tags',
				'description' => 'List all WordPress post tags',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/tags',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'List Tags',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_add_tag',
				'description' => 'Add a new WordPress post tag',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wp/v2/tags',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Tag',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_update_tag',
				'description' => 'Update a WordPress post tag',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wp/v2/tags/(?P<id>[\d]+)',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Update Tag',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_delete_tag',
				'description' => 'Delete a WordPress post tag',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wp/v2/tags/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Tag',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);
	}
}
