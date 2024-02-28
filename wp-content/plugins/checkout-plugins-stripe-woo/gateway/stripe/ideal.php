<?php
/**
 * IDEAL Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.2.0
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Helper;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Gateway\Local_Gateway;

/**
 * IDEAL gateway
 *
 * @since 1.2.0
 */
class Ideal extends Local_Gateway {

	use Get_Instance;

	/**
	 * Gateway id
	 *
	 * @var string
	 */
	public $id = 'cpsw_ideal';

	/**
	 * Payment method types
	 *
	 * @var string
	 */
	public $payment_method_types = 'ideal';

	/**
	 * Constructor
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		parent::__construct();
		$this->method_title       = __( 'iDEAL', 'checkout-plugins-stripe-woo' );
		$this->method_description = $this->method_description();
		$this->has_fields         = true;

		$this->init_supports();
		$this->init_form_fields();
		$this->init_settings();

		$this->title             = $this->get_option( 'title' );
		$this->description       = $this->get_option( 'description' );
		$this->order_button_text = $this->get_option( 'order_button_text' );
	}

	/**
	 * Description for ideal gateway
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function method_description() {
		$payment_description = $this->payment_description();

		return sprintf(
			/* translators: %1$s: Break, %2$s: HTML entities */
			__( 'Accept payment using iDEAL. %1$s %2$s', 'checkout-plugins-stripe-woo' ),
			'<br/>',
			$payment_description
		);
	}

	/**
	 * Checks whether this gateway is available.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'eur' !== strtolower( get_woocommerce_currency() ) ) {
			return false;
		}
		return parent::is_available();
	}

	/**
	 * Returns all supported currencies for this payment method.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_supported_currency() {
		return apply_filters(
			'cpsw_ideal_supported_currencies',
			[
				'EUR',
			]
		);
	}

	/**
	 * Creates markup for payment form for iDEAL
	 *
	 * @return void
	 */
	public function payment_fields() {
		/**
		 * Action before payment field.
		 *
		 * @since 1.3.0
		 */
		do_action( $this->id . '_before_payment_field_checkout' );

		echo '<div class="status-box"></div>';
		echo '<div class="cpsw_stripe_ideal_form">';
		if ( $this->description ) {
			echo wp_kses_post( $this->description );
		}
		echo '<div class="cpsw_stripe_ideal_select"></div>';
		echo '<div class="cpsw_stripe_ideal_error"></div>';
		if ( 'test' === Helper::get_payment_mode() ) {
			echo '<div class="cpsw_stripe_test_description">';
			echo wp_kses_post( $this->get_test_mode_description() );
			echo '</div>';
		}
		echo '</div>';

		/**
		 * Action after payment field.
		 *
		 * @since 1.3.0
		 */
		do_action( $this->id . '_after_payment_field_checkout' );
	}
}

