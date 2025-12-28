<?php
/**
 * Users Tools class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Tools;

use WordpressMcpLite\Core\RegisterTool;

/**
 * Class UsersTools
 *
 * Registers MCP tools for WordPress users.
 */
class UsersTools {

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
				'name'        => 'wp_users_search',
				'description' => 'Search and filter WordPress users with pagination',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/users',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Search Users',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_get_user',
				'description' => 'Get a WordPress user by ID',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wp/v2/users/(?P<id>[\d]+)',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Get User',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_add_user',
				'description' => 'Add a new WordPress user',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'                   => '/wp/v2/users',
					'method'                  => 'POST',
					'inputSchemaReplacements' => array(
						'properties' => array(
							'username' => array(
								'type'        => 'string',
								'description' => 'Login username for the user',
							),
							'email'    => array(
								'type'        => 'string',
								'description' => 'The email address of the user',
							),
							'password' => array(
								'type'        => 'string',
								'description' => 'Password for the user (required when creating)',
							),
							'name'     => array(
								'type'        => 'string',
								'description' => 'Display name for the user',
							),
							'first_name' => array(
								'type'        => 'string',
								'description' => 'First name of the user',
							),
							'last_name' => array(
								'type'        => 'string',
								'description' => 'Last name of the user',
							),
							'roles'     => array(
								'type'        => 'array',
								'description' => 'Roles assigned to the user',
							),
						),
						'required'   => array('username', 'email', 'password'),
					),
				),
				'annotations' => array(
					'title'           => 'Add User',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_update_user',
				'description' => 'Update a WordPress user by ID',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wp/v2/users/(?P<id>[\d]+)',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Update User',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wp_delete_user',
				'description' => 'Delete a WordPress user by ID',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wp/v2/users/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete User',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);
	}
}
