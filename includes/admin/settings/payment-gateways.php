<?php
/**
 * Payment Gateways
 * This file is responsible for displaying the payment gateways settings page.
 *
 * @since 1.0.0
 * @package WPRobo/CryptoCart-Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WPRobo\CryptoCartLite\PaymentGateways\WPRoboCCP_CoinPayments;

$gateways = array(
	'Coin Payment' => WPRoboCCP_CoinPayments::$name,
);

?>

<div class="wrap ccp_page">
	<h2><?php esc_html_e( 'Enable the Crypto Payment Gateways', 'wprobo-ccp' ); ?></h2>
	<div class="ccp-inner">
		<form action="#" method="post">
			<div class="ccp_gateways_wrapper">
				<?php
				foreach ( $gateways as $label => $gateway ) :

					$is_current_gateway_enabled = wprobo_ccp_lite_initialize()->admin_loader()->admin_menu()->check_if_payment_gateway_enabled( $gateway );

					?>

					<div class="ccp-<?php echo esc_attr( $gateway ); ?>-gateway">
						<label for="ccp-<?php echo esc_attr( $gateway ); ?>" class="<?php echo $is_current_gateway_enabled ? 'ccp-enable' : ''; ?>">

							<strong class="description">
								<?php
									/* translators: %s: Gateway Name */
									echo esc_html( sprintf( __( 'Enable %s', 'wprobo-ccp' ), $label ) );
								?>
							</strong>

							<span class="company-logo">
								<img src="<?php echo esc_url( wprobo_ccp_lite_initialize()->get_assets_url() . '/images/' . $gateway . '-logo.svg' ); ?>" alt="<?php echo esc_attr( $gateway . '-logo' ); ?>" width="150">
							</span>

							<span class="ccp-input-wrapper">
								<input type="checkbox" name="ccp-gateways[]" value="<?php echo esc_attr( $gateway ); ?>" id="ccp-<?php echo esc_attr( $gateway ); ?>" class="input-control" <?php echo $is_current_gateway_enabled ? 'checked' : ''; ?>>
							</span>
						</label>

						<?php wp_nonce_field( 'wprobo-ccp', 'wprobo-ccp' ); ?>
						<?php submit_button(); ?>
					</div>

				<?php endforeach; ?>
			</div>
		</form>
	</div>
</div>
