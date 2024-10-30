<?php
/**
 * Payment Gateways.
 *
 * @package WPRobo/CryptoCart-Lite
 */

namespace WPRobo\CryptoCartLite\PaymentGateways;

defined( 'ABSPATH' ) || exit;

/**
 * Payment Gateways class.
 */
class PaymentGateways {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var PaymentGateways|null The single instance of the class.
	 */
	protected static  $instance = null;

	/**
	 * PaymentGateways constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {

		$this->hooks();
	}

	/**
	 * Get registered coin payment gateways.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_registered_coin_payment_gateways(): array {

		return array(
			WPRoboCCP_CoinPayments::class,
		);
	}

	/**
	 * Get id of coinpaymentgateway.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_id_of_coinpaymentgateway(): string {

		return WPRoboCCP_CoinPayments::$gateway_id;
	}

	/**
	 * Get name of coinpaymentgateway.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ) );
	}

	/**
	 * Add payment gateways.
	 *
	 * @param array $methods Payment gateways.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_payment_gateways( array $methods ): array {

		foreach ( $this->get_registered_coin_payment_gateways() as $class ) {
			$is_gateway_enabled = wprobo_ccp_lite_initialize()->admin_loader()->admin_menu()->check_if_payment_gateway_enabled( $class::$name );

			if ( ! in_array( $class, $methods, true ) && $is_gateway_enabled ) {
				$methods[] = $class;
			}
		}

		return $methods;
	}

	/**
	 * Get the single instance of the class.
	 *
	 * @since 1.0.0
	 * @return PaymentGateways|null
	 */
	public static function instance(): ?PaymentGateways {

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
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		// Override this PHP function to prevent unwanted copies of your instance.
		// Implement your own error or use `wc_doing_it_wrong().
	}

}
