<?php
/**
 * Admin Init.
 *
 * @package WPRobo/CryptoCart-Lite
 */

namespace WPRobo\CryptoCartLite\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Init class.
 */
class AdminInit {

	/**
	 * The single instance of the class.
	 *
	 * @var AdminInit|null $instance.
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {

		$this->admin_menu();
	}

	/**
	 * Initializing the Menu Class.
	 *
	 * @since 1.0.0
	 * @return Menu Returns the Menu class object.
	 */
	public function admin_menu(): Menu {

		static $wprobo_ccp_admin_menu;

		if ( ! isset( $wprobo_ccp_admin_menu ) ) {
			$wprobo_ccp_admin_menu = Menu::instance();
		}

		return $wprobo_ccp_admin_menu;
	}

	/**
	 * Main Menu Instance.
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return AdminInit instance.
	 */
	public static function instance(): AdminInit {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		// Override this PHP function to prevent unwanted copies of your instance.
		// Implement your own error or use `wc_doing_it_wrong().
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		// Override this PHP function to prevent unwanted copies of your instance.
		// Implement your own error or use `wc_doing_it_wrong().
	}
}
