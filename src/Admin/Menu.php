<?php
/**
 * Admin Menu.
 *
 * @package WPRobo/CryptoCart-Lite
 */

namespace WPRobo\CryptoCartLite\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Menu Class.
 */
class Menu {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var Menu|null $instance The single instance of the class.
	 */
	protected static $instance = null;

	/**
	 * Settings Menu Page Slug.
	 *
	 * @var string $settings_menu_page_slug Settings Menu Page Slug.
	 * @since 1.0.0
	 */
	private string $settings_menu_page_slug = 'wprobo-ccp';

	/**
	 * Options Slug.
	 *
	 * @var string $options_slug Options Slug.
	 * @since 1.0.0
	 */
	private string $options_slug = 'wprobo_ccp';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {

		$this->hooks();
	}

	/**
	 * Get the settings menu page slug.
	 *
	 * @since 1.0.0
	 */
	public function get_settings_menu_page_slug(): string {

		return $this->settings_menu_page_slug;
	}

	/**
	 * Get the options slug.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'admin_menu', array( $this, 'register_settings_menu' ) );
	}

	/**
	 * Register Settings Menu.
	 *
	 * @since 1.0.0
	 */
	public function register_settings_menu() {

		add_menu_page(
			__( 'CryptoCart Lite', 'wprobo-ccp' ),
			__( 'CryptoCart Lite', 'wprobo-ccp' ),
			'manage_options',
			$this->settings_menu_page_slug,
			array( $this, 'settings_page_callback' ),
			'dashicons-cart',
			30
		);
	}

	/**
	 * Settings Page Callback.
	 *
	 * @since 1.0.0
	 * @param array|string $args Arguments.
	 */
	public function settings_page_callback( $args ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! empty( $_POST ) && isset( $_POST['wprobo-ccp'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wprobo-ccp'] ) ), 'wprobo-ccp' ) ) {
				( new Notices( __( 'Nonce expired. Please refresh the page and try again.', 'wprobo-ccp' ) ) )->render( 'error', false );
			} else {
				$this->handle_settings();
			}
		}

		wc_admin_connect_page(
			array(
				'id'           => 'wprobo-ccp',
				'title'        => __( 'CryptoCart Lite', 'wprobo-ccp' ),
				'description'  => __( 'Enable the Gateway before continue.', 'wprobo-ccp' ),
				'hide_sidebar' => true,
				'path'         => add_query_arg( 'page', 'wprobo-ccp', 'admin.php' ),
			)
		);

		require_once trailingslashit( WPROBO_CCP_INCLUDES_DIR ) . 'admin/settings/payment-gateways.php';

	}

	/**
	 * Handle Settings.
	 *
	 * @return void Returns true if settings were saved successfully.
	 * @since 1.0.0
	 */
	private function handle_settings(): void {

		if ( isset( $_POST['wprobo-ccp'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wprobo-ccp'] ) ), 'wprobo-ccp' ) ) {
			( new Notices( __( 'Nonce expired. Please refresh the page and try again.', 'wprobo-ccp' ) ) )->render( 'error', false );
			return;
		}

		$gateways = array();

		if ( isset( $_POST['ccp-gateways'] ) && is_array( $_POST['ccp-gateways'] ) ) {
			$gateways = array_map( 'sanitize_text_field', wp_unslash( $_POST['ccp-gateways'] ) );
		}

		$data = array(
			'gateways'     => $gateways,
			'last_updated' => time(),
		);

		$is_updated = update_option( $this->options_slug, $data );

		if ( ! $is_updated ) {
			( new Notices( __( 'Unable to save the settings. Some internal error occurred. Please try again.', 'wprobo-ccp' ) ) )->render( 'warning', false );
			return;
		}

		( new Notices( __( 'Settings were successfully Saved.', 'wprobo-ccp' ) ) )->render( 'success' );

	}

	/**
	 * Check if Payment Gateway is enabled.
	 *
	 * @since 1.0.0
	 * @param string $gateway Gateway.
	 */
	public function check_if_payment_gateway_enabled( string $gateway ): bool {

		$payment_gateways = get_option( $this->options_slug, array() );

		if ( ! empty( $payment_gateways ) && isset( $payment_gateways['gateways'] ) && in_array( $gateway, $payment_gateways['gateways'], true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the single instance of the class.
	 *
	 * @since 1.0.0
	 * @return Menu Returns the single instance of the class.
	 */
	public static function instance(): Menu {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		// Override this PHP function to prevent unwanted copies of your instance.
		// Implement your own error or use `wc_doing_it_wrong().
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		// Override this PHP function to prevent unwanted copies of your instance.
		// Implement your own error or use `wc_doing_it_wrong().
	}
}
