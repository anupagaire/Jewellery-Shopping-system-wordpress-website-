<?php
/**
 * SEPA Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.4.0
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Helper;
use CPSW\Inc\Logger;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Inc\Traits\Subscriptions;
use CPSW\Gateway\Local_Gateway;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Token;
use Exception;
use WP_Error;
use WC_AJAX;

/**
 * SEPA
 *
 * @since 1.4.0
 */
class Sepa extends Local_Gateway {

	use Get_Instance;
	use Subscriptions;

	/**
	 * Gateway id
	 *
	 * @var string
	 */
	public $id = 'cpsw_sepa';

	/**
	 * Payment method types
	 *
	 * @var string
	 */
	public $payment_method_types = 'sepa_debit';

	/**
	 * Retry interval
	 *
	 * @var int
	 */
	public $retry_interval = 1;

	/**
	 * Constructor
	 *
	 * @since 1.4.0
	 */
	public function __construct() {
		parent::__construct();

		$this->method_title       = __( 'SEPA', 'checkout-plugins-stripe-woo' );
		$this->method_description = $this->method_description();
		$this->has_fields         = true;
		$this->init_supports();
		$this->maybe_init_subscriptions();

		$this->init_form_fields();
		$this->init_settings();
		// get_option should be called after init_form_fields().
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->order_button_text    = $this->get_option( 'order_button_text' );
		$this->enable_saved_cards   = $this->get_option( 'enable_saved_card' );
		$this->company_name         = $this->get_option( 'company_name' );
		$this->statement_descriptor = $this->clean_statement_descriptor( $this->get_option( 'statement_descriptor' ) );
		$this->payment_conform      = true;

		add_action( 'wc_ajax_' . $this->id . '_verify_payment_intent', [ $this, 'verify_intent' ] );
		add_action( 'woocommerce_payment_token_class', [ $this, 'modify_token_class' ], 15, 2 );
		add_filter( 'woocommerce_payment_methods_list_item', [ $this, 'get_saved_payment_methods_list' ], 10, 2 );
		add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 999, 2 );
	}

	/**
	 * Controls the output on the my account page.
	 *
	 * @since 1.4.0
	 *
	 * @param  array            $item  Individual list item from woocommerce_saved_payment_methods_list.
	 * @param  WC_Payment_Token $token The payment token associated with this method entry.
	 *
	 * @return array $item
	 */
	public function get_saved_payment_methods_list( $item, $token ) {
		if ( 'cpsw_sepa' === strtolower( $token->get_type() ) ) {
			$item['method']['last4'] = $token->get_last4();
			$item['method']['brand'] = esc_html__( 'SEPA IBAN', 'checkout-plugins-stripe-woo' );
		}

		return $item;
	}

	/**
	 * Added token class
	 *
	 * @since 1.4.0
	 *
	 * @param string $class token class name.
	 * @param string $type gateway name.
	 *
	 * @return string
	 */
	public function modify_token_class( $class, $type ) {
		if ( 'cpsw_sepa' === $type ) {
			return 'CPSW\Inc\Token';
		}

		return $class;
	}

	/**
	 * Description for SEPA gateway
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function method_description() {
		$payment_description = $this->payment_description();

		return sprintf(
			/* translators: %1$s: Break, %2$s: HTML entities */
			__( 'Accept payment using SEPA. %1$s %2$s', 'checkout-plugins-stripe-woo' ),
			'<br/>',
			$payment_description
		);
	}

	/**
	 * Returns all supported currencies for this payment method.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_supported_currency() {
		return apply_filters(
			'cpsw_sepa_supported_currencies',
			[
				'EUR',
			]
		);
	}

	/**
	 * Init payment supports.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'tokenization';
		$this->supports[] = 'add_payment_method';
		$this->supports[] = 'pre-orders';
	}

	/**
	 * Process payment method functionality
	 *
	 * @return array
	 */
	public function add_payment_method() {
		$source_id = '';
		if ( empty( $_POST['payment_method_created'] ) && empty( $_POST['stripe_token'] ) || ! is_user_logged_in() ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$error_msg = __( 'There was a problem adding the payment method.', 'checkout-plugins-stripe-woo' );
			/* translators: error msg */
			Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
			return;
		}

		$customer_id   = $this->get_customer_id();
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
			$error_msg = __( 'Unble to attach payment method to customer', 'checkout-plugins-stripe-woo' );
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
	 * Add more gateway form fields
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_default_settings() {
		$company_name = [
			'enable_saved_card'    => [
				'label'       => __( 'Enable Payment via Saved IBAN', 'checkout-plugins-stripe-woo' ),
				'title'       => __( 'Saved IBAN', 'checkout-plugins-stripe-woo' ),
				'type'        => 'checkbox',
				'description' => __( 'Save IBAN details for future orders', 'checkout-plugins-stripe-woo' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			],
			'statement_descriptor' => [
				'title'       => __( 'Statement Descriptor', 'checkout-plugins-stripe-woo' ),
				'type'        => 'text',
				'description' => __( 'Statement descriptors are limited to 22 characters, cannot use the special characters >, <, ", \, *, /, (, ), {, }, and must not consist solely of numbers. This will appear on your customer\'s statement in capital letters.', 'checkout-plugins-stripe-woo' ),
				'default'     => get_bloginfo( 'name' ),
				'desc_tip'    => true,
			],
			'company_name'         => [
				'title'       => __( 'Company Name', 'checkout-plugins-stripe-woo' ),
				'type'        => 'text',
				'default'     => get_bloginfo( 'name' ),
				'desc_tip'    => true,
				'description' => __( 'The name of your company that will appear in the SEPA mandate info.', 'checkout-plugins-stripe-woo' ),
			],
		];

		$local_settings = parent::get_default_settings();

		$local_settings['description']['default'] = __( 'Mandate Information.', 'checkout-plugins-stripe-woo' );

		return array_merge( $local_settings, $company_name );
	}

	/**
	 * Checks whether this gateway is available.
	 *
	 * @since 1.4.0
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( ! in_array( $this->get_currency(), $this->get_supported_currency(), true ) ) {
			return false;
		}

		if ( ! Helper::get_webhook_secret() ) {
			return false;
		}

		return parent::is_available();
	}

	/**
	 * Renders the Stripe elements form.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function payment_form() {
		// translators: %s: company name.
		$description = sprintf( __( 'By providing your IBAN and confirming this payment, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'checkout-plugins-stripe-woo' ), $this->get_option( 'company_name' ) );
		?>
		<fieldset id="<?php echo esc_attr( $this->id ); ?>-form" class="wc-payment-form cpsw_stripe_sepa_payment_form">
			<?php echo wp_kses_post( wpautop( $description ) ); ?>
			<p class="form-row form-row-wide">
				<label for="cpsw-sepa-stripe-iban-element">
					<?php esc_html_e( 'IBAN.', 'checkout-plugins-stripe-woo' ); ?> <span class="required">*</span>
				</label>
				<div id="cpsw_stripe_sepa_iban_element" class="cpsw_stripe_sepa_iban_element_field">
					<!-- A Stripe Element will be inserted here. -->
				</div>
			</p>

			<!-- Used to display form errors -->
			<div class="cpsw_stripe_sepa_error" role="alert"></div>
			<div class="clear"></div>
		</fieldset>
		<?php
	}

	/**
	 * Creates markup for payment form for card payments
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function payment_fields() {
		$total                = WC()->cart->total;
		$display_tokenization = $this->supports( 'tokenization' ) && is_checkout() && 'yes' === $this->enable_saved_cards && is_user_logged_in();
		$description          = $this->get_description();
		$description          = ! empty( $description ) ? $description : '';

		// If paying from order, we need to get total from order not cart. Frontend summary nonce verification may not be required.
		if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			global $wp;

			$order = wc_get_order( wc_clean( $wp->query_vars['order-pay'] ) );
			$total = $order->get_total();
		}

		if ( is_add_payment_method_page() ) {
			$total = '';
		}

		/**
		 * Action before payment field.
		 *
		 * @since 1.4.0
		 */
		do_action( $this->id . '_before_payment_field_checkout' );

		echo '<div
			id="cpsw-sepa-payment-data"
			data-amount="' . esc_attr( $this->get_formatted_amount( $total ) ) . '"
			data-currency="' . esc_attr( strtolower( get_woocommerce_currency() ) ) . '">';

		if ( $display_tokenization ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
		}

		$this->payment_form();

		// Nonce verification already done in parent WooCommerce function.
		if ( apply_filters( 'cpsw_sepa_display_save_payment_method_checkbox', $display_tokenization ) && ! $this->is_subscription_item_in_cart() && ! is_add_payment_method_page() && ! isset( $_GET['change_payment_method'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->save_payment_method_checkbox();
		}

		if ( 'live' !== Helper::get_payment_mode() ) {
			echo '<div class="cpsw-test-description">';
			/* translators: %1$1s - %6$6s: HTML Markup */
			printf( esc_html__( '%1$1s Test Mode Enabled %2$2s : Use demo IBAN number DE89370400440532013000 for test payment. %3$3s Check more %4$4sDemo IBAN Number%5$5s', 'checkout-plugins-stripe-woo' ), '<b>', '</b>', '</br>', "<a href='https://stripe.com/docs/testing#sepa-direct-debit' referrer='noopener' target='_blank'>", '</a>' );
			echo '</div>';
		}

		echo '</div>';

		/**
		 * Action after payment field.
		 *
		 * @since 1.4.0
		 */
		do_action( $this->id . '_after_payment_field_checkout' );
	}

	/**
	 * Tokenize card payment
	 *
	 * @since 1.4.0
	 *
	 * @param int    $user_id id of current user placing .
	 * @param object $payment_method payment method object.
	 *
	 * @return object token object.
	 */
	public function create_payment_token_for_user( $user_id, $payment_method ) {
		$token = new Token();
		$token->set_last4( $payment_method->sepa_debit->last4 );
		$token->set_gateway_id( $this->id );
		$token->set_token( $payment_method->id );
		$token->set_user_id( $user_id );
		$token->save();

		return $token;
	}

	/**
	 * Process woocommerce orders after payment is done
	 *
	 * @since 1.4.0
	 *
	 * @param int $order_id wooCommerce order id.
	 *
	 * @return array data to redirect after payment processing.
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
			$customer_id     = $this->get_customer_id( $order );
			$idempotency_key = $order->get_order_key() . time();

			$data = [
				'amount'               => $this->get_formatted_amount( $order->get_total() ),
				'currency'             => $this->get_currency(),
				'description'          => $this->get_order_description( $order ),
				'metadata'             => $this->get_metadata( $order_id ),
				'payment_method_types' => [ 'sepa_debit' ],
				'customer'             => $customer_id,
			];

			if ( ! empty( trim( $this->statement_descriptor ) ) ) {
				$data['statement_descriptor'] = $this->statement_descriptor;
			}

			if ( $this->should_save_card( $order_id ) ) {
				$data['setup_future_usage'] = 'off_session';
			}

			/* translators: %1$1s order id, %2$2s order total amount  */
			Logger::info( sprintf( __( 'Begin processing payment with SEPA for order %1$1s for the amount of %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $order->get_total() ) );
			$intent_data = $this->get_payment_intent( $order_id, $idempotency_key, $data );

			if ( $intent_data ) {
				return [
					'result'        => 'success',
					'redirect'      => false,
					'intent_secret' => $intent_data['client_secret'],
					'save_card'     => $this->should_save_card( $order_id ),
				];
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
	 * Prepare payment method object
	 *
	 * @since 1.4.5
	 *
	 * @param object $payment_method payment method object from intent.
	 * @param object $token token object used for payment.
	 *
	 * @return object
	 */
	public function prepare_payment_method_zero_amount( $payment_method, $token ) {
		return (object) apply_filters(
			'cpsw_prepare_payment_method_args',
			[
				'token_id'              => $token,
				'customer'              => $payment_method->customer,
				'source'                => $payment_method->id,
				'source_object'         => $payment_method,
				'payment_method'        => $payment_method->id,
				'payment_method_object' => $payment_method,
			]
		);
	}

	/**
	 * Process zero amount order
	 *
	 * @since 1.4.5
	 *
	 * @param WC_Order $order WooCommerce order.
	 */
	public function process_zero_amount_order( $order ) {
		$token                   = '';
		$prepared_payment_method = '';

		// Nonce verification already done in parent woocommerce function.
		if ( isset( $_POST['payment_method_created'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$method_created          = ! empty( $_POST['payment_method_created'] ) ? wc_clean( wp_unslash( $_POST['payment_method_created'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe_api              = new Stripe_Api();
			$response                = $stripe_api->payment_methods( 'retrieve', [ $method_created ] );
			$payment_method          = $response['success'] ? $response['data'] : false;
			$user                    = $order->get_id() ? $order->get_user() : wp_get_current_user();
			$user_id                 = $user->ID;
			$token                   = $this->create_payment_token_for_user( $user_id, $payment_method );
			$prepared_payment_method = $this->prepare_payment_method( $payment_method, $token );
		} elseif ( $this->is_using_saved_payment_method() ) {
			// Nonce verification already done in parent woocommerce function.
			$token                   = $this->get_token_from_request( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe_api              = new Stripe_Api();
			$response                = $stripe_api->payment_methods( 'retrieve', [ $token ] );
			$payment_method          = $response['success'] ? $response['data'] : false;
			$prepared_payment_method = $this->prepare_payment_method_zero_amount( $payment_method, $token );
		}

		if ( ! empty( $token ) ) {
			$order->payment_complete();
			$this->save_payment_method_to_order( $order, $prepared_payment_method );

			return $this->process_change_subscription_payment_method( $order->get_id() );
		}

		return false;
	}

	/**
	 * Verify intent state and redirect.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function verify_intent() {
		$order_id = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order    = wc_get_order( $order_id );

		$intent_secret = $order->get_meta( '_cpsw_intent_secret' );
		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_intents( 'retrieve', [ $intent_secret['id'] ] );
		$intent        = $response['success'] ? $response['data'] : false;

		if ( ! $intent ) {
			return;
		}

		if ( isset( $_GET['save_card'] ) && '1' === $_GET['save_card'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$user           = $order->get_id() ? $order->get_user() : wp_get_current_user();
			$user_id        = $user->ID;
			$payment_method = $intent->payment_method;
			$response       = $stripe_api->payment_methods( 'retrieve', [ $payment_method ] );
			$payment_method = $response['success'] ? $response['data'] : false;
			$token          = $this->create_payment_token_for_user( $user_id, $payment_method );
			/* translators: %1$1s order id, %2$2s token id  */
			Logger::info( sprintf( __( 'Payment method tokenized for Order id - %1$1s with token id - %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $token->get_id() ) );
			$prepared_payment_method = $this->prepare_payment_method( $payment_method, $token );
			$this->save_payment_method_to_order( $order, $prepared_payment_method );
		}

		if ( 'succeeded' === $intent->status ) {
			$redirect_to  = $this->process_order( end( $intent->charges->data ), $order_id );
			$redirect_url = apply_filters( 'cpsw_redirect_order_url', $redirect_to, $order );
		} elseif ( 'pending' === $intent->status || 'processing' === $intent->status ) {
			$order_stock_reduced = $order->get_meta( '_order_stock_reduced', true );

			if ( ! $order_stock_reduced ) {
				wc_reduce_stock_levels( $order_id );
			}

			$order->set_transaction_id( $intent->id );
			$others_info = __( 'Payment will be completed once payment_intent.succeeded webhook received from Stripe.', 'checkout-plugins-stripe-woo' );

			/* translators: transaction id, other info */
			$order->update_status( 'on-hold', sprintf( __( 'Stripe charge awaiting payment: %1$s. %2$s', 'checkout-plugins-stripe-woo' ), $intent->id, $others_info ) );
			$redirect_to  = $this->get_return_url( $order );
			$redirect_url = apply_filters( 'cpsw_redirect_order_url', $redirect_to, $order );
		} elseif ( isset( $response['data']->last_payment_error ) ) {
			$message = isset( $response['data']->last_payment_error->message ) ? $response['data']->last_payment_error->message : '';

			// translators: %s: payment fail message.
			wc_add_notice( sprintf( __( 'Payment failed. %s', 'checkout-plugins-stripe-woo' ), $message ), $notice_type = 'error' );
			$redirect_url = wc_get_checkout_url();
		}
		wp_safe_redirect( $redirect_url );
		exit();
	}

	/**
	 * Modify redirect url
	 *
	 * @since 1.4.0
	 *
	 * @param array $result redirect url array.
	 * @param int   $order_id woocommerce order id.
	 *
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
			WC_AJAX::get_endpoint( $this->id . '_verify_payment_intent' )
		);

		// Combine into a hash.
		$redirect = sprintf( '#confirm-pi-%s:%s:%s', $result['intent_secret'], rawurlencode( $verification_url ), $this->id );

		return [
			'result'   => 'success',
			'redirect' => $redirect,
		];
	}
}
