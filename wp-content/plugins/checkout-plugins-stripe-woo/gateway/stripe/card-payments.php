<?php
/**
 * Stripe Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Helper;
use CPSW\Inc\Logger;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Inc\Traits\Subscriptions;
use CPSW\Gateway\Abstract_Payment_Gateway;
use CPSW\Gateway\Stripe\Stripe_Api;
use WC_AJAX;
use WC_HTTPS;
use WC_Payment_Token_CC;
use Exception;
use WP_Error;
/**
 * Card_Payments
 *
 * @since 0.0.1
 */
class Card_Payments extends Abstract_Payment_Gateway {

	use Get_Instance;
	use Subscriptions;

	/**
	 * Gateway id
	 *
	 * @var string
	 */
	public $id = 'cpsw_stripe';

	/**
	 * Payment method types
	 *
	 * @var string
	 */
	public $payment_method_types = 'card';

	/**
	 * Retry interval
	 *
	 * @var int
	 */
	public $retry_interval = 1;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		parent::__construct();

		$this->method_title       = __( 'Stripe Card Processing', 'checkout-plugins-stripe-woo' );
		$this->method_description = __( 'Accepts payments via Credit/Debit Cards', 'checkout-plugins-stripe-woo' );
		$this->has_fields         = true;
		$this->init_supports();
		$this->maybe_init_subscriptions();

		$this->init_form_fields();
		$this->init_settings();
		// get_option should be called after init_form_fields().
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->order_button_text    = $this->get_option( 'order_button_text' );
		$this->inline_cc            = $this->get_option( 'inline_cc' );
		$this->enable_saved_cards   = $this->get_option( 'enable_saved_cards' );
		$this->capture_method       = $this->get_option( 'charge_type' );
		$this->allowed_cards        = empty( $this->get_option( 'allowed_cards' ) ) ? [ 'mastercard', 'visa', 'diners', 'discover', 'amex', 'jcb', 'unionpay' ] : $this->get_option( 'allowed_cards' );
		$this->statement_descriptor = $this->clean_statement_descriptor( $this->get_option( 'statement_descriptor' ) );

		add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 999, 2 );
		add_action( 'wc_ajax_cpsw_verify_payment_intent', [ $this, 'verify_intent' ] );
		add_filter( 'woocommerce_payment_complete_order_status', [ $this, 'cpsw_payment_complete_order_status' ], 10, 3 );
	}

	/**
	 * Registers supported filters for payment gateway
	 *
	 * @return void
	 */
	public function init_supports() {
		$this->supports = apply_filters(
			'cpsw_card_payment_supports',
			[
				'products',
				'refunds',
				'tokenization',
				'add_payment_method',
				'pre-orders',
			]
		);
	}

	/**
	 * Gateway form fields
	 *
	 * @return void
$	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters(
			'cpsw_card_payment_form_fields',
			[
				'enabled'              => [
					'label'   => ' ',
					'type'    => 'checkbox',
					'title'   => __( 'Enable Stripe Gateway', 'checkout-plugins-stripe-woo' ),
					'default' => 'no',
				],
				'title'                => [
					'title'       => __( 'Title', 'checkout-plugins-stripe-woo' ),
					'type'        => 'text',
					'description' => __( 'Title of Card Element', 'checkout-plugins-stripe-woo' ),
					'default'     => __( 'Credit Card (Stripe)', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => true,
				],
				'description'          => [
					'title'       => __( 'Description', 'checkout-plugins-stripe-woo' ),
					'type'        => 'textarea',
					'css'         => 'width:25em',
					'description' => __( 'Description on Card Elements for Live mode', 'checkout-plugins-stripe-woo' ),
					'default'     => __( 'Pay with your credit card via Stripe', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => true,
				],
				'statement_descriptor' => [
					'title'       => __( 'Statement Descriptor', 'checkout-plugins-stripe-woo' ),
					'type'        => 'text',
					'description' => __( 'Statement descriptors are limited to 22 characters, cannot use the special characters >, <, ", \, *, /, (, ), {, }, and must not consist solely of numbers. This will appear on your customer\'s statement in capital letters.', 'checkout-plugins-stripe-woo' ),
					'default'     => get_bloginfo( 'name' ),
					'desc_tip'    => true,
				],
				'charge_type'          => [
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
				'enable_saved_cards'   => [
					'label'       => __( 'Enable Payment via Saved Cards', 'checkout-plugins-stripe-woo' ),
					'title'       => __( 'Saved Cards', 'checkout-plugins-stripe-woo' ),
					'type'        => 'checkbox',
					'description' => __( 'Save card details for future orders', 'checkout-plugins-stripe-woo' ),
					'default'     => 'yes',
					'desc_tip'    => true,
				],
				'inline_cc'            => [
					'label'       => __( 'Enable Inline Credit Card Form', 'checkout-plugins-stripe-woo' ),
					'title'       => __( 'Inline Credit Card Form', 'checkout-plugins-stripe-woo' ),
					'type'        => 'checkbox',
					'description' => __( 'Use inline credit card for card payments', 'checkout-plugins-stripe-woo' ),
					'default'     => 'yes',
					'desc_tip'    => true,
				],
				'allowed_cards'        => [
					'title'       => __( 'Allowed Cards', 'checkout-plugins-stripe-woo' ),
					'type'        => 'multiselect',
					'class'       => 'cpsw_select_woo',
					'desc_tip'    => __( 'Accepts payments using selected cards. If empty all stripe cards are accepted.', 'checkout-plugins-stripe-woo' ),
					'options'     => [
						'mastercard' => 'MasterCard',
						'visa'       => 'Visa',
						'amex'       => 'American Express',
						'discover'   => 'Discover',
						'jcb'        => 'JCB',
						'diners'     => 'Diners Club',
						'unionpay'   => 'UnionPay',
					],
					'default'     => [],
					'description' => __( 'Select cards for accepts payments. If empty all stripe cards are accepted.', 'checkout-plugins-stripe-woo' ),
				],
				'order_status'         => [
					'type'        => 'select',
					'title'       => __( 'Order Status', 'checkout-plugins-stripe-woo' ),
					'class'       => 'cpsw_select_woo',
					'options'     => [
						''              => __( 'Default', 'checkout-plugins-stripe-woo' ),
						'wc-processing' => __( 'Processing', 'checkout-plugins-stripe-woo' ),
						'wc-on-hold'    => __( 'On Hold', 'checkout-plugins-stripe-woo' ),
						'wc-completed'  => __( 'Completed', 'checkout-plugins-stripe-woo' ),
					],
					'default'     => '',
					'tool_tip'    => true,
					/* translators: HTML tag */
					'description' => sprintf( __( '%1$1sDefault%2$2s option is recommended. This option applies the WooCommerce\'s default status on order completion.%3$3sIf you want different order status on order completion, you can use any of the following %4$4sProcessing, On Hold, Completed%5$5s as the order status.', 'checkout-plugins-stripe-woo' ), '<strong>', '</strong>', '<br/>', '<strong>', '</strong>' ),

				],
				'order_button_text'    => [
					'title'       => __( 'Order Button Label', 'checkout-plugins-stripe-woo' ),
					'type'        => 'text',
					'description' => __( 'Customize label for Order button', 'checkout-plugins-stripe-woo' ),
					'default'     => __( 'Pay via Stripe', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => true,
				],
			]
		);
	}

	/**
	 * Process woocommerce orders after payment is done
	 *
	 * @param int $order_id wooCommerce order id.
	 * @return mixed data to redirect after payment processing.
	 */
	public function process_payment( $order_id ) {
		if ( $this->maybe_change_subscription_payment_method( $order_id ) ) {
			return $this->process_change_subscription_payment_method( $order_id );
		}

		$order = wc_get_order( $order_id );

		if ( 0 === absint( $order->get_total() ) ) {
			return $this->process_zero_amount_order( $order );
		}

		if ( $this->is_using_saved_payment_method() ) {
			return $this->process_payment_with_saved_payment_method( $order_id );
		}

		try {
			// Nonce verification already done in parent woocommerce function.
			if ( ! isset( $_POST['payment_method_created'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				return;
			}

			// Nonce verification already done in parent woocommerce function.
			$payment_method  = sanitize_text_field( $_POST['payment_method_created'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$customer_id     = $this->get_customer_id( $order );
			$idempotency_key = $payment_method . '_' . $order->get_order_key();

			$data = [
				'amount'               => $this->get_formatted_amount( $order->get_total() ),
				'currency'             => get_woocommerce_currency(),
				'description'          => $this->get_order_description( $order ),
				'payment_method_types' => [ $this->payment_method_types ],
				'payment_method'       => $payment_method,
				'metadata'             => $this->get_metadata( $order_id ),
				'customer'             => $customer_id,
				'capture_method'       => $this->capture_method,
			];

			if ( ! empty( trim( $this->statement_descriptor ) ) ) {
				$data['statement_descriptor'] = $this->statement_descriptor;
			}

			if ( $this->should_save_card( $order_id ) ) {
				$data['setup_future_usage'] = 'off_session';
			}

			/* translators: %1$1s order id, %2$2s order total amount  */
			Logger::info( sprintf( __( 'Begin processing payment with new payment method for order %1$1s for the amount of %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $order->get_total() ) );
			$intent_data = $this->get_payment_intent( $order_id, $idempotency_key, apply_filters( 'cpsw_card_payment_intent_post_data', $data ) );
			if ( $intent_data ) {
				return apply_filters(
					'cpsw_card_payment_return_intent_data',
					[
						'result'         => 'success',
						'redirect'       => $this->get_return_url( $order ),
						'payment_method' => $payment_method,
						'intent_secret'  => $intent_data['client_secret'],
						'save_card'      => $this->should_save_card( $order_id ),
					]
				);
			} else {
				return [
					'result'   => 'fail',
					'redirect' => '',
				];
			}
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage(), true );
			return new WP_Error( 'order-error', '<div class="woocommerce-error">' . $e->getMessage() . '</div>', [ 'status' => 200 ] );
		}
	}

	/**
	 * Process zero amount order
	 *
	 * @since 1.4.5
	 *
	 * @param WC_Order $order WooCommerce order.
	 */
	public function process_zero_amount_order( $order ) {
		if ( 'automatic' === $this->capture_method ) {
			$order->payment_complete();
		} else {
			$order->update_status( apply_filters( 'cpsw_stripe_authorized_order_status', 'on-hold' ) );
		}

		return $this->process_change_subscription_payment_method( $order->get_id() );
	}

	/**
	 * Process payment method functionality
	 *
	 * @return mixed
	 */
	public function add_payment_method() {
		$source_id = '';

		if ( empty( $_POST['payment_method_created'] ) && empty( $_POST['stripe_token'] ) || ! is_user_logged_in() ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$error_msg = __( 'There was a problem adding the payment method.', 'checkout-plugins-stripe-woo' );
			/* translators: error msg */
			Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
			return;
		}

		$customer_id = $this->get_customer_id();

		$source        = ! empty( $_POST['payment_method_created'] ) ? wc_clean( wp_unslash( $_POST['payment_method_created'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_methods( 'retrieve', [ $source ] );
		$source_object = $response['success'] ? $response['data'] : false;

		if ( isset( $source_object ) ) {
			if ( ! empty( $source_object->error ) ) {
				$error_msg = __( 'Invalid stripe source', 'checkout-plugins-stripe-woo' );
				wc_add_notice( $error_msg, 'error' );
				/* translators: error msg */
				Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
				return;
			}

			$source_id = $source_object->id;
		}
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->payment_methods( 'attach', [ $source_id, [ 'customer' => $customer_id ] ] );
		$response   = $response['success'] ? $response['data'] : false;
		$user       = wp_get_current_user();
		$user_id    = ( $user->ID && $user->ID > 0 ) ? $user->ID : false;
		$this->create_payment_token_for_user( $user_id, $source_object );

		if ( ! $response || is_wp_error( $response ) || ! empty( $response->error ) ) {
			$error_msg = __( 'Unable to attach payment method to customer', 'checkout-plugins-stripe-woo' );
			wc_add_notice( $error_msg, 'error' );
			/* translators: error msg */
			Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
			return;
		}

		do_action( 'cpsw_add_payment_method_' . ( isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : '' ) . '_success', $source_id, $source_object ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

		Logger::info( __( 'New payment method added successfully', 'checkout-plugins-stripe-woo' ) );
		return [
			'result'   => 'success',
			'redirect' => wc_get_endpoint_url( 'payment-methods' ),
		];
	}

	/**
	 * Verify intent state and redirect.
	 *
	 * @return void
	 */
	public function verify_intent() {
		$order_id = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order    = wc_get_order( $order_id );

		$intent_secret = $order->get_meta( '_cpsw_intent_secret' );
		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_intents( 'retrieve', [ $intent_secret['id'] ] );
		$intent        = $response['success'] ? $response['data'] : false;

		if ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status ) {
			if ( ( isset( $_GET['save_card'] ) && '1' === $_GET['save_card'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$user           = $order->get_id() ? $order->get_user() : wp_get_current_user();
				$user_id        = isset( $user->ID ) ? $user->ID : 0;
				$payment_method = $intent->payment_method;
				$response       = $stripe_api->payment_methods( 'retrieve', [ $payment_method ] );
				$payment_method = $response['success'] ? $response['data'] : false;
				$token          = $this->create_payment_token_for_user( $user_id, $payment_method );
				/* translators: %1$1s order id, %2$2s token id  */
				Logger::info( sprintf( __( 'Payment method tokenized for Order id - %1$1s with token id - %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $token->get_id() ) );
				$prepared_payment_method = $this->prepare_payment_method( $payment_method, $token );
				$this->save_payment_method_to_order( $order, $prepared_payment_method );
			}
			$redirect_to  = $this->process_order( end( $intent->charges->data ), $order_id );
			$redirect_url = apply_filters( 'cpsw_redirect_order_url', ! empty( $redirect ) ? $redirect : $redirect_to, $order );
		} elseif ( isset( $response['data']->last_payment_error ) ) {
			$message = isset( $response['data']->last_payment_error->message ) ? $response['data']->last_payment_error->message : '';
			$code    = isset( $response['data']->last_payment_error->code ) ? $response['data']->last_payment_error->code : '';
			$order->update_status( 'wc-failed' );

			// translators: %s: payment fail message.
			wc_add_notice( sprintf( __( 'Payment failed. %s', 'checkout-plugins-stripe-woo' ), Helper::get_localized_messages( $code, $message ) ), 'error' );
			$redirect_url = wc_get_checkout_url();
		}
		wp_safe_redirect( $redirect_url );
		exit();
	}

	/**
	 * Tokenize card payment
	 *
	 * @param int    $user_id id of current user placing .
	 * @param object $payment_method payment method object.
	 * @return object token object.
	 */
	public function create_payment_token_for_user( $user_id, $payment_method ) {
		$token = new WC_Payment_Token_CC();
		$token->set_expiry_month( $payment_method->card->exp_month );
		$token->set_expiry_year( $payment_method->card->exp_year );
		$token->set_card_type( strtolower( $payment_method->card->brand ) );
		$token->set_last4( $payment_method->card->last4 );
		$token->set_gateway_id( $this->id );
		$token->set_token( $payment_method->id );
		$token->set_user_id( $user_id );
		$token->save();

		return $token;
	}

	/**
	 * Checks whether saved card settings is enabled ord not.
	 *
	 * @return bool
	 */
	public function enable_saved_cards() {
		return ( $this->supports( 'tokenization' ) && 'yes' === $this->enable_saved_cards && is_user_logged_in() ) ? true : false;
	}

	/**
	 * Modify redirect url
	 *
	 * @param array $result redirect url array.
	 * @param int   $order_id woocommerce order id.
	 * @return array modified redirect url array.
	 */
	public function modify_successful_payment_result( $result, $order_id ) {
		if ( empty( $order_id ) ) {
			return $result;
		}

		$order = wc_get_order( $order_id );
		if ( $this->id !== $order->get_payment_method() ) {
			return $result;
		}

		if ( ! isset( $result['intent_secret'] ) ) {
			return $result;
		}

		// Put the final thank you page redirect into the verification URL.
		$verification_url = add_query_arg(
			[
				'order'                 => $order_id,
				'confirm_payment_nonce' => wp_create_nonce( 'cpsw_confirm_payment_intent' ),
				'redirect_to'           => rawurlencode( $result['redirect'] ),
				'save_card'             => $result['save_card'],
			],
			WC_AJAX::get_endpoint( 'cpsw_verify_payment_intent' )
		);

		// Combine into a hash.
		$redirect = sprintf( '#confirm-pi-%s:%s:%s', $result['intent_secret'], rawurlencode( $verification_url ), $this->id );

		return [
			'result'   => 'success',
			'redirect' => $redirect,
		];
	}

	/**
	 * Get stripe activated payment cards icon.
	 */
	public function get_icon() {
		$ext   = version_compare( WC()->version, '2.6', '>=' ) ? '.svg' : '.png';
		$style = version_compare( WC()->version, '2.6', '>=' ) ? 'style="margin-left: 0.3em"' : '';
		$icon  = '';

		if ( ( in_array( 'visa', $this->allowed_cards, true ) ) || ( in_array( 'Visa', $this->allowed_cards, true ) ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/visa' . $ext ) . '" alt="Visa" width="32" title="VISA" ' . $style . ' />';
		}
		if ( ( in_array( 'mastercard', $this->allowed_cards, true ) ) || ( in_array( 'MasterCard', $this->allowed_cards, true ) ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard' . $ext ) . '" alt="Mastercard" width="32" title="Master Card" ' . $style . ' />';
		}
		if ( ( in_array( 'amex', $this->allowed_cards, true ) ) || ( in_array( 'American Express', $this->allowed_cards, true ) ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex' . $ext ) . '" alt="Amex" width="32" title="American Express" ' . $style . ' />';
		}
		if ( 'USD' === get_woocommerce_currency() ) {
			if ( ( in_array( 'discover', $this->allowed_cards, true ) ) || ( in_array( 'Discover', $this->allowed_cards, true ) ) ) {
				$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover' . $ext ) . '" alt="Discover" width="32" title="Discover" ' . $style . ' />';
			}
			if ( ( in_array( 'jcb', $this->allowed_cards, true ) ) || ( in_array( 'JCB', $this->allowed_cards, true ) ) ) {
				$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/jcb' . $ext ) . '" alt="JCB" width="32" title="JCB" ' . $style . ' />';
			}
			if ( ( in_array( 'diners', $this->allowed_cards, true ) ) || ( in_array( 'Diners Club', $this->allowed_cards, true ) ) ) {
				$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/diners' . $ext ) . '" alt="Diners" width="32" title="Diners Club" ' . $style . ' />';
			}
		}
		return apply_filters( 'woocommerce_gateway_icon', '<span class="cpsw_stripe_icons">' . $icon . '</span>', $this->id );
	}

	/**
	 * Creates markup for payment form for card payments
	 *
	 * @return void
	 */
	public function payment_fields() {
		if ( 'live' === Helper::get_payment_mode() && ! is_ssl() ) {
			/* translators: %1$1s, %2$2s: HTML Markup */
			echo '<span class="cpsw-stripe-error">' . sprintf( esc_html__( 'Live Stripe.js integrations must use HTTPS. %1$1s For more information:%2$2s', 'checkout-plugins-stripe-woo' ), '<br/><a href="https://stripe.com/docs/security/guide#tls" target="_blank" rel="noopener">', '</a>' ) . '</span></span>';
			return;
		}
		$display_tokenization = $this->supports( 'tokenization' ) && is_checkout();

		/**
		 * Action before payment field.
		 *
		 * @since 1.3.0
		 */
		do_action( $this->id . '_before_payment_field_checkout' );

		echo '<div class="status-box"></div>';
		echo '<div class="cpsw-stipe-pay-data">';
		echo '<div class="cpsw-stripe-info">';
		echo wp_kses_post( wpautop( $this->description ) );
		echo '</div>';
		if ( $display_tokenization ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
		}
		echo '<div class="cpsw-stripe-elements-form">';
		if ( 'yes' === $this->inline_cc ) {
			?>
			<span class="cpsw-cc"></span>
			<span class="cpsw-stripe-error"></span>
			<?php
		}
		if ( 'no' === $this->inline_cc ) {
			?>
			<strong><?php esc_html_e( 'Enter Card Details', 'checkout-plugins-stripe-woo' ); ?>:</strong>
			<span class="cpsw-number"></span><span class="cpsw-number-error"></span>
			<span class="cpsw-expiry-wrapper">
				<strong><?php esc_html_e( 'Expiry Date', 'checkout-plugins-stripe-woo' ); ?></strong>
				<span class="cpsw-expiry"></span><span class="cpsw-expiry-error"></span>
			</span>
			<span class="cpsw-cvc-wrapper">
				<strong><?php esc_html_e( 'CVC', 'checkout-plugins-stripe-woo' ); ?></strong>
				<span class="cpsw-cvc"></span><span class="cpsw-cvc-error"></span>
			</span>
			<?php
		}
		echo ( apply_filters( 'cpsw_display_save_payment_method_checkbox', $display_tokenization ) && $this->enable_saved_cards() ) ? '<span class="cpsw-save-cards"><label><input type="checkbox" name="wc-cpsw_stripe-new-payment-method" value="on"/>' . wp_kses_post( apply_filters( 'cpsw_saved_cards_label', __( 'Save Card for Future Payments', 'checkout-plugins-stripe-woo' ) ) ) . '</label></span>' : '';
		do_action( 'cpsw_payment_fields_' . $this->id, $this->id );
		echo '</div>';
		if ( 'test' === Helper::get_payment_mode() ) {
			echo '<div class="cpsw-test-description">';
			/* translators: %1$1s - %6$6s: HTML Markup */
			printf( esc_html__( '%1$1s Test Mode Enabled:%2$2s Use demo card 4242424242424242 with any future date and CVV. Check more %3$3sdemo cards%4$4s', 'checkout-plugins-stripe-woo' ), '<b>', '</b>', "<a href='https://stripe.com/docs/testing' referrer='noopener' target='_blank'>", '</a>' );
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

	/**
	 * Updates order status as per option 'order_status' set in card payment settings
	 *
	 * @param string   $order_status default order status.
	 * @param id       $order_id current order id.
	 * @param WC_Order $order current order.
	 * @return string
	 */
	public function cpsw_payment_complete_order_status( $order_status, $order_id, $order = null ) {
		if ( $order && $order->get_payment_method() ) {
			$gateway = $order->get_payment_method();
			if ( $this->id === $gateway && ! empty( $this->get_option( 'order_status' ) ) ) {
				$order_status = $this->get_option( 'order_status' );
			}
		}

		return apply_filters( 'cpsw_payment_complete_order_status', $order_status );
	}

	/**
	 * Checks whether current page is a product pare or not
	 *
	 * @return boolean
	 */
	public function is_product() {
		return is_product() || wc_post_content_has_shortcode( 'product_page' );
	}

	/**
	 * Prepare Sorce for current order
	 *
	 * @param WC_Order $order Current order.
	 * @return object
	 */
	public function prepare_order_source( $order = null ) {
		$stripe_customer = false;
		$stripe_source   = false;
		$source_object   = false;

		if ( $order ) {

			$stripe_customer_id = $this->get_cpsw_customer_id( $order );

			if ( $stripe_customer_id ) {

				$stripe_customer       = [];
				$stripe_customer['id'] = $stripe_customer_id;
			}

			$source_id = $order->get_meta( '_cpsw_source_id', true );

			if ( $source_id ) {
				$stripe_source = $source_id;
				$stripe_api    = new Stripe_Api();
				$response      = $stripe_api->payment_methods( 'retrieve', [ $stripe_source ] );
				$source_object = $response['success'] ? $response['data'] : false;
			} elseif ( apply_filters( 'cpsw_use_default_customer_source', true ) ) {
				$stripe_source = '';
			}
		}

		return (object) [
			'customer'      => $stripe_customer ? $stripe_customer['id'] : false,
			'source'        => $stripe_source,
			'source_object' => $source_object,
		];
	}
}
