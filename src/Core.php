<?php
/**
 * CryptoCartLite Core.
 *
 * @package WPRobo/CryptoCart-Lite
 */

namespace WPRobo\CryptoCartLite;

use WPRobo\CryptoCartLite\Admin\AdminInit;
use WPRobo\CryptoCartLite\PaymentGateways\PaymentGateways;

defined( 'ABSPATH' ) || exit;

/**
 * CryptoCartLite Core class.
 */
class Core {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var null|Core The single instance of the class.
	 */
	protected static $instance = null;

	/**
	 * URL to plugin directory.
	 *
	 * @since 1.0.0
	 * @var string The URL to plugin directory without trailing slash.
	 */
	protected string $plugin_url;

	/**
	 * URL to plugin assets directory.
	 *
	 * @since 1.0.0
	 * @var string The URL to plugin assets directory without trailing slash.
	 */
	protected string $assets_url;

	/**
	 * Path to plugin directory.
	 *
	 * @since 1.0.0
	 * @var string The path to plugin directory without trailing slash.
	 */
	protected string $plugin_path;

	/**
	 * Plugin Slug.
	 *
	 * @since 1.0.0
	 * @var string The plugin slug.
	 */
	protected string $plugin_slug;

	/**
	 * The version of the plugin.
	 *
	 * @since 1.0.0
	 * @var string The version of the plugin.
	 */
	protected string $plugin_version = '1.0.1';

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {

		$plugin_url       = plugin_dir_url( __DIR__ );
		$this->plugin_url = rtrim( $plugin_url, '/\\' );

		$this->assets_url  = $this->plugin_url . '/assets';
		$this->plugin_path = rtrim( plugin_dir_path( __DIR__ ), '/\\' );
		$this->plugin_slug = 'wprobo-ccp';

		$this->includes();
		$this->hooks();
		$this->payment_gateways();

		if ( is_admin() ) {
			$this->admin_loader();
		}
	}

	/**
	 * Init hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}
	}

	/**
	 * Get the plugin url.
	 *
	 * @since 1.0.0
	 * @return PaymentGateways The plugin url.
	 */
	public function payment_gateways(): PaymentGateways {

		static $wprobo_ccp_payment_gateways;

		if ( ! isset( $wprobo_ccp_payment_gateways ) ) {
			$wprobo_ccp_payment_gateways = PaymentGateways::instance();
		}

		return $wprobo_ccp_payment_gateways;
	}

	/**
	 * Main CryptoCartLite Instance.
	 * Ensures only one instance of CryptoCartLite is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return Core Main instance.
	 */
	public static function instance(): Core {

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

	/**
	 * Include the required files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		require_once trailingslashit( WPROBO_CCP_INCLUDES_DIR ) . 'functions.php';
	}

	/**
	 * Initializing the AdminMenu Class.
	 *
	 * @since 1.0.0
	 * @return AdminInit Returns the AdminMenu class object.
	 */
	public function admin_loader(): AdminInit {

		static $wprobo_ccp_admin_init;

		if ( ! isset( $wprobo_ccp_admin_init ) ) {
			$wprobo_ccp_admin_init = AdminInit::instance();
		}

		return $wprobo_ccp_admin_init;
	}

	/**
	 * Enqueue the frontend scripts.
	 *
	 * @since 1.0.0
	 */
	public function frontend_enqueue() {

		wp_enqueue_style( 'wprobo-ccp-frontend', trailingslashit( $this->get_assets_url() ) . 'css/frontend' . self::get_assets_min() . '.css', array(), self::get_assets_version() );
		wp_enqueue_script( 'wprobo-ccp-frontend', trailingslashit( $this->get_assets_url() ) . 'js/frontend' . self::get_assets_min() . '.js', array( 'jquery' ), self::get_assets_version(), true );

		wp_localize_script(
			'wprobo-ccp-frontend',
			'wproboCcp',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Get the plugin screen ids.
	 *
	 * @since 1.0.0
	 * @return array The plugin screen ids.
	 */
	private function get_plugin_screen_ids(): array {

		return array(
			'toplevel_page_' . $this->admin_loader()->admin_menu()->get_settings_menu_page_slug(),
		);
	}

	/**
	 * Enqueue the admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, $this->get_plugin_screen_ids(), true ) ) {
			return;
		}

		wp_enqueue_style( 'wprobo-ccp-admin', trailingslashit( $this->get_assets_url() ) . 'css/admin' . self::get_assets_min() . '.css', array(), self::get_assets_version() );
		wp_enqueue_script( 'wprobo-ccp-admin', trailingslashit( $this->get_assets_url() ) . 'js/admin' . self::get_assets_min() . '.js', array( 'jquery' ), self::get_assets_version(), true );

		wp_localize_script(
			'wprobo-ccp-admin',
			'wproboCcp_admin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

	}

	/**
	 * Get the plugin assets url.
	 *
	 * @since 1.0.0
	 * @return string The plugin assets URL.
	 */
	public function get_assets_url(): string {

		return $this->assets_url;
	}

	/**
	 * Get the plugin assets min.
	 *
	 * @since 1.0.0
	 * @return string The plugin assets min.
	 */
	public static function get_assets_min(): string {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			return '';
		}

		return '.min';
	}

	/**
	 * Get the plugin assets version.
	 * If the WP DEBUG is true then sends the time stamp. So, we will get the latest version of the files.
	 * Else send the current plugin version.
	 *
	 * @since 1.0.0
	 * @return string The plugin asset version.
	 */
	public function get_assets_version(): string {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return time();
		}

		return $this->get_version();
	}

	/**
	 * Get the plugin version.
	 *
	 * @since 1.0.0
	 * @return string The plugin version.
	 */
	public function get_version(): string {

		return $this->plugin_version;
	}

}
