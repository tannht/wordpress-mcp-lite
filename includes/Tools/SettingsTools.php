<?php
/**
 * Settings Tools class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Tools;

use WordpressMcpLite\Core\RegisterTool;

/**
 * Class SettingsTools
 *
 * Registers MCP tools for WordPress settings.
 */
class SettingsTools {

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
		// Get site info.
		new RegisterTool(
			array(
				'name'        => 'wp_get_site_info',
				'description' => 'Get general WordPress site information',
				'type'        => 'read',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'callback'            => array($this, 'get_site_info'),
				'permission_callback' => '__return_true',
				'annotations'         => array(
					'title'         => 'Get Site Info',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		// Get settings.
		new RegisterTool(
			array(
				'name'        => 'wp_get_settings',
				'description' => 'Get WordPress site settings',
				'type'        => 'read',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'callback'            => array($this, 'get_settings'),
				'permission_callback' => array($this, 'can_manage_options'),
				'annotations'         => array(
					'title'         => 'Get Settings',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		// Update settings.
		new RegisterTool(
			array(
				'name'        => 'wp_update_settings',
				'description' => 'Update WordPress site settings',
				'type'        => 'update',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'blogname'        => array(
							'type'        => 'string',
							'description' => 'Site title',
						),
						'blogdescription' => array(
							'type'        => 'string',
							'description' => 'Site tagline',
						),
						'site_url'        => array(
							'type'        => 'string',
							'description' => 'Site URL',
						),
						'home'            => array(
							'type'        => 'string',
							'description' => 'Blog URL',
						),
						'admin_email'     => array(
							'type'        => 'string',
							'description' => 'Admin email address',
						),
						'timezone_string' => array(
							'type'        => 'string',
							'description' => 'Timezone string',
						),
						'date_format'     => array(
							'type'        => 'string',
							'description' => 'Date format',
						),
						'time_format'     => array(
							'type'        => 'string',
							'description' => 'Time format',
						),
						'start_of_week'   => array(
							'type'        => 'integer',
							'description' => 'Start of week (0-6)',
						),
					),
				),
				'callback'            => array($this, 'update_settings'),
				'permission_callback' => array($this, 'can_manage_options'),
				'annotations'         => array(
					'title'           => 'Update Settings',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);
	}

	/**
	 * Check if user can manage options.
	 *
	 * @return bool
	 */
	public function can_manage_options(): bool {
		return current_user_can('manage_options');
	}

	/**
	 * Get site info.
	 *
	 * @param array $args The arguments (unused).
	 * @return array
	 */
	public function get_site_info(array $args): array {
		return array(
			'name'        => get_bloginfo('name'),
			'description' => get_bloginfo('description'),
			'url'         => get_bloginfo('url'),
			'home'        => get_bloginfo('home'),
			'wp_version'  => get_bloginfo('version'),
			'language'    => get_bloginfo('language'),
			'text_direction' => get_bloginfo('text_direction'),
			'admin_email' => get_option('admin_email'),
			'timezone'    => get_option('timezone_string') ?: get_option('gmt_offset'),
			'active_plugins' => array(
				'count' => count(get_option('active_plugins', array())),
			),
			'active_theme' => array(
				'name' => wp_get_theme()->get('Name'),
				'version' => wp_get_theme()->get('Version'),
			),
		);
	}

	/**
	 * Get settings.
	 *
	 * @param array $args The arguments (unused).
	 * @return array
	 */
	public function get_settings(array $args): array {
		return array(
			'general' => array(
				'blogname'        => get_option('blogname'),
				'blogdescription' => get_option('blogdescription'),
				'site_url'        => get_option('site_url'),
				'home'            => get_option('home'),
				'admin_email'     => get_option('admin_email'),
				'timezone_string' => get_option('timezone_string'),
				'date_format'     => get_option('date_format'),
				'time_format'     => get_option('time_format'),
				'start_of_week'   => get_option('start_of_week'),
			),
			'writing' => array(
				'use_smilies'      => get_option('use_smilies'),
				'use_balanceTags'  => get_option('use_balanceTags'),
				'default_category' => get_option('default_category'),
				'default_post_format' => get_option('default_post_format'),
			),
			'reading' => array(
				'posts_per_page'      => get_option('posts_per_page'),
				'show_on_front'       => get_option('show_on_front'),
				'page_on_front'       => get_option('page_on_front'),
				'page_for_posts'      => get_option('page_for_posts'),
			),
			'discussion' => array(
				'default_comment_status' => get_option('default_comment_status'),
				'default_ping_status'   => get_option('default_ping_status'),
				'comments_per_page'     => get_option('comments_per_page'),
			),
			'media' => array(
				'thumbnail_size_w'  => get_option('thumbnail_size_w'),
				'thumbnail_size_h'  => get_option('thumbnail_size_h'),
				'medium_size_w'     => get_option('medium_size_w'),
				'medium_size_h'     => get_option('medium_size_h'),
				'large_size_w'      => get_option('large_size_w'),
				'large_size_h'      => get_option('large_size_h'),
			),
		);
	}

	/**
	 * Update settings.
	 *
	 * @param array $args The settings to update.
	 * @return array
	 */
	public function update_settings(array $args): array {
		$updated = array();

		// Allowed settings that can be updated.
		$allowed_settings = array(
			'blogname',
			'blogdescription',
			'site_url',
			'home',
			'admin_email',
			'timezone_string',
			'date_format',
			'time_format',
			'start_of_week',
		);

		foreach ($allowed_settings as $setting) {
			if (isset($args[$setting])) {
				$value = $args[$setting];
				$update_result = update_option($setting, $value);
				$updated[$setting] = array(
					'old_value' => get_option($setting),
					'new_value' => $value,
					'updated'  => $update_result,
				);
			}
		}

		return array(
			'message'  => 'Settings updated',
			'updated'  => $updated,
			'current'  => $this->get_settings(array()),
		);
	}
}
