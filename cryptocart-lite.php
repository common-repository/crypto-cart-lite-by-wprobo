<?php
/**
 * Plugin Name: Crypto Cart Lite by WPRobo
 * Plugin URI: https://wordpress.org/plugins/crypto-cart-lite-by-wprobo/
 * Description: Accept cryptocurrency payments on your WooCommerce store with Crypto Cart Lite by WPRobo. Easy, secure,
 * and seamless payment processing for your customers.
 * Version: 1.0.1
 * Author: WPRobo
 * Author URI: http://wprobo.com/
 * Developer: Ali Shan
 * Developer URI: http://wprobo.com/
 * Text Domain: wprobo-ccp
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WPRobo/CryptoCart-Lite
 */

defined( 'ABSPATH' ) || exit;

use WPRobo\CryptoCartLite\Core;
use WPRobo\CryptoCartLite\Admin\Notices;

if ( ! defined( 'WPROBO_CCP_PLUGIN_FILE' ) ) {
	define( 'WPROBO_CCP_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WPROBO_CCP_PLUGIN_VERSION' ) ) {
	define( 'WPROBO_CCP_PLUGIN_VERSION', '1.0.1' );
}

if ( ! defined( 'WPROBO_CCP_PLUGIN_DIR' ) ) {
	define( 'WPROBO_CCP_PLUGIN_DIR', __DIR__ );
}

if ( ! defined( 'WPROBO_CCP_INCLUDES_DIR' ) ) {
	define( 'WPROBO_CCP_INCLUDES_DIR', WPROBO_CCP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'includes' );
}

if ( ! defined( 'WPROBO_CCP_VENDOR_DIR' ) ) {
	define( 'WPROBO_CCP_VENDOR_DIR', WPROBO_CCP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'vendor' );
}

if ( ! defined( 'WPROBO_CCP_PLUGIN_SRC_URL' ) ) {
	define( 'WPROBO_CCP_PLUGIN_SRC_URL', plugin_dir_url( WPROBO_CCP_PLUGIN_FILE ) . 'src' );
}

$loader = include_once WPROBO_CCP_VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php';

if ( ! $loader ) {
	throw new Exception( 'vendor/autoload.php missing please run `composer install`' );
}

/**
 * Activation and deactivation hooks for WordPress
 */
if ( ! function_exists( 'wprobo_ccp_lite_extension_activate' ) ) {
	/**
	 * Deactivate the PRO version of the plugin if it is active.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function wprobo_ccp_lite_extension_activate() {
		$plugin_to_disable = 'cryptocartpro';

		// Check if the target plugin is active.
		if ( is_plugin_active( $plugin_to_disable . '/' . $plugin_to_disable . '.php' ) ) {
			// Deactivate the target plugin.
			deactivate_plugins( $plugin_to_disable . '/' . $plugin_to_disable . '.php' );
		}
	}

	register_activation_hook( __FILE__, 'wprobo_ccp_lite_extension_activate' );
}

if ( ! function_exists( 'wprobo_ccp_lite_extension_deactivate' ) ) {
	/**
	 * Deactivation hook for WordPress.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function wprobo_ccp_lite_extension_deactivate() {
		// Add any deactivation tasks here (e.g., cleanup, data removal).
		// This code will be executed once when the plugin is deactivated.
	}

	register_deactivation_hook( __FILE__, 'wprobo_ccp_lite_extension_deactivate' );
}

if ( ! function_exists( 'wprobo_ccp_lite_initialize' ) ) {
	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 * @return Core Instance of the Core class.
	 */
	function wprobo_ccp_lite_initialize(): ?Core {

		static $wprobo_ccp;

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'wprobo_ccp_lite_display_woocommerce_admin_notice' );
			return null;
		}

		if ( ! isset( $wprobo_ccp ) ) {
			$wprobo_ccp = Core::instance();
		}

		$GLOBALS['wprobo_ccp'] = $wprobo_ccp;

		return $wprobo_ccp;
	}

	add_action( 'plugins_loaded', 'wprobo_ccp_lite_initialize', 10 );
}

if ( ! function_exists( 'wprobo_ccp_lite_display_woocommerce_admin_notice' ) ) {
	/**
	 * Display WooCommerce admin notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function wprobo_ccp_lite_display_woocommerce_admin_notice() {
		( new Notices( __( 'Please install and activate WooCommerce plugin to use Crypto Cart Lite by WPRobo.', 'wprobo-ccp' ) ) )->render( 'error', false );
	}
}
