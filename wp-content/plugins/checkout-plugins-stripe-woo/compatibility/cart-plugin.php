<?php
/**
 * Cart plugin compatibility
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.4.5
 */

namespace CPSW\Compatibility;

use CPSW\Inc\Traits\Get_Instance;
use CPSW\Gateway\Stripe\Payment_Request_Api;

/**
 * Cart plugin class
 */
class Cart_Plugin {

	use Get_Instance;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->payment_request = Payment_Request_Api::get_instance();
		add_action( 'wcp_slide_out_footer_after', [ $this, 'render_payment_request_button' ] );
	}

	/**
	 * Payment request button render for cart plugin
	 *
	 * @return void
	 */
	public function render_payment_request_button() {
		$this->payment_request->payment_request_button();
	}
}
