<?php
/**
 * WooCommerce Tools class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Tools;

use WordpressMcpLite\Core\RegisterTool;

/**
 * Class WooCommerceTools
 *
 * Registers MCP tools for WooCommerce (only when WC is active).
 */
class WooCommerceTools {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action('wordpress_mcp_init', array($this, 'register_tools'));
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	private function is_woocommerce_active(): bool {
		return class_exists('WooCommerce');
	}

	/**
	 * Register the tools.
	 */
	public function register_tools(): void {
		// Only register tools if WooCommerce is active.
		if (!$this->is_woocommerce_active()) {
			return;
		}

		// Products.
		new RegisterTool(
			array(
				'name'        => 'wc_products_search',
				'description' => 'Search and filter WooCommerce products with pagination',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Search Products',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_get_product',
				'description' => 'Get a WooCommerce product by ID',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/(?P<id>[\d]+)',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Get Product',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_add_product',
				'description' => 'Add a new WooCommerce product',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Product',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_update_product',
				'description' => 'Update a WooCommerce product by ID',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/(?P<id>[\d]+)',
					'method' => 'PUT',
				),
				'annotations' => array(
					'title'           => 'Update Product',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_delete_product',
				'description' => 'Delete a WooCommerce product by ID',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Product',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		// Product Categories.
		new RegisterTool(
			array(
				'name'        => 'wc_list_product_categories',
				'description' => 'List all WooCommerce product categories',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/categories',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'List Product Categories',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_add_product_category',
				'description' => 'Add a new WooCommerce product category',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/categories',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Product Category',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_update_product_category',
				'description' => 'Update a WooCommerce product category',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/categories/(?P<id>[\d]+)',
					'method' => 'PUT',
				),
				'annotations' => array(
					'title'           => 'Update Product Category',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_delete_product_category',
				'description' => 'Delete a WooCommerce product category',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/categories/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Product Category',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		// Product Tags.
		new RegisterTool(
			array(
				'name'        => 'wc_list_product_tags',
				'description' => 'List all WooCommerce product tags',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/tags',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'List Product Tags',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_add_product_tag',
				'description' => 'Add a new WooCommerce product tag',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/tags',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Product Tag',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_update_product_tag',
				'description' => 'Update a WooCommerce product tag',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/tags/(?P<id>[\d]+)',
					'method' => 'PUT',
				),
				'annotations' => array(
					'title'           => 'Update Product Tag',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_delete_product_tag',
				'description' => 'Delete a WooCommerce product tag',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/tags/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Product Tag',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		// Product Brands (if supported).
		new RegisterTool(
			array(
				'name'        => 'wc_list_product_brands',
				'description' => 'List all WooCommerce product brands',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/brands',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'List Product Brands',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_add_product_brand',
				'description' => 'Add a new WooCommerce product brand',
				'type'        => 'create',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/brands',
					'method' => 'POST',
				),
				'annotations' => array(
					'title'           => 'Add Product Brand',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_update_product_brand',
				'description' => 'Update a WooCommerce product brand',
				'type'        => 'update',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/brands/(?P<id>[\d]+)',
					'method' => 'PUT',
				),
				'annotations' => array(
					'title'           => 'Update Product Brand',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_delete_product_brand',
				'description' => 'Delete a WooCommerce product brand',
				'type'        => 'delete',
				'rest_alias'  => array(
					'route'  => '/wc/v3/products/brands/(?P<id>[\d]+)',
					'method' => 'DELETE',
				),
				'annotations' => array(
					'title'           => 'Delete Product Brand',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		// Orders.
		new RegisterTool(
			array(
				'name'        => 'wc_orders_search',
				'description' => 'Search and filter WooCommerce orders with pagination',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/orders',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Search Orders',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterTool(
			array(
				'name'        => 'wc_get_order',
				'description' => 'Get a WooCommerce order by ID',
				'type'        => 'read',
				'rest_alias'  => array(
					'route'  => '/wc/v3/orders/(?P<id>[\d]+)',
					'method' => 'GET',
				),
				'annotations' => array(
					'title'         => 'Get Order',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);
	}
}
