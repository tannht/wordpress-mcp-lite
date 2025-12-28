# WordPress MCP Lite

A lightweight WordPress plugin that implements the Model Context Protocol (MCP), providing AI assistants with standardized access to WordPress data and functionality.

## Features

- **Lightweight Architecture**: ~75% less code than the original WordPress MCP plugin
- **Single Transport**: STDIO (WordPress-style) using native WP_REST_Response
- **No Admin UI**: Configuration via constants only - developer-focused
- **Full WooCommerce Support**: 24 tools for products, orders, categories, tags, and brands
- **Automatic Schema Generation**: REST alias feature auto-generates tool schemas from WordPress REST API
- **JWT Authentication**: Secure token-based authentication with configurable expiration

## Requirements

- WordPress 6.4+
- PHP 8.0+
- Composer (for dependency installation)

## Installation

1. Clone or download this plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone <repository-url> wordpress-mcp-lite
   cd wordpress-mcp-lite
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Activate the plugin through WordPress Admin or WP-CLI:
   ```bash
   wp plugin activate wordpress-mcp-lite
   ```

## MCP Tools

### WordPress Core Tools (33 tools)

#### Posts (6 tools)
- `wp_posts_search` - Search/filter posts with pagination
- `wp_get_post` - Get post by ID
- `wp_add_post` - Create new post
- `wp_update_post` - Update post by ID
- `wp_delete_post` - Delete post by ID

#### Pages (5 tools)
- `wp_pages_search` - Search/filter pages
- `wp_get_page` - Get page by ID
- `wp_add_page` - Create new page
- `wp_update_page` - Update page by ID
- `wp_delete_page` - Delete page by ID

#### Categories (4 tools)
- `wp_list_categories` - List all categories
- `wp_add_category` - Add new category
- `wp_update_category` - Update category
- `wp_delete_category` - Delete category

#### Tags (4 tools)
- `wp_list_tags` - List all tags
- `wp_add_tag` - Add new tag
- `wp_update_tag` - Update tag
- `wp_delete_tag` - Delete tag

#### Users (5 tools)
- `wp_users_search` - Search/filter users
- `wp_get_user` - Get user by ID
- `wp_add_user` - Create new user
- `wp_update_user` - Update user by ID
- `wp_delete_user` - Delete user by ID

#### Media (4 tools)
- `wp_media_search` - Search media items
- `wp_get_media` - Get media by ID
- `wp_add_media` - Upload media file
- `wp_delete_media` - Delete media by ID

#### Settings (3 tools)
- `wp_get_site_info` - Get general site information
- `wp_get_settings` - Get site settings
- `wp_update_settings` - Update site settings

### WooCommerce Tools (24 tools - when WooCommerce is active)

#### Products (5 tools)
- `wc_products_search` - Search products
- `wc_get_product` - Get product by ID
- `wc_add_product` - Create product
- `wc_update_product` - Update product
- `wc_delete_product` - Delete product

#### Product Categories (4 tools)
- `wc_list_product_categories`
- `wc_add_product_category`
- `wc_update_product_category`
- `wc_delete_product_category`

#### Product Tags (4 tools)
- `wc_list_product_tags`
- `wc_add_product_tag`
- `wc_update_product_tag`
- `wc_delete_product_tag`

#### Product Brands (4 tools)
- `wc_list_product_brands`
- `wc_add_product_brand`
- `wc_update_product_brand`
- `wc_delete_product_brand`

#### Orders (2 tools)
- `wc_orders_search` - Search orders
- `wc_get_order` - Get order by ID

## Authentication

### Generate JWT Token

```bash
curl -X POST https://yoursite.com/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{"username":"your_username","password":"your_password"}'
```

Response:
```json
{
  "token": "eyJ...",
  "user_id": 1,
  "expires_in": 3600,
  "expires_at": 1234567890
}
```

### Using the Token

Include the JWT token in the Authorization header:
```
Authorization: Bearer eyJ...
```

## MCP Endpoints

### Main MCP Endpoint
```
POST /wp-json/wp/v2/wpmcp
```

### Example: Initialize MCP
```json
{
  "method": "initialize",
  "params": {
    "protocolVersion": "2024-11-05",
    "capabilities": {},
    "clientInfo": {
      "name": "test-client",
      "version": "1.0.0"
    }
  }
}
```

### Example: List Tools
```json
{
  "method": "tools/list"
}
```

### Example: Call Tool
```json
{
  "method": "tools/call",
  "params": {
    "name": "wp_posts_search",
    "arguments": {
      "per_page": 10,
      "page": 1
    }
  }
}
```

### Example: Create Post
```json
{
  "method": "tools/call",
  "params": {
    "name": "wp_add_post",
    "arguments": {
      "title": "Hello from MCP",
      "content": "This post was created via MCP!",
      "status": "draft"
    }
  }
}
```

## Configuration

### Optional Constants (add to wp-config.php)

```php
// Set custom JWT secret key (auto-generated if not set)
define('WPMCP_JWT_SECRET_KEY', 'your-64-character-secret-key');

// Enable debug logging
define('WP_DEBUG', true);
```

### JWT Token Expiration

Default: 1 hour (3600 seconds)
Maximum: 30 days (2592000 seconds)

Customize via filter:
```php
add_filter('wpmcp_jwt_max_expiration_time', function($max_expiration) {
    return 604800; // 7 days
});
```

## Project Structure

```
wordpress-mcp-lite/
├── wordpress-mcp-lite.php         # Main plugin file
├── composer.json                   # Dependencies
├── includes/
│   ├── Core/
│   │   ├── McpServer.php           # Main registry
│   │   ├── McpTransport.php        # STDIO transport
│   │   └── RegisterTool.php        # Tool registration
│   ├── Auth/
│   │   └── JwtAuth.php             # JWT authentication
│   ├── Tools/
│   │   ├── PostsTools.php          # Posts, categories, tags
│   │   ├── PagesTools.php          # Pages
│   │   ├── UsersTools.php          # Users
│   │   ├── MediaTools.php          # Media
│   │   ├── SettingsTools.php       # Settings
│   │   └── WooCommerceTools.php    # WooCommerce
│   └── Utils/
│       └── ErrorHandler.php        # Error logging
└── README.md
```

## Security

- All operations respect WordPress user capabilities
- JWT tokens are stored in a registry and can be revoked
- Tokens expire automatically after the configured time
- Permission callbacks are checked before tool execution

## Comparison with WordPress MCP

| Feature | WordPress MCP | WordPress MCP Lite |
|---------|---------------|-------------------|
| Lines of Code | ~8,983 | ~2,000 |
| Files | 43 | 16 |
| Transports | 2 (STDIO + Streamable) | 1 (STDIO) |
| Admin UI | Full React UI | None |
| WooCommerce | Full support | Full support |
| Configuration | Database settings | Constants |
| Tools | 65+ | 57 |

## Development

### Adding Custom Tools

Create a new file in `includes/Tools/`:

```php
<?php
namespace WordpressMcpLite\Tools;

use WordpressMcpLite\Core\RegisterTool;

class MyCustomTools {
    public function __construct() {
        add_action('wordpress_mcp_init', array($this, 'register_tools'));
    }

    public function register_tools(): void {
        new RegisterTool([
            'name'        => 'my_custom_tool',
            'description' => 'My custom tool description',
            'type'        => 'read',
            'rest_alias'  => [
                'route'  => '/wp/v2/my-endpoint',
                'method' => 'GET',
            ],
        ]);
    }
}
```

Then register it in `McpServer::init_default_tools()`.

## License

GPL-2.0-or-later

## Credits

Based on the [WordPress MCP](https://github.com/Automattic/wordpress-mcp) plugin by Automattic.
