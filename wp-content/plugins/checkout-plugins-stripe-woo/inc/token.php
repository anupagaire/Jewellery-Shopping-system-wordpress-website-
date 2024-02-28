<?php
/**
 * Token helper.
 *
 * @package checkout-plugins-stripe-woo
 *
 * @since 1.4.0
 */

namespace CPSW\Inc;

use WC_Payment_Token;

/**
 * Stripe Payment Token.
 *
 * Representation of a payment token for SEPA.
 *
 * @class Token
 *
 * @since 1.4.0
 */
class Token extends WC_Payment_Token {

	/**
	 * Stores payment type.
	 *
	 * @var string
	 */
	protected $type = 'cpsw_sepa';

	/**
	 * Stores SEPA payment token data.
	 *
	 * @var array
	 */
	protected $extra_data = [
		'last4'               => '',
		'payment_method_type' => 'sepa_debit',
	];

	/**
	 * Get type to display to user.
	 *
	 * @since 1.4.0
	 *
	 * @param  string $deprecated Deprecated.
	 *
	 * @return string
	 */
	public function get_display_name( $deprecated = '' ) {
		$display = sprintf(
			/* translators: last 4 digits of IBAN account */
			__( 'SEPA IBAN ending in %s', 'checkout-plugins-stripe-woo' ),
			$this->get_last4()
		);

		return $display;
	}

	/**
	 * Hook prefix
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	protected function get_hook_prefix() {
		return 'cpsw_payment_token_sepa_get_';
	}

	/**
	 * Validate SEPA payment tokens.
	 *
	 * These fields are required by all SEPA payment tokens:
	 * last4  - string Last 4 digits of the iBAN.
	 *
	 * @since 1.4.0
	 *
	 * @return boolean True if the passed data is valid
	 */
	public function validate() {
		if ( false === parent::validate() ) {
			return false;
		}

		if ( ! $this->get_last4( 'edit' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the last four digits.
	 *
	 * @since 1.4.0
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string Last 4 digits
	 */
	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	/**
	 * Set the last four digits.
	 *
	 * @since 1.4.0
	 *
	 * @param string $last4 Last 4 digits card number.
	 *
	 * @return void
	 */
	public function set_last4( $last4 ) {
		$this->set_prop( 'last4', $last4 );
	}

	/**
	 * Set Stripe payment method type.
	 *
	 * @since 1.4.0
	 *
	 * @param string $type Payment method type.
	 *
	 * @return void
	 */
	public function set_payment_method_type( $type ) {
		$this->set_prop( 'payment_method_type', $type );
	}

	/**
	 * Returns Stripe payment method type.
	 *
	 * @since 1.4.0
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string $payment_method_type
	 */
	public function get_payment_method_type( $context = 'view' ) {
		return $this->get_prop( 'payment_method_type', $context );
	}
}
