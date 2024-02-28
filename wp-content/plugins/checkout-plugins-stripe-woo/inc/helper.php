<?php
/**
 * Stripe Gateway webhook.
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Inc;

/**
 * Stripe Webhook.
 */
class Helper {

	/**
	 * Default global values
	 *
	 * @var array
	 */
	private static $global_defaults = [
		'cpsw_test_pub_key'        => '',
		'cpsw_pub_key'             => '',
		'cpsw_test_secret_key'     => '',
		'cpsw_secret_key'          => '',
		'cpsw_test_con_status'     => '',
		'cpsw_con_status'          => '',
		'cpsw_mode'                => 'test',
		'cpsw_live_webhook_secret' => '',
		'cpsw_test_webhook_secret' => '',
		'cpsw_account_id'          => '',
		'cpsw_debug_log'           => 'yes',
	];

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
	}

	/**
	 * Stripe get all settings
	 *
	 * @return $global_settings array It returns all stripe settings in an array.
	 */
	public static function get_settings() {
		$response = [];
		foreach ( self::$global_defaults as $key => $default_data ) {
			$response[ $key ] = self::get_global_setting( $key );
		}
		return apply_filters( 'cpsw_settings', $response );
	}

	/**
	 * Stripe get all settings
	 *
	 * @return $global_settings array It returns all stripe settings in an array.
	 */
	public static function get_gateway_defaults() {
		return apply_filters(
			'cpsw_stripe_gateway_defaults_settings',
			[
				'woocommerce_cpsw_stripe_settings' => [
					'enabled'                             => 'no',
					'inline_cc'                           => 'yes',
					'order_status'                        => '',
					'allowed_cards'                       => [
						'mastercard',
						'visa',
						'diners',
						'discover',
						'amex',
						'jcb',
						'unionpay',
					],
					'express_checkout_location'           => [
						'product',
						'cart',
						'checkout',
					],
					'express_checkout_enabled'            => 'no',
					'express_checkout_button_text'        => __( 'Pay now', 'checkout-plugins-stripe-woo' ),
					'express_checkout_button_theme'       => 'dark',
					'express_checkout_button_height'      => '40',
					'express_checkout_title'              => __( 'Express Checkout', 'checkout-plugins-stripe-woo' ),
					'express_checkout_tagline'            => __( 'Checkout faster with one of our express checkout options.', 'checkout-plugins-stripe-woo' ),
					'express_checkout_product_page_position' => 'above',
					'express_checkout_product_sticky_footer' => 'yes',
					'express_checkout_separator_product'  => __( 'OR', 'checkout-plugins-stripe-woo' ),
					'express_checkout_button_width'       => '',
					'express_checkout_button_alignment'   => 'left',
					'express_checkout_separator_cart'     => __( 'OR', 'checkout-plugins-stripe-woo' ),
					'express_checkout_separator_checkout' => __( 'OR', 'checkout-plugins-stripe-woo' ),
					'express_checkout_checkout_page_position' => 'above-checkout',
					'express_checkout_checkout_page_layout' => 'custom',
				],
				'woocommerce_cpsw_alipay_settings' => [
					'enabled' => 'no',
				],
			]
		);
	}

	/**
	 * Get all settings of a particular gateway
	 *
	 * @param string $gateway gateway id.
	 * @return array
	 */
	public static function get_gateway_settings( $gateway = 'cpsw_stripe' ) {
		$default_settings = [];
		$setting_name     = 'woocommerce_' . $gateway . '_settings';
		$saved_settings   = is_array( get_option( $setting_name, [] ) ) ? get_option( $setting_name, [] ) : [];
		$gateway_defaults = self::get_gateway_defaults();

		if ( isset( $gateway_defaults[ $setting_name ] ) ) {
			$default_settings = $gateway_defaults[ $setting_name ];
		}

		$settings = array_merge( $default_settings, $saved_settings );

		return apply_filters( 'cpsw_gateway_settings', $settings );
	}

	/**
	 * Get value of gateway option parameter
	 *
	 * @param string $key key name.
	 * @param string $gateway gateway id.
	 * @return mixed
	 */
	public static function get_gateway_setting( $key = '', $gateway = 'cpsw_stripe' ) {
		$settings = self::get_gateway_settings( $gateway );
		$value    = false;

		if ( isset( $settings[ $key ] ) ) {
			$value = $settings[ $key ];
		}

		return $value;
	}

	/**
	 * Get value of global option
	 *
	 * @param string $key value of global setting.
	 * @return mixed
	 */
	public static function get_global_setting( $key ) {
		$db_data = get_option( $key );
		return $db_data ? $db_data : self::$global_defaults[ $key ];
	}

	/**
	 * Stripe get settings value by key.
	 *
	 * @param string $key Name of the key to get the value.
	 * @param mixed  $gateway Name of the payment gateway to get options from the database.
	 *
	 * @return array $global_settings It returns all stripe settings in an array.
	 */
	public static function get_setting( $key = '', $gateway = false ) {
		$result = false;
		if ( false !== $gateway ) {
			$result = self::get_gateway_setting( $key, $gateway );
		} else {
			$result = self::get_global_setting( $key );
		}
		return is_array( $result ) || $result ? apply_filters( $key, $result ) : false;
	}

	/**
	 * Stripe get current mode
	 *
	 * @return $mode string It returns current mode of the stripe payment gateway.
	 */
	public static function get_payment_mode() {
		return apply_filters( 'cpsw_payment_mode', self::get_setting( 'cpsw_mode' ) );
	}

	/**
	 * Get webhook secret key.
	 *
	 * @since 1.2.0
	 * @param string $mode payment mode.
	 * @return mixed
	 */
	public static function get_webhook_secret( $mode = '' ) {
		$mode = empty( $mode ) ? self::get_payment_mode() : $mode;
		if ( 'live' === $mode ) {
			$endpoint_secret = self::get_setting( 'cpsw_live_webhook_secret' );
		} elseif ( 'test' === $mode ) {
			$endpoint_secret = self::get_setting( 'cpsw_test_webhook_secret' );
		}

		if ( empty( trim( $endpoint_secret ) ) ) {
			return false;
		}

		return $endpoint_secret;
	}

	/**
	 * Localize Stripe messages based on code
	 *
	 * @since 1.4.1
	 *
	 * @param string $code Stripe error code.
	 * @param string $message Stripe error message.
	 *
	 * @return string
	 */
	public static function get_localized_messages( $code = '', $message = '' ) {
		$localized_messages = apply_filters(
			'cpsw_stripe_localized_messages',
			[
				'account_country_invalid_address'        => __( 'The business address that you provided does not match the country set in your account. Please enter an address that falls within the same country.', 'checkout-plugins-stripe-woo' ),
				'account_invalid'                        => __( 'The account ID provided in the Stripe-Account header is invalid. Please check that your requests specify a valid account ID.', 'checkout-plugins-stripe-woo' ),
				'amount_too_large'                       => __( 'The specified amount is greater than the maximum amount allowed. Use a lower amount and try again.', 'checkout-plugins-stripe-woo' ),
				'amount_too_small'                       => __( 'The specified amount is less than the minimum amount allowed. Use a higher amount and try again.', 'checkout-plugins-stripe-woo' ),
				'api_key_expired'                        => __( 'Your API Key has expired. Please update your integration with the latest API key available in your Dashboard.', 'checkout-plugins-stripe-woo' ),
				'authentication_required'                => __( 'The payment requires authentication to proceed. If your customer is off session, notify your customer to return to your application and complete the payment. If you provided the error_on_requires_action parameter, then your customer should try another card that does not require authentication.', 'checkout-plugins-stripe-woo' ),
				'balance_insufficient'                   => __( 'The transfer or payout could not be completed because the associated account does not have a sufficient balance available. Create a new transfer or payout using an amount less than or equal to the account’s available balance.', 'checkout-plugins-stripe-woo' ),
				'bank_account_declined'                  => __( 'The bank account provided can not be used either because it is not verified yet or it is not supported.', 'checkout-plugins-stripe-woo' ),
				'bank_account_unusable'                  => __( 'The bank account provided cannot be used. Please try a different bank account.', 'checkout-plugins-stripe-woo' ),
				'setup_intent_unexpected_state'          => __( 'The SetupIntent\'s state was incompatible with the operation you were trying to perform.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_action_required'         => __( 'The provided payment method requires customer action to complete. If you\'d like to add this payment method, please upgrade your integration to handle actions.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_authentication_failure'  => __( 'The provided payment method failed authentication. Provide a new payment method to attempt this payment again.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_incompatible_payment_method' => __( 'The Payment expected a payment method with different properties than what was provided.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_invalid_parameter'       => __( 'One or more provided parameters was not allowed for the given operation on the Payment.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_mandate_invalid'         => __( 'The provided mandate is invalid and can not be used for the payment intent.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_payment_attempt_expired' => __( 'The latest attempt for this Payment has expired. Provide a new payment method to attempt this Payment again.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_unexpected_state'        => __( 'The PaymentIntent\'s state was incompatible with the operation you were trying to perform.', 'checkout-plugins-stripe-woo' ),
				'payment_method_billing_details_address_missing' => __( 'The PaymentMethod\'s billing details is missing address details. Please update the missing fields and try again.', 'checkout-plugins-stripe-woo' ),
				'payment_method_currency_mismatch'       => __( 'The currency specified does not match the currency for the attached payment method. A payment can only be created for the same currency as the corresponding payment method.', 'checkout-plugins-stripe-woo' ),
				'processing_error'                       => __( 'An error occurred while processing the card. Use a different payment method or try again later.', 'checkout-plugins-stripe-woo' ),
				'token_already_used'                     => __( 'The token provided has already been used. You must create a new token before you can retry this request.', 'checkout-plugins-stripe-woo' ),
				'invalid_number'                         => __( 'The card number is invalid. Check the card details or use a different card.', 'checkout-plugins-stripe-woo' ),
				'invalid_card_type'                      => __( 'The card provided as an external account is not supported for payouts. Provide a non-prepaid debit card instead.', 'checkout-plugins-stripe-woo' ),
				'invalid_charge_amount'                  => __( 'The specified amount is invalid. The charge amount must be a positive integer in the smallest currency unit, and not exceed the minimum or maximum amount.', 'checkout-plugins-stripe-woo' ),
				'invalid_cvc'                            => __( 'The card\'s security code is invalid. Check the card\'s security code or use a different card.', 'checkout-plugins-stripe-woo' ),
				'invalid_expiry_year'                    => __( 'The card\'s expiration year is incorrect. Check the expiration date or use a different card.', 'checkout-plugins-stripe-woo' ),
				'invalid_source_usage'                   => __( 'The source cannot be used because it is not in the correct state.', 'checkout-plugins-stripe-woo' ),
				'incorrect_address'                      => __( 'The address entered for the card is invalid. Please check the address or try a different card.', 'checkout-plugins-stripe-woo' ),
				'incorrect_cvc'                          => __( 'The security code entered is invalid. Please try again.', 'checkout-plugins-stripe-woo' ),
				'incorrect_number'                       => __( 'The card number entered is invalid. Please try again with a valid card number or use a different card.', 'checkout-plugins-stripe-woo' ),
				'incorrect_zip'                          => __( 'The postal code entered for the card is invalid. Please try again.', 'checkout-plugins-stripe-woo' ),
				'missing'                                => __( 'Both a customer and source ID have been provided, but the source has not been saved to the customer. To create a charge for a customer with a specified source, you must first save the card details.', 'checkout-plugins-stripe-woo' ),
				'email_invalid'                          => __( 'The email address is invalid. Check that the email address is properly formatted and only includes allowed characters.', 'checkout-plugins-stripe-woo' ),
				// Card declined started here.
				'card_declined'                          => __( 'The card has been declined. When a card is declined, the error returned also includes the decline_code attribute with the reason why the card was declined.', 'checkout-plugins-stripe-woo' ),
				'insufficient_funds'                     => __( 'The card has insufficient funds to complete the purchase.', 'checkout-plugins-stripe-woo' ),
				'generic_decline'                        => __( 'The card has been declined. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'lost_card'                              => __( 'The card has been declined (Lost card). Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'stolen_card'                            => __( 'The card has been declined (Stolen card). Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				// Card declined end here.
				'parameter_unknown'                      => __( 'The request contains one or more unexpected parameters. Remove these and try again.', 'checkout-plugins-stripe-woo' ),
				'incomplete_number'                      => __( 'Your card number is incomplete.', 'checkout-plugins-stripe-woo' ),
				'incomplete_expiry'                      => __( 'Your card\'s expiration date is incomplete.', 'checkout-plugins-stripe-woo' ),
				'incomplete_cvc'                         => __( 'Your card\'s security code is incomplete.', 'checkout-plugins-stripe-woo' ),
				'incomplete_zip'                         => __( 'Your card\'s zip code is incomplete.', 'checkout-plugins-stripe-woo' ),
				'stripe_cc_generic'                      => __( 'There was an error processing your credit card.', 'checkout-plugins-stripe-woo' ),
				'invalid_expiry_year_past'               => __( 'Your card\'s expiration year is in the past.', 'checkout-plugins-stripe-woo' ),
				'bank_account_verification_failed'       => __(
					'The bank account cannot be verified, either because the microdeposit amounts provided do not match the actual amounts, or because verification has failed too many times.',
					'checkout-plugins-stripe-woo'
				),
				'card_decline_rate_limit_exceeded'       => __(
					'This card has been declined too many times. You can try to charge this card again after 24 hours. We suggest reaching out to your customer to make sure they have entered all of their information correctly and that there are no issues with their card.',
					'checkout-plugins-stripe-woo'
				),
				'charge_already_captured'                => __( 'The charge you\'re attempting to capture has already been captured. Update the request with an uncaptured charge ID.', 'checkout-plugins-stripe-woo' ),
				'charge_already_refunded'                => __(
					'The charge you\'re attempting to refund has already been refunded. Update the request to use the ID of a charge that has not been refunded.',
					'checkout-plugins-stripe-woo'
				),
				'charge_disputed'                        => __(
					'The charge you\'re attempting to refund has been charged back. Check the disputes documentation to learn how to respond to the dispute.',
					'checkout-plugins-stripe-woo'
				),
				'charge_exceeds_source_limit'            => __(
					'This charge would cause you to exceed your rolling-window processing limit for this source type. Please retry the charge later, or contact us to request a higher processing limit.',
					'checkout-plugins-stripe-woo'
				),
				'charge_expired_for_capture'             => __(
					'The charge cannot be captured as the authorization has expired. Auth and capture charges must be captured within seven days.',
					'checkout-plugins-stripe-woo'
				),
				'charge_invalid_parameter'               => __(
					'One or more provided parameters was not allowed for the given operation on the Charge. Check our API reference or the returned error message to see which values were not correct for that Charge.',
					'checkout-plugins-stripe-woo'
				),
				'account_number_invalid'                 => __( 'The bank account number provided is invalid (e.g., missing digits). Bank account information varies from country to country. We recommend creating validations in your entry forms based on the bank account formats we provide.', 'checkout-plugins-stripe-woo' ),
			]
		);

		// if need all messages.
		if ( empty( $code ) ) {
			return $localized_messages;
		}

		return isset( $localized_messages[ $code ] ) ? $localized_messages[ $code ] : $message;
	}
}
