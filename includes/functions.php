<?php
/**
 * WPRobo CryptoCart Lite Functions.
 * This file contains all the functions which are used in the plugin.
 *
 * @since 1.0.0
 * @package WPRobo/CryptoCart-Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wprobo_add_log' ) ) {
	/**
	 * Add log to debug.log file
	 *
	 * @param array|string $msg            Message to add in log file.
	 * @param bool         $var_export     Whether to use var_export or not.
	 * @param bool         $forcefully_add Whether to forcefully add log or not.
	 *
	 * @since 1.0.0
	 */
	function wprobo_add_log( $msg, bool $var_export = false, bool $forcefully_add = false ): void {

		if ( ( defined( 'WPROBO_DEBUG' ) && WPROBO_DEBUG ) || $forcefully_add ) {
			$upload_dir = wp_upload_dir();
			$log_dir    = $upload_dir['basedir'] . '/wprobo-logs';

			// Attempt to create the directory if it doesn't exist.
			if ( ! file_exists( $log_dir ) ) {
				wp_mkdir_p( $log_dir );
			}

			$plugin_log = $log_dir . '/debug.log'; // Path to the log file within the uploads directory.

			/**
			 * Filter to change the log path.
			 *
			 * @since 1.0.0
			 * @param string $plugin_log Path of the log file.
			 */
			$plugin_log = apply_filters( 'wprobo_ccp_functions_add_log_path', $plugin_log );

			if ( $var_export ) {
				/* phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export */
				$msg = var_export( $msg, true );
			}

			$message = '[' . gmdate( 'd-m-Y H:i:s' ) . '] :: ' . $msg . PHP_EOL;

			/* phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log */
			error_log( $message, 3, $plugin_log );
		}
	}
}

if ( ! function_exists( 'wprobo_sanitize_thing' ) ) {
	/**
	 * Recursive sanitation for text, integer or array.
	 *
	 * @since 1.0.0
	 * @param array|string $var Array or string to sanitize.
	 * @return array|string
	 */
	function wprobo_sanitize_thing( $var ) {

		if ( is_array( $var ) ) {
			return array_map( 'wprobo_sanitize_thing', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( wp_unslash( $var ) ) : $var;
		}
	}
}
