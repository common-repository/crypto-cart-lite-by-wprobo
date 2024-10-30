<?php
/**
 * Handle the admin notices.
 *
 * @package WPRobo/CryptoCart-Lite
 */

namespace WPRobo\CryptoCartLite;

defined( 'ABSPATH' ) || exit;

/**
 * Class to log warnings.
 */
abstract class Notices {
	/**
	 * Message to be displayed in a warning.
	 *
	 * @var string $message Message to be displayed.
	 * @since 1.0.0
	 */
	private string $message;

	/**
	 * Initialize class.
	 *
	 * @param string $message Message to be displayed.
	 * @since 1.0.0
	 */
	public function __construct( string $message ) {
		$this->message = $message;

		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	/**
	 * Displays warning on the admin screen.
	 *
	 * @param string $type           Type of warning.
	 *                               Default: warning.
	 *                               Options: error, warning, success, info.
	 *                               Reference: https://developer.wordpress.org/reference/functions/add_settings_error/.
	 *
	 * @param bool   $is_dismissible Whether the warning is dismissible or not.
	 *                               Default: true.
	 *                               Options: true, false.
	 *                               Reference: https://developer.wordpress.org/reference/functions/add_settings_error/.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render( string $type = 'warning', bool $is_dismissible = true ) {
		$dismissible = '';
		if ( $is_dismissible ) {
			$dismissible = 'is-dismissible';
		}

		printf(
			'<div class="notice notice-%s %s"><p>%s</p></div>',
			esc_attr( $type ),
			esc_attr( $dismissible ),
			esc_html( $this->message )
		);
	}
}
