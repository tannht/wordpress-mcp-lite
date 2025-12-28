<?php
/**
 * Error Handler utility class.
 *
 * @package WordPress_MCP_Lite
 */

declare(strict_types=1);

namespace WordpressMcpLite\Utils;

/**
 * Class ErrorHandler
 *
 * Provides centralized error logging for the WordPress MCP Lite plugin.
 */
class ErrorHandler {

	/**
	 * Log an error message.
	 *
	 * @param string $message The error message to log.
	 * @return void
	 */
	public static function log_error(string $message): void {
		// Only log if WP_DEBUG is enabled.
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('[WordPress MCP Lite] ' . $message);
		}
	}
}
