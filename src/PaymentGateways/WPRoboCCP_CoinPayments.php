<?php
/**
 * CryptoCart Lite
 * This file is part of CryptoCart Lite WordPress Plugin.
 *
 * @since 1.0.0
 * @package WPRobo/CryptoCart-Lite
 */

namespace WPRobo\CryptoCartLite\PaymentGateways;

use WC_Logger;
use WC_Order;
use WC_Payment_Gateway;

defined( 'ABSPATH' ) || exit;

/**
 * CoinPayments.net Payment Gateway
 *
 * Provides a CoinPayments.net Payment Gateway.
 *
 * @class       WPRoboCCP_CoinPayments
 * @extends     WC_Payment_Gateway
 * @version     1.0.0
 * @package     WPRobo/CryptoCart-Lite
 */
class WPRoboCCP_CoinPayments extends WC_Payment_Gateway {

	/**
	 * IPN url.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $ipn_url;

	/**
	 * Constructor for the gateway.
	 *
	 * @var $name string Gateway name.
	 * @since 1.0.0
	 */
	public static $name = 'coinpayment';

	/**
	 * Gateway id.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $gateway_id = 'wprobo_ccp_coinpayments';

	/**
	 * Constructor for the gateway.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id           = self::$gateway_id;
		$this->icon         = trailingslashit( wprobo_ccp_lite_initialize()->get_assets_url() ) . 'images/pay-using-coinpayments.png';
		$this->has_fields   = false;
		$this->method_title = __( 'CoinPayments.net - CryptoCart Lite', 'wprobo-ccp' );
		$this->ipn_url      = add_query_arg( 'wc-api', 'wprobo_ccp_gateway', site_url() );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title                  = $this->get_option( 'title' );
		$this->description            = $this->get_option( 'description' );
		$this->merchant_id            = $this->get_option( 'merchant_id' );
		$this->ipn_secret             = $this->get_option( 'ipn_secret' );
		$this->send_shipping          = $this->get_option( 'send_shipping' );
		$this->debug_email            = $this->get_option( 'debug_email' );
		$this->allow_zero_confirm     = $this->get_option( 'allow_zero_confirm' ) === 'yes';
		$this->form_submission_method = $this->get_option( 'form_submission_method' ) === 'yes';
		$this->invoice_prefix         = $this->get_option( 'invoice_prefix', 'WC-' );
		$this->simple_total           = $this->get_option( 'simple_total' ) === 'yes';

		// Logs.
		$this->log = new WC_Logger();

		// Actions.
		add_action( 'woocommerce_receipt_coinpayments', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wprobo_ccp_gateway', array( $this, 'check_ipn_response' ) );

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}
	}

	/**
	 * Check if this gateway is enabled and available in the user's country
	 * But for now we are  not checking.
	 *
	 * @return bool True if it's valid.
	 */
	private function is_valid_for_use(): bool {
		return true;
	}

	/**
	 * Output for the order received page.
	 *
	 * @return void
	 */
	public function receipt_page() {
		echo '<p class="ccp-receipt-message-wrapper">' . esc_html__( 'Thank you for your order, please click the button below to pay with CoinPayments.net.', 'wprobo-ccp' ) . '</p>';
	}

	/**
	 * Check for CoinPayments IPN Response
	 *
	 * @return void
	 */
	public function check_ipn_response() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST ) && $this->check_ipn_request_is_valid() ) {
			$this->successful_request( $_POST );
		} else {
			wp_die( 'CoinPayments.net IPN Request Failure' );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Check CoinPayments.net IPN validity.
	 **/
	private function check_ipn_request_is_valid() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$order     = false;
		$error_msg = __( 'Something went wrong', 'wprobo-ccp' );
		$auth_ok   = false;

		if ( isset( $_POST['ipn_mode'] ) && sanitize_text_field( wp_unslash( $_POST['ipn_mode'] ) ) === 'hmac' ) {
			if ( isset( $_SERVER['HTTP_HMAC'] ) && ! empty( $_SERVER['HTTP_HMAC'] ) ) {
				$request = file_get_contents( 'php://input' );
				if ( ! empty( $request ) ) {
					if ( isset( $_POST['merchant'] ) && sanitize_text_field( wp_unslash( $_POST['merchant'] ) ) === trim( $this->merchant_id ) ) {
						$hmac = hash_hmac( 'sha512', $request, trim( $this->ipn_secret ) );
						if ( $hmac === $_SERVER['HTTP_HMAC'] ) {
							$auth_ok = true;
						} else {
							$error_msg = __( 'HMAC signature does not match', 'wprobo-ccp' );
						}
					} else {
						$error_msg = __( 'No or incorrect Merchant ID passed', 'wprobo-ccp' );
					}
				} else {
					$error_msg = __( 'Error reading POST data', 'wprobo-ccp' );
				}
			} else {
				$error_msg = __( 'No HMAC signature sent.', 'wprobo-ccp' );
			}
		} else {
			$error_msg = __( 'Unknown IPN verification method.', 'wprobo-ccp' );
		}

		if ( $auth_ok ) {
			if ( ! empty( $_POST['invoice'] ) && ! empty( $_POST['custom'] ) ) {
				$order = $this->get_coinpayments_order( $_POST );
			}

			if ( false !== $order ) {
				if ( isset( $_POST['ipn_type'] ) && ( 'button' === $_POST['ipn_type'] || 'simple' === $_POST['ipn_type'] ) ) {
					if ( isset( $_POST['merchant'] ) && sanitize_text_field( wp_unslash( $_POST['merchant'] ) ) === $this->merchant_id ) {
						if ( isset( $_POST['currency1'] ) && sanitize_text_field( wp_unslash( $_POST['currency1'] ) ) === $order->get_currency() ) {
							if ( isset( $_POST['amount1'] ) && sanitize_text_field( wp_unslash( $_POST['amount1'] ) ) >= $order->get_total() ) {
								esc_html_e( 'IPN check OK', 'wprobo-ccp' );
								return true;
							} else {
								$error_msg = __( 'Amount received is less than the total!', 'wprobo-ccp' );
							}
						} else {
							$error_msg = __( "Original currency doesn't match!", 'wprobo-ccp' );
						}
					} else {
						$error_msg = __( "Merchant ID doesn't match!", 'wprobo-ccp' );
					}
				} else {
					$error_msg = __( 'ipn_type != button or simple', 'wprobo-ccp' );
				}
			} else {
				$error_msg = sprintf(
					/* translators: %s: Invoice */
					__( 'Could not find order info for order: %s', 'wprobo-ccp' ),
					sanitize_text_field( wp_unslash( $_POST['invoice'] ) )
				);
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		$report = 'Error Message: ' . $error_msg . "\n\n";

		$report .= "POST Fields\n\n";

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		foreach ( $_POST as $key => $value ) {
			$report .= $key . '=' . $value . "\n";
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( $order ) {
			/* Translators: %s: Error Message. */
			$order->update_status( 'on-hold', sprintf( __( 'CoinPayments.net IPN Error: %s', 'wprobo-ccp' ), $error_msg ) );
		}
		if ( ! empty( $this->debug_email ) ) {
			wp_mail( $this->debug_email, 'CoinPayments.net Invalid IPN', $report );
		}

		wprobo_add_log(
			'IPN Error: ' . $error_msg
		);

		return false;
	}

	/**
	 * Get coinpayments.net order.
	 *
	 * @param mixed $posted Post data.
	 *
	 * @return bool|WC_Order
	 */
	private function get_coinpayments_order( $posted ) {
		$custom = json_decode( $posted['custom'] );

		// Backwards comp for IPN requests.
		if ( is_numeric( $custom ) ) {
			$order_id  = (int) $custom;
			$order_key = $posted['invoice'];
		} elseif ( is_string( $custom ) ) {
			$order_id  = (int) str_replace( $this->invoice_prefix, '', $custom );
			$order_key = $custom;
		} else {
			list( $order_id, $order_key ) = $custom;
		}

		$order = wc_get_order( $order_id );

		if ( false === $order ) {
			// We have an invalid $order_id, probably because invoice_prefix has changed.
			$order_id = wc_get_order_id_by_order_key( $order_key );
			$order    = wc_get_order( $order_id );
		}

		// Validate key.
		if ( false === $order || $order->get_order_key() !== $order_key ) {
			return false;
		}

		return $order;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'wprobo-ccp' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable CoinPayments.net', 'wprobo-ccp' ),
				'default' => 'yes',
			),
			'title'              => array(
				'title'       => __( 'Title', 'wprobo-ccp' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wprobo-ccp' ),
				'default'     => __( 'CoinPayments.net', 'wprobo-ccp' ),
				'desc_tip'    => true,
			),
			'description'        => array(
				'title'       => __( 'Description', 'wprobo-ccp' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wprobo-ccp' ),
				'default'     => __( 'Pay with Bitcoin, Litecoin, or other altcoins via CoinPayments.net', 'wprobo-ccp' ),
			),
			'merchant_id'        => array(
				'title'       => __( 'Merchant ID', 'wprobo-ccp' ),
				'type'        => 'text',
				'description' => __( 'Please enter your CoinPayments.net Merchant ID.', 'wprobo-ccp' ),
				'default'     => '',
			),
			'ipn_secret'         => array(
				'title'       => __( 'IPN Secret', 'wprobo-ccp' ),
				'type'        => 'text',
				'description' => __( 'Please enter your CoinPayments.net IPN Secret.', 'wprobo-ccp' ),
				'default'     => '',
			),
			'simple_total'       => array(
				'title'   => __( 'Compatibility Mode', 'wprobo-ccp' ),
				'type'    => 'checkbox',
				'label'   => __( "This may be needed for compatibility with certain addons if the order total isn't correct.", 'wprobo-ccp' ),
				'default' => '',
			),
			'send_shipping'      => array(
				'title'   => __( 'Collect Shipping Info?', 'wprobo-ccp' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Shipping Information on Checkout page', 'wprobo-ccp' ),
				'default' => 'yes',
			),
			'allow_zero_confirm' => array(
				'title'   => __( 'Enable 1st-confirm payments?', 'wprobo-ccp' ),
				'type'    => 'checkbox',
				'label'   => __( '* WARNING * If this is selected orders will be marked as paid as soon as your buyer\'s payment is detected, but before it is fully confirmed. This can be dangerous if the payment never confirms and is only recommended for digital downloads.', 'wprobo-ccp' ),
				'default' => '',
			),
			'invoice_prefix'     => array(
				'title'       => __( 'Invoice Prefix', 'wprobo-ccp' ),
				'type'        => 'text',
				'description' => __( 'Please enter a prefix for your invoice numbers. If you use your CoinPayments.net account for multiple stores ensure this prefix is unique.', 'wprobo-ccp' ),
				'default'     => 'WC-',
				'desc_tip'    => true,
			),
			'testing'            => array(

				'title'       => __( 'Gateway Testing', 'wprobo-ccp' ),
				'type'        => 'title',
				'description' => '',
			),
			'debug_email'        => array(
				'title'       => __( 'Debug Email', 'wprobo-ccp' ),
				'type'        => 'email',
				'default'     => '',
				'description' => __( 'Send copies of invalid IPNs to this email address.', 'wprobo-ccp' ),
			),
		);

	}

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {

		?>

		<h3><?php esc_html_e( 'CoinPayments.net - CryptoCart Lite', 'wprobo-ccp' ); ?></h3>
		<p><?php esc_html_e( 'Completes checkout via CoinPayments.net', 'wprobo-ccp' ); ?></p>

		<?php if ( $this->is_valid_for_use() ) : ?>

			<table class="form-table">
				<?php
				// Generate the HTML For the settings form.
				$this->generate_settings_html();
				?>
			</table><!--/.form-table-->

		<?php else : ?>
			<div class="inline error"><p>

					<strong><?php esc_html_e( 'Gateway Disabled', 'wprobo-ccp' ); ?></strong>: <?php esc_html_e( 'CoinPayments.net does not support your store currency.', 'wprobo-ccp' ); ?>

				</p></div>
			<?php
		endif;
	}

	/**
	 * Get CoinPayments.net Args for passing.
	 *
	 * @param mixed $order Order object.
	 */
	public function get_coinpayments_args( $order ) {

		if ( in_array( $order->get_billing_country(), array( 'US', 'CA' ), true ) ) {
			$order->set_billing_phone( str_replace( array( '( ', '-', ' ', ' )', '.' ), '', $order->get_billing_phone() ) );
		}

		// CoinPayments.net Args.
		$coinpayments_args = array(
			'cmd'         => '_pay_auto',
			'merchant'    => $this->merchant_id,
			'allow_extra' => 0,
			// Get the currency from the order, not the active currency.
			'currency'    => $order->get_currency(),
			'reset'       => 1,
			'success_url' => $this->get_return_url( $order ),
			'cancel_url'  => esc_url_raw( $order->get_cancel_order_url_raw() ),

			// Order key + ID.
			'invoice'     => $this->invoice_prefix . $order->get_order_number(),
			'custom'      => wp_json_encode( array( $order->get_id(), $order->get_order_key() ) ),

			// IPN.
			'ipn_url'     => $this->ipn_url,

			// Billing Address info.
			'first_name'  => $order->get_billing_first_name(),
			'last_name'   => $order->get_billing_last_name(),
			'email'       => $order->get_billing_email(),
		);

		if ( 'yes' === $this->send_shipping ) {
			$coinpayments_args = array_merge(
				$coinpayments_args,
				array(
					'want_shipping' => 1,
					'company'       => $order->get_billing_company(),
					'address1'      => $order->get_billing_address_1(),
					'address2'      => $order->get_billing_address_2(),
					'city'          => $order->get_billing_city(),
					'state'         => $order->get_billing_state(),
					'zip'           => $order->get_billing_postcode(),
					'country'       => $order->get_billing_country(),
					'phone'         => $order->get_billing_phone(),
				)
			);
		} else {
			$coinpayments_args['want_shipping'] = 0;
		}

		/* translators: %s: Order Number. */
		$coinpayments_args['item_name'] = sprintf( __( 'Order %s', 'wprobo-ccp' ), $order->get_order_number() );
		$coinpayments_args['quantity']  = 1;

		if ( $this->simple_total ) {
			$coinpayments_args['amountf']   = number_format( $order->get_total(), 8, '.', '' );
			$coinpayments_args['taxf']      = 0.00;
			$coinpayments_args['shippingf'] = 0.00;
		} elseif ( wc_tax_enabled() && wc_prices_include_tax() ) {
			/* translators: %s: Order Number. */
			$coinpayments_args['amountf']   = number_format( $order->get_total() - $order->get_total_shipping() - $order->get_shipping_tax(), 8, '.', '' );
			$coinpayments_args['shippingf'] = number_format( $order->get_total_shipping() + $order->get_shipping_tax(), 8, '.', '' );
			$coinpayments_args['taxf']      = 0.00;
		} else {
			/* Translators: %s: Order Number. */
			$coinpayments_args['amountf']   = number_format( $order->get_total() - $order->get_total_shipping() - $order->get_total_tax(), 8, '.', '' );
			$coinpayments_args['shippingf'] = number_format( $order->get_total_shipping(), 8, '.', '' );
			$coinpayments_args['taxf']      = number_format( $order->get_total_tax(), 8, '.', '' );
		}

		/**
		 * Filter coinpayments.net args, to let devs add extra fields or change the ones that exist.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'woocommerce_coinpayments_args', $coinpayments_args );
	}

	/**
	 * Generate the coinpayments.net button link.
	 *
	 * @param WC_Order $order The object.
	 *
	 * @return string
	 */
	public function generate_coinpayments_url( $order ) {

		if ( $order->get_status() !== 'completed' && get_post_meta( $order->get_id(), 'CoinPayments payment complete', true ) !== 'Yes' ) {
			$order->update_status( 'pending', 'Customer is being redirected to CoinPayments...' );
		}

		$coinpayments_adr  = 'https://www.coinpayments.net/index.php?';
		$coinpayments_args = $this->get_coinpayments_args( $order );
		$coinpayments_adr .= http_build_query( $coinpayments_args, '', '&' );

		return $coinpayments_adr;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order id.
	 *
	 * @return array returns the payment gateway response.
	 */
	public function process_payment( $order_id ): array {

		$order = wc_get_order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $this->generate_coinpayments_url( $order ),
		);

	}

	/**
	 * Check for valid CoinPayments.net server callback.
	 *
	 * @param array $posted Post data.
	 * @return void
	 */
	public function successful_request( $posted ) {

		$posted = stripslashes_deep( $posted );

		// Custom holds post ID.
		/* phpcs:ignore WordPress.Security.NonceVerification.Missing */
		if ( ! empty( $_POST['invoice'] ) && ! empty( $_POST['custom'] ) ) {
			$order = $this->get_coinpayments_order( $posted );

			if ( false === $order ) {
				/* phpcs:ignore WordPress.Security.NonceVerification.Missing */
				$msg = esc_html( sanitize_text_field( wp_unslash( $_POST['invoice'] ) ) );

				$msg = sprintf(
					/* translators: %s: Message */
					__( 'IPN Error: Could not find order info for order: %s', 'wprobo-ccp' ),
					$msg
				);

				die( esc_attr( $msg ) );
			}

			$this->log->add( 'coinpayments', 'Order #' . $order->get_id() . ' payment status: ' . $posted['status_text'] );
			$order->add_order_note( 'CoinPayments.net Payment Status: ' . $posted['status_text'] );

			if ( $order->get_status() !== 'completed' && get_post_meta( $order->get_id(), 'CoinPayments payment complete', true ) !== 'Yes' ) {
				// no need to update status if it's already done.
				if ( ! empty( $posted['txn_id'] ) ) {
					update_post_meta( $order->get_id(), 'Transaction ID', $posted['txn_id'] );
				}
				if ( ! empty( $posted['first_name'] ) ) {
					update_post_meta( $order->get_id(), 'Payer first name', $posted['first_name'] );
				}
				if ( ! empty( $posted['last_name'] ) ) {
					update_post_meta( $order->get_id(), 'Payer last name', $posted['last_name'] );
				}
				if ( ! empty( $posted['email'] ) ) {
					update_post_meta( $order->get_id(), 'Payer email', $posted['email'] );
				}
				if ( $posted['status'] >= 100 || intval( $posted['status'] ) === 2 || ( $this->allow_zero_confirm && $posted['status'] >= 0 && $posted['received_confirms'] > 0 && $posted['received_amount'] >= $posted['amount2'] ) ) {
					print "Marking complete\n";
					update_post_meta( $order->get_id(), 'CoinPayments payment complete', 'Yes' );
					$order->payment_complete();
				} elseif ( $posted['status'] < 0 ) {
					print "Marking cancelled\n";
					$order->update_status( 'cancelled', 'CoinPayments.net Payment cancelled/timed out: ' . $posted['status_text'] );
				} else {
					print "Marking pending\n";
					$order->update_status( 'pending', 'CoinPayments.net Payment pending: ' . $posted['status_text'] );
				}
			}
			die( 'IPN OK' );
		}
	}
}
