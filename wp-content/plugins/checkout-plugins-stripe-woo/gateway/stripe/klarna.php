<?php
/**
 * Klarna Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.3.0
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Helper;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Gateway\Local_Gateway;

/**
 * Klarna
 *
 * @since 1.3.0
 */
class Klarna extends Local_Gateway {

	use Get_Instance;

	/**
	 * Gateway id
	 *
	 * @var string
	 */
	public $id = 'cpsw_klarna';

	/**
	 * Payment method types
	 *
	 * @var string
	 */
	public $payment_method_types = 'klarna';

	/**
	 * Allow countries
	 *
	 * @var array
	 */
	public $allow_countries = [ 'AT', 'AU', 'BE', 'CA', 'CH', 'CZ', 'DE', 'DK', 'ES', 'FI', 'FR', 'GB', 'GR', 'IE', 'IT', 'NL', 'NO', 'NZ', 'PL', 'PT', 'SE', 'US' ];

	/**
	 * Constructor
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		parent::__construct();

		$this->method_title       = __( 'Klarna', 'checkout-plugins-stripe-woo' );
		$this->method_description = $this->method_description();
		$this->has_fields         = true;
		$this->init_supports();

		$this->init_form_fields();
		$this->init_settings();
		// get_option should be called after init_form_fields().
		$this->title             = $this->get_option( 'title' );
		$this->description       = $this->get_option( 'description' );
		$this->order_button_text = $this->get_option( 'order_button_text' );
		$this->capture_method    = $this->get_option( 'charge_type' );
	}

	/**
	 * Description for Klarna gateway
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function method_description() {
		$payment_description = $this->payment_description();
		/* translators: HTML Entities.*/
		$extra_description = $this->is_current_section() ? sprintf( __( 'Klarna is supported only for billing countries %1$sAustralia (AU), Austria (AT), Belgium (BE), Canada (CA), Czechia (CZ), Denmark (DK), Finland (FI), France (FR), Germany (DE), Greece (GR), Ireland (IE), Italy (IT), Netherlands (NL), New Zealand (NZ), Norway (NO), Poland (PL), Portugal (PT), Spain (ES), Sweden (SE), Switzerland (CH), United Kingdom (UK) (GB), United States (US)%2$s.', 'checkout-plugins-stripe-woo' ), '<strong>', '</strong>' ) : '';

		return sprintf(
			/* translators: %1$s: Break, %2$s: Gateway appear message, %3$s: Break, %4$s: Gateway appear message currency wise, %4$s:  HTML entities */
			__( 'Accept payment using Klarna. %1$s %2$s %3$s %4$s', 'checkout-plugins-stripe-woo' ),
			'<br/>',
			$payment_description,
			'<br/>',
			$extra_description
		);
	}

	/**
	 * Returns all supported currencies for this payment method.
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public function get_supported_currency() {
		return apply_filters(
			'cpsw_klarna_supported_currencies',
			[
				'EUR',
				'USD',
				'GBP',
				'DKK',
				'SEK',
				'NOK',
			]
		);
	}

	/**
	 * Checks whether this gateway is available.
	 *
	 * @since 1.3.0
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( ! in_array( $this->get_currency(), $this->get_supported_currency(), true ) ) {
			return false;
		}

		if ( ! in_array( $this->get_billing_country(), $this->allow_countries, true ) ) {
			return false;
		}

		return parent::is_available();
	}

	/**
	 * Add more gateway form fields
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public function get_default_settings() {
		$charge_type = [
			'charge_type' => [
				'title'       => __( 'Charge Type', 'checkout-plugins-stripe-woo' ),
				'type'        => 'select',
				'description' => __( 'Select how to charge Order', 'checkout-plugins-stripe-woo' ),
				'default'     => 'automatic',
				'options'     => [
					'automatic' => __( 'Charge', 'checkout-plugins-stripe-woo' ),
					'manual'    => __( 'Authorize', 'checkout-plugins-stripe-woo' ),
				],
				'desc_tip'    => true,
			],
		];

		$local_settings = parent::get_default_settings();

		$local_settings['allowed_countries']['default']  = 'specific';
		$local_settings['specific_countries']['default'] = $this->allow_countries;
		$local_settings['specific_countries']['options'] = $this->allow_countries;
		$local_settings['except_countries']['options']   = $this->allow_countries;

		return array_merge( $local_settings, $charge_type );
	}

	/**
	 * Creates markup for payment form for card payments
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function payment_fields() {
		global $wp;

		$user  = wp_get_current_user();
		$total = WC()->cart->total;

		// If paying from order, we need to get total from order not cart.
		if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order = wc_get_order( wc_clean( $wp->query_vars['order-pay'] ) );
			$total = $order->get_total();
		}

		if ( is_add_payment_method_page() ) {
			$pay_button_text = __( 'Add Payment', 'checkout-plugins-stripe-woo' );
			$total           = '';
		} else {
			$pay_button_text = '';
		}

		/**
		 * Action before payment field.
		 *
		 * @since 1.3.0
		 */
		do_action( $this->id . '_before_payment_field_checkout' );

		echo '<div
			id="cpsw-klarna-payment-data"
			data-amount="' . esc_attr( $total ) . '"
			data-currency="' . esc_attr( strtolower( $this->get_currency() ) ) . '">';

		if ( $this->description ) {
			echo wp_kses_post( $this->description );
		}

		echo '</div>';
		if ( 'test' === Helper::get_payment_mode() ) {
			echo '<div class="cpsw_stripe_test_description">';
			echo '<p>';
			esc_html_e( 'Test verification code:', 'checkout-plugins-stripe-woo' );
			echo '&nbsp;<strong>111000</strong></p>';
			echo wp_kses_post( $this->get_test_mode_description() );
			echo '</div>';
		}

		/**
		 * Action after payment field.
		 *
		 * @since 1.3.0
		 */
		do_action( $this->id . '_after_payment_field_checkout' );
	}
}
