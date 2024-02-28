<?php
/**
 * Abstract Payment Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Gateway;

use WC_Payment_Gateway;
use CPSW\Inc\Helper;
use CPSW\Inc\Logger;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Traits\Subscriptions;
use WP_Error;
use WC_Payment_Tokens;
use Exception;

/**
 * Abstract Payment Gateway
 *
 * @since 0.0.1
 */
abstract class Abstract_Payment_Gateway extends WC_Payment_Gateway {

	use Subscriptions;

	/**
	 * Url of assets directory
	 *
	 * @var string
	 */
	private $assets_url = CPSW_URL . 'assets/';

	/**
	 * Zero currencies accepted by stripe.
	 *
	 * @var array
	 */
	private static $zero_currencies = [ 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VUV', 'XAF', 'XOF', 'XPF', 'VND' ];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_payment_gateways', [ $this, 'add_gateway_class' ], 999 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_admin_order_totals_after_total', [ $this, 'get_stripe_order_data' ] );
		add_action( 'wp_ajax_create_setup_intent', [ $this, 'create_setup_intent' ] );
	}

	/**
	 * Adds transaction url in order details page
	 *
	 * @param WC_Order $order current order.
	 * @return string
	 */
	public function get_transaction_url( $order ) {
		if ( 'test' === Helper::get_payment_mode() ) {
			$this->view_transaction_url = 'https://dashboard.stripe.com/test/payments/%s';
		} else {
			$this->view_transaction_url = 'https://dashboard.stripe.com/payments/%s';
		}

		return parent::get_transaction_url( $order );
	}

	/**
	 * Get Order description string
	 *
	 * @param WC_Order $order current order.
	 * @return string
	 */
	public function get_order_description( $order ) {
		return apply_filters( 'cpsw_get_order_description', get_bloginfo( 'name' ) . ' - ' . __( 'Order ', 'checkout-plugins-stripe-woo' ) . $order->get_id() );
	}

	/**
	 * Registering Gateway to WooCommerce
	 *
	 * @param array $methods List of registered gateways.
	 * @return array
	 */
	public function add_gateway_class( $methods ) {
		array_unshift( $methods, $this );
		return $methods;
	}

	/**
	 * Get billing countries for gateways
	 *
	 * @since 1.2.0
	 *
	 * @return string $billing_country
	 */
	public function get_billing_country() {
		global $wp;

		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order           = wc_get_order( absint( $wp->query_vars['order-pay'] ) );
			$billing_country = $order->get_billing_country();
		} else {
			$customer        = WC()->customer;
			$billing_country = $customer ? $customer->get_billing_country() : null;

			if ( ! $billing_country ) {
				$billing_country = WC()->countries->get_base_country();
			}
		}

		return $billing_country;
	}

	/**
	 * Get WooCommerce currency
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_currency() {
		global $wp;

		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order = wc_get_order( absint( $wp->query_vars['order-pay'] ) );

			return $order->get_currency();
		}

		return get_woocommerce_currency();
	}

	/**
	 * Checks whether this gateway is available.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'yes' !== $this->enabled ) {
			return false;
		}

		if ( ! Helper::get_payment_mode() && is_checkout() ) {
			return false;
		}

		if ( 'test' === Helper::get_payment_mode() ) {
			if ( empty( Helper::get_setting( 'cpsw_test_pub_key' ) ) || empty( Helper::get_setting( 'cpsw_test_secret_key' ) ) ) {
				return false;
			}
		} else {
			if ( empty( Helper::get_setting( 'cpsw_pub_key' ) ) || empty( Helper::get_setting( 'cpsw_secret_key' ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get/Retrieve stripe customer id if exists
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $order current woocommerce order.
	 *
	 * @return mixed customer id
	 */
	public function get_customer_id( $order = false ) {
		$user        = wp_get_current_user();
		$user_id     = ( $user->ID && $user->ID > 0 ) ? $user->ID : false;
		$customer_id = false;

		if ( $user_id ) {
			$customer_id = get_user_option( '_cpsw_customer_id', $user_id );
			if ( $customer_id ) {
				return $customer_id;
			}
		}

		$customer = false;
		if ( ! $customer_id ) {
			$customer = $this->create_stripe_customer( $order, $user->email );
		}

		if ( $customer ) {
			if ( $user_id ) {
				update_user_option( $user_id, '_cpsw_customer_id', $customer->id, false );
			}
			return $customer->id;
		}
	}

	/**
	 * Creates stripe customer object
	 *
	 * @since 1.0.0
	 *
	 * @param object         $order woocommerce order object.
	 * @param boolean|string $user_email user email id.
	 *
	 * @return Stripe::Customer
	 */
	public function create_stripe_customer( $order = false, $user_email = false ) {
		$args = [
			'email' => $user_email,
		];

		if ( $order ) {
			$args = [
				'description' => 'Customer for Order #' . $order->get_id(),
				'email'       => $user_email ? $user_email : $order->get_billing_email(),
				'address'     => [ // sending name and billing address to stripe to support indian exports.
					'city'        => method_exists( $order, 'get_billing_city' ) ? $order->get_billing_city() : $order->billing_city,
					'country'     => method_exists( $order, 'get_billing_country' ) ? $order->get_billing_country() : $order->billing_country,
					'line1'       => method_exists( $order, 'get_billing_address_1' ) ? $order->get_billing_address_1() : $order->billing_address_1,
					'line2'       => method_exists( $order, 'get_billing_address_2' ) ? $order->get_billing_address_2() : $order->billing_address_2,
					'postal_code' => method_exists( $order, 'get_billing_postcode' ) ? $order->get_billing_postcode() : $order->billing_postcode,
					'state'       => method_exists( $order, 'get_billing_state' ) ? $order->get_billing_state() : $order->billing_state,
				],
				'name'        => ( method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name ) . ' ' . ( method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name ),
			];
		}

		$args       = apply_filters( 'cpsw_create_stripe_customer_args', $args );
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->customers( 'create', [ $args ] );
		$response   = $response['success'] ? $response['data'] : false;

		if ( empty( $response->id ) ) {
			return false;
		}

		return apply_filters( 'cpsw_create_stripe_customer_response', $response );
	}

	/**
	 * Refunds amount from stripe and return true/false as result
	 *
	 * @param string $order_id order id.
	 * @param string $amount refund amount.
	 * @param string $reason reason of refund.
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		if ( 0 >= $amount ) {
			return false;
		}

		try {
			$reason          = empty( $reason ) ? __( 'N/A', 'checkout-plugins-stripe-woo' ) : '';
			$order           = wc_get_order( $order_id );
			$intent_secret   = $order->get_meta( '_cpsw_intent_secret', true );
			$response        = $this->create_refund_request( $order, $amount, $reason, $intent_secret['id'] );
			$refund_response = $response['success'] ? $response['data'] : false;

			if ( $refund_response ) {

				if ( isset( $refund_response->balance_transaction ) ) {
					$this->update_balance( $order, $refund_response->balance_transaction );
				}

				$refund_time = gmdate( 'Y-m-d H:i:s', time() );
				set_transient( '_cpsw_refund_id_cache_' . $order_id, $refund_response->id, 60 );
				set_transient( '_cpsw_refund_status_cache_' . $order_id, $refund_response->status, 60 );
				$order->update_meta_data( '_cpsw_refund_id', $refund_response->id );
				$order->add_order_note( __( 'Reason : ', 'checkout-plugins-stripe-woo' ) . $reason . '.<br>' . __( 'Amount : ', 'checkout-plugins-stripe-woo' ) . get_woocommerce_currency_symbol() . $amount . '.<br>' . __( 'Status : ', 'checkout-plugins-stripe-woo' ) . ucfirst( $refund_response->status ) . ' [ ' . $refund_time . ' ] ' . ( is_null( $refund_response->id ) ? '' : '<br>' . __( 'Transaction ID : ', 'checkout-plugins-stripe-woo' ) . $refund_response->id ) );
				Logger::info( __( 'Refund initiated: ', 'checkout-plugins-stripe-woo' ) . __( 'Reason : ', 'checkout-plugins-stripe-woo' ) . $reason . __( 'Amount : ', 'checkout-plugins-stripe-woo' ) . get_woocommerce_currency_symbol() . $amount . __( 'Status : ', 'checkout-plugins-stripe-woo' ) . ucfirst( $refund_response->status ) . ' [ ' . $refund_time . ' ] ' . ( is_null( $refund_response->id ) ? '' : __( 'Transaction ID : ', 'checkout-plugins-stripe-woo' ) . $refund_response->id ), true );

				if ( 'succeeded' === $refund_response->status ) {
					return true;
				} else {
					return new WP_Error( 'error', __( 'Your refund process is ', 'checkout-plugins-stripe-woo' ) . ucfirst( $refund_response->status ) );
				}
			} else {
				$order->add_order_note( __( 'Reason : ', 'checkout-plugins-stripe-woo' ) . $reason . '.<br>' . __( 'Amount : ', 'checkout-plugins-stripe-woo' ) . get_woocommerce_currency_symbol() . $amount . '.<br>' . __( ' Status : Failed ', 'checkout-plugins-stripe-woo' ) );
				Logger::error( $response['message'], true );
				return new WP_Error( 'error', $response['message'] );
			}
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage(), true );
		}
	}

	/**
	 * Process response for saved cards
	 *
	 * @param object $response intent response.
	 * @param object $order order response.
	 * @return array
	 */
	public function process_response( $response, $order ) {
		Logger::info( 'Processing: ' . $response->id );

		$order_id = $order->get_id();
		$captured = ( isset( $response->captured ) && $response->captured ) ? 'yes' : 'no';

		// Store charge data.
		$order->update_meta_data( '_cpsw_charge_captured', $captured );

		if ( isset( $response->balance_transaction ) ) {
			$this->update_balance( $order, $response->balance_transaction, true );
		}

		if ( 'yes' === $captured ) {
			/**
			 * Charge can be captured but in a pending state. Payment methods
			 * that are asynchronous may take couple days to clear. Webhook will
			 * take care of the status changes.
			 */
			if ( 'pending' === $response->status || 'processing' === $response->status ) {
				$order_stock_reduced = $order->get_meta( '_order_stock_reduced', true );

				if ( ! $order_stock_reduced ) {
					wc_reduce_stock_levels( $order_id );
				}

				$order->set_transaction_id( $response->id );
				$others_info = 'cpsw_sepa' === $this->id ? __( 'Payment will be completed once payment_intent.succeeded webhook received from Stripe.', 'checkout-plugins-stripe-woo' ) : '';

				/* translators: transaction id, other info */
				$order->update_status( 'on-hold', sprintf( __( 'Stripe charge awaiting payment: %1$s. %2$s', 'checkout-plugins-stripe-woo' ), $response->id, $others_info ) );
			}

			if ( 'succeeded' === $response->status ) {
				if ( $order->has_status( [ 'pending', 'failed', 'on-hold' ] ) ) {
					$order->payment_complete( $response->id );
				}

				/* translators: transaction id */
				$message = sprintf( __( 'Stripe charge complete (Charge ID: %s)', 'checkout-plugins-stripe-woo' ), $response->id );
				Logger::info( $message, true );
				$order->add_order_note( $message );
			}

			if ( 'failed' === $response->status ) {
				$message = __( 'Payment processing failed. Please retry.', 'checkout-plugins-stripe-woo' );
				Logger::error( $message, true );
				$order->add_order_note( $message );
			}
		} else {
			$order->set_transaction_id( $response->id );

			if ( $order->has_status( [ 'pending', 'failed', 'on-hold' ] ) ) {
				wc_reduce_stock_levels( $order_id );
			}

			/* translators: transaction id */
			$order_info = 'cpsw_sepa' === $this->id ? sprintf( __( 'Stripe charge awaiting payment: %1$s. Payment will be completed once payment_intent.succeeded webhook received from Stripe.', 'checkout-plugins-stripe-woo' ), $response->id ) : sprintf( __( 'Stripe charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization. Attempting to refund the order in part or in full will release the authorization and cancel the payment.', 'checkout-plugins-stripe-woo' ), $response->id );

			$order->update_status( 'on-hold', $order_info );
		}

		if ( is_callable( [ $order, 'save' ] ) ) {
			$order->save();
		}

		do_action( 'cpsw_process_response', $response, $order );

		return $response;
	}

	/**
	 * Basic details of logged in user
	 *
	 * @since 1.0.0
	 *
	 * @return array current user data.
	 */
	public function get_clients_details() {
		return apply_filters(
			'cswp_clients_details',
			[
				'ip'      => isset( $_SERVER['REMOTE_ADDR'] ) ? esc_url_raw( $_SERVER['REMOTE_ADDR'] ) : '',
				'agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
				'referer' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
			]
		);
	}

	/**
	 * Checks if payment intent available for current order or else creates new payment intent.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $order_id woocommerce order id.
	 * @param string  $idempotency_key unique idempotency key.
	 * @param array   $args payment_intent arguments.
	 * @param boolean $return_error_response if true returns complete error response array.
	 *
	 * @return array intent data.
	 */
	public function get_payment_intent( $order_id, $idempotency_key, $args, $return_error_response = false ) {
		$order         = wc_get_order( $order_id );
		$intent_secret = $order->get_meta( '_cpsw_intent_secret' );
		$client_secret = '';

		if ( ! empty( $intent_secret ) ) {
			$secret     = $intent_secret;
			$stripe_api = new Stripe_Api();
			$response   = $stripe_api->payment_intents( 'retrieve', [ $secret['id'] ] );

			if ( $response['success'] && 'success' === $response['data']->status ) {
				wc_add_notice( __( 'An error has occurred internally, due to which you are not redirected to the order received page.', 'checkout-plugins-stripe-woo' ), $notice_type = 'error' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit();
			}
		}

		$args = [
			[ $args ],
			[ 'idempotency_key' => $idempotency_key ],
		];

		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->payment_intents( 'create', $args );

		if ( $response['success'] ) {
			$intent = $response['data'];
		} elseif ( $return_error_response ) {
			return $response;
		} else {
			wc_add_notice( $response['message'], 'error' );
			return false;
		}

		$intent_data = [
			'id'            => $intent->id,
			'client_secret' => $intent->client_secret,
		];

		$order = wc_get_order( $order_id );
		$order->update_meta_data( '_cpsw_intent_secret', $intent_data );
		$order->save();
		$client_secret = $intent->client_secret;

		return [
			'client_secret' => $client_secret,
		];
	}

	/**
	 * Returns amount as per currency type
	 *
	 * @since 1.0.0
	 *
	 * @param string $total amount to be processed.
	 * @param string $currency transaction currency.
	 *
	 * @return int
	 */
	public function get_formatted_amount( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		if ( in_array( strtoupper( $currency ), self::$zero_currencies, true ) ) {
			// Zero decimal currencies accepted by stripe.
			return absint( $total );
		} else {
			return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) ); // In cents.
		}
	}

	/**
	 * Add metadata to stripe
	 *
	 * @since 1.2.0
	 *
	 * @param int $order_id WooCommerce order Id.
	 *
	 * @return array
	 */
	public function get_metadata( $order_id ) {
		$order              = wc_get_order( $order_id );
		$details            = [];
		$billing_first_name = $order->get_billing_first_name();
		$billing_last_name  = $order->get_billing_last_name();
		$name               = $billing_first_name . ' ' . $billing_last_name;

		if ( ! empty( $name ) ) {
			$details['name'] = $name;
		}

		if ( ! empty( $order->get_billing_email() ) ) {
			$details['email'] = $order->get_billing_email();
		}

		if ( ! empty( $order->get_billing_phone() ) ) {
			$details['phone'] = $order->get_billing_phone();
		}

		if ( ! empty( $order->get_billing_address_1() ) ) {
			$details['address'] = $order->get_billing_address_1();
		}

		if ( ! empty( $order->get_billing_city() ) ) {
			$details['city'] = $order->get_billing_city();
		}

		if ( ! empty( $order->get_billing_country() ) ) {
			$details['country'] = $order->get_billing_country();
		}

		$details['site_url'] = get_site_url();

		return apply_filters( 'cpsw_metadata_details', $details, $order );
	}

	/**
	 * All payment icons that work with Stripe
	 *
	 * @since 1.2.0
	 *
	 * @param string $gateway_id gateway id to fetch icon.
	 *
	 * @return array
	 */
	public function payment_icons( $gateway_id ) {
		$icons = [
			'cpsw_alipay'     => '<img src="' . $this->assets_url . 'icon/alipay.svg" class="cpsw-alipay-icon" alt="Alipay" width="50px" />',
			'cpsw_ideal'      => '<img src="' . $this->assets_url . 'icon/ideal.svg" class="cpsw-ideal-icon" alt="iDEAL" width="32" />',
			'cpsw_klarna'     => '<img src="' . $this->assets_url . 'icon/klarna.svg" class="cpsw-klarna-icon" alt="Klarna" width="60" />',
			'cpsw_p24'        => '<img src="' . $this->assets_url . 'icon/p24.svg" class="cpsw-p24-icon" alt="Przelewy24" width="60" />',
			'cpsw_bancontact' => '<img src="' . $this->assets_url . 'icon/bancontact.svg" class="cpsw-bancontact-icon" alt="Bancontact" width="40" />',
			'cpsw_wechat'     => '<img src="' . $this->assets_url . 'icon/wechat.svg" class="cpsw-wechat-icon" alt="WeChat" width="80" />',
			'cpsw_sepa'       => '<img src="' . $this->assets_url . 'icon/sepa.svg" class="cpsw-sepa-icon" alt="SEPA" width="50px" />',
		];

		return apply_filters(
			'cpsw_payment_icons',
			isset( $icons[ $gateway_id ] ) ? $icons[ $gateway_id ] : ''
		);
	}

	/**
	 * Create refund request.
	 *
	 * @since 1.0.0
	 *
	 * @param object $order order.
	 * @param string $amount refund amount.
	 * @param string $reason reason of refund.
	 * @param string $intent_secret_id secret key.
	 *
	 * @return array
	 */
	public function create_refund_request( $order, $amount, $reason, $intent_secret_id ) {
		$client     = $this->get_clients_details();
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->payment_intents( 'retrieve', [ $intent_secret_id ] );
		$status     = $response['success'] && isset( $response['data']->charges->data[0]->captured ) ? $response['data']->charges->data[0]->captured : false;

		if ( ! $status ) {
			Logger::error( __( 'Uncaptured Amount cannot be refunded', 'checkout-plugins-stripe-woo' ), true );
			return new WP_Error( 'error', __( 'Uncaptured Amount cannot be refunded', 'checkout-plugins-stripe-woo' ) );
		}

		$intent_response = $response['data'];
		$currency        = $intent_response->currency;

		$refund_params = apply_filters(
			'cpsw_refund_request_args',
			[
				'payment_intent' => $intent_secret_id,
				'amount'         => $this->get_formatted_amount( $amount, $currency ),
				'reason'         => 'requested_by_customer',
				'metadata'       => [
					'order_id'          => $order->get_order_number(),
					'customer_ip'       => $client['ip'],
					'agent'             => $client['agent'],
					'referer'           => $client['referer'],
					'reason_for_refund' => $reason,
				],
			]
		);

		$stripe_api = new Stripe_Api();
		return $stripe_api->refunds( 'create', [ $refund_params ] );
	}

	/**
	 * Clean/Trim statement descriptor as per stripe requirement.
	 *
	 * @since 1.0.0
	 *
	 * @param string $statement_descriptor User Input.
	 *
	 * @return string optimized statement descriptor.
	 */
	public function clean_statement_descriptor( $statement_descriptor = '' ) {
		$disallowed_characters = [ '<', '>', '\\', '*', '"', "'", '/', '(', ')', '{', '}' ];

		// Strip any tags.
		$statement_descriptor = wp_strip_all_tags( $statement_descriptor );

		// Strip any HTML entities.
		// Props https://stackoverflow.com/questions/657643/how-to-remove-html-special-chars .
		$statement_descriptor = preg_replace( '/&#?[a-z0-9]{2,8};/i', '', $statement_descriptor );

		// Next, remove any remaining disallowed characters.
		$statement_descriptor = str_replace( $disallowed_characters, '', $statement_descriptor );

		// Trim any whitespace at the ends and limit to 22 characters.
		$statement_descriptor = substr( trim( $statement_descriptor ), 0, 22 );

		return $statement_descriptor;
	}

	/**
	 * Process order after stripe payment
	 *
	 * @since 1.0.0
	 *
	 * @param object $response intent response data.
	 * @param string $order_id currnt coocommerce id.
	 *
	 * @return array return data.
	 */
	public function process_order( $response, $order_id ) {
		$order = wc_get_order( $order_id );

		if ( isset( $response->balance_transaction ) ) {
			$this->update_balance( $order, $response->balance_transaction, true );
		}

		if ( true === $response->captured ) {
			$order->payment_complete( $response->id );
			/* translators: order id */
			Logger::info( sprintf( __( 'Payment successful Order id - %1s', 'checkout-plugins-stripe-woo' ), $order->get_id() ), true );

			$source_name = 'cpsw_stripe' === $this->id ? ucfirst( $response->payment_method_details->card->brand ) : ucfirst( $response->payment_method_details->type );

			$order->add_order_note( __( 'Payment Status: ', 'checkout-plugins-stripe-woo' ) . ucfirst( $response->status ) . ', ' . __( 'Source: Payment is Completed via ', 'checkout-plugins-stripe-woo' ) . $source_name );
		} else {
			/* translators: transaction id */
			$order->update_status( 'on-hold', sprintf( __( 'Charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization. Attempting to refund the order in part or in full will release the authorization and cancel the payment.', 'checkout-plugins-stripe-woo' ), $response->id ) );
			/* translators: transaction id */
			Logger::info( sprintf( __( 'Charge authorized Order id - %1s', 'checkout-plugins-stripe-woo' ), $order->get_id() ), true );
		}

		WC()->cart->empty_cart();

		/**
		 * Action when process order.
		 *
		 * @since 1.4.0
		 *
		 * @param obj    $response Payment response data.
		 * @param obj    $order WooCommerce main order.
		 * @param string $this->id Current payment gateway id.
		 */
		do_action( 'cpsw_local_gateways_process_order', $response, $order, $this->id );

		return $this->get_return_url( $order );
	}

	/**
	 * Save payment method to meta of current order
	 *
	 * @since 1.0.0
	 *
	 * @param object $order current woocommerce order.
	 * @param object $payment_method payment method associated with current order.
	 *
	 * @return void
	 */
	public function save_payment_method_to_order( $order, $payment_method ) {
		if ( $payment_method->customer ) {
			$order->update_meta_data( '_cpsw_customer_id', $payment_method->customer );
		}

		$order->update_meta_data( '_cpsw_source_id', $payment_method->source );

		if ( is_callable( [ $order, 'save' ] ) ) {
			$order->save();
		}

		$this->maybe_update_source_on_subscription_order( $order, $payment_method );
	}

	/**
	 * Prepare payment method object
	 *
	 * @since 1.0.0
	 *
	 * @param object $payment_method payment method object from intent.
	 * @param object $token token object used for payment.
	 *
	 * @return object
	 */
	public function prepare_payment_method( $payment_method, $token ) {
		return (object) apply_filters(
			'cpsw_prepare_payment_method_args',
			[
				'token_id'              => $token->get_id(),
				'customer'              => $payment_method->customer,
				'source'                => $payment_method->id,
				'source_object'         => $payment_method,
				'payment_method'        => $payment_method->id,
				'payment_method_object' => $payment_method,
			]
		);
	}

	/**
	 * Checks if using saved payment method or new card
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_using_saved_payment_method() {
		// nonce verification already done while saving data in woocommerce function.
		$payment_method = isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : $this->id; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		return ( isset( $_POST[ 'wc-' . $payment_method . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $payment_method . '-payment-token' ] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * This function checks for the conditions whether current card should be saved or not.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id WooCommerce order id.
	 *
	 * @return boolean
	 */
	public function should_save_card( $order_id ) {
		$status = false;
		// Just checks if this key exists in chosen payment method, nonce verification not required.
		$status = isset( $_POST[ 'wc-' . $this->id . '-new-payment-method' ] ) || $this->has_subscription( $order_id ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

		return apply_filters( 'cpsw_force_save_card', $status );
	}



	/**
	 * Get token object for selected saved card payment
	 *
	 * @since 1.0.0
	 *
	 * @param array $request post request.
	 * @return object
	 */
	public function get_token_from_request( array $request ) {
		$payment_method    = ! is_null( $request['payment_method'] ) ? $request['payment_method'] : null;
		$token_request_key = 'wc-' . $payment_method . '-payment-token';
		if (
			! isset( $request[ $token_request_key ] ) ||
			'new' === $request[ $token_request_key ]
			) {
			return null;
		}

		$token = WC_Payment_Tokens::get( wc_clean( $request[ $token_request_key ] ) );

		// If the token doesn't belong to this gateway or the current user it's invalid.
		if ( ! $token || $payment_method !== $token->get_gateway_id() || $token->get_user_id() !== get_current_user_id() ) {
			return null;
		}

		return $token;
	}

	/**
	 * Process payment using saved cards.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id woocommerce order id.
	 * @return array
	 */
	public function process_payment_with_saved_payment_method( $order_id ) {
		try {
			$order = wc_get_order( $order_id );
			// Nonce verification already done in parent woocommerce function.
			$token                   = $this->get_token_from_request( wc_clean( $_POST ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe_api              = new Stripe_Api();
			$response                = $stripe_api->payment_methods( 'retrieve', [ $token->get_token() ] );
			$payment_method          = $response['success'] ? $response['data'] : false;
			$prepared_payment_method = $this->prepare_payment_method( $payment_method, $token );

			$this->save_payment_method_to_order( $order, $prepared_payment_method );
			/* translators: %1$1s order id, %2$2s order total amount  */
			Logger::info( sprintf( __( 'Begin processing payment with saved payment method for order %1$1s for the amount of %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $order->get_total() ) );

			$request = [
				'payment_method'       => $payment_method->id,
				'payment_method_types' => [ $this->payment_method_types ],
				'amount'               => $this->get_formatted_amount( $order->get_total() ),
				'currency'             => strtolower( $order->get_currency() ),
				'description'          => $this->get_order_description( $order ),
				'customer'             => $payment_method->customer,
				'metadata'             => $this->get_metadata( $order_id ),
			];

			if ( 'cpsw_sepa' !== $this->id ) {
				$request['confirm'] = true;
			}

			if ( ! empty( $this->capture_method ) ) {
				$request['capture_method'] = $this->capture_method;
			}

			if ( ! empty( trim( $this->statement_descriptor ) ) ) {
				$request['statement_descriptor'] = $this->statement_descriptor;
			}

			$response = $this->create_payment_for_saved_payment_method( apply_filters( 'cpsw_saved_card_payment_intent_post_data', $request ) );

			if ( $response['success'] ) {
				$intent = $response['data'];
			} else {
				wc_add_notice( $response['message'], 'error' );
				$intent = false;
			}

			if ( ! $intent ) {
				return [
					'result'   => 'fail',
					'redirect' => '',
				];
			}

			$intent_data = [
				'id'            => $intent->id,
				'client_secret' => $intent->client_secret,
			];

			$order->update_meta_data( '_cpsw_intent_secret', $intent_data );
			$order->save();

			if ( 'requires_action' === $intent->status || 'requires_confirmation' === $intent->status ) {
				if ( isset( $intent->next_action->type ) && 'redirect_to_url' === $intent->next_action->type && ! empty( $intent->next_action->redirect_to_url->url ) ) {
					return [
						'result'   => 'success',
						'redirect' => $intent->next_action->redirect_to_url->url,
					];
				} else {
					return apply_filters(
						'cpsw_saved_card_payment_return_data',
						[
							'result'         => 'success',
							'redirect'       => $this->get_return_url( $order ),
							'payment_method' => $intent_data['id'],
							'intent_secret'  => $intent_data['client_secret'],
							'save_card'      => false,
						]
					);
				}
			}

			if ( $intent->amount > 0 ) {
				// Use the last charge within the intent to proceed.
				$this->process_response( end( $intent->charges->data ), $order );
			} else {
				$order->payment_complete();
			}

			// Remove cart.
			if ( isset( WC()->cart ) ) {
				WC()->cart->empty_cart();
			}

			// Return thank you page redirect.
			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];

		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );

			/* translators: error message */
			$order->update_status( 'failed' );

			return [
				'result'   => 'fail',
				'redirect' => '',
			];
		}
	}

	/** Get stripe order data
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return void
	 * @since 1.3.0
	 */
	public function get_stripe_order_data( $order_id ) {
		if ( apply_filters( 'cpsw_hide_stripe_order_data', false, $order_id ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( $this->id !== $order->get_payment_method() || empty( $order->get_meta( '_cpsw_display_stripe_data' ) ) ) {
			return;
		}

		$currency = $this->get_stripe_currency( $order );
		$net      = $this->get_stripe_net( $order );
		$fees     = $this->get_stripe_fee( $order );

		if ( ! $currency || ! $net || ! $fees ) {
			return;
		}

		?>

		<tr>
			<td class="label stripe-fees">
				<?php echo wp_kses_post( wc_help_tip( __( 'Fee charged by stripe for this order.', 'checkout-plugins-stripe-woo' ) ) ); ?>
				<?php esc_html_e( 'Stripe Fee:', 'checkout-plugins-stripe-woo' ); ?>
			</td>
			<td style='width:"1%";'></td>
			<td class="total">
				<?php echo wp_kses_post( wc_price( $fees, [ 'currency' => $currency ] ) ); ?>
			</td>
		</tr>
		<tr>
			<td class="label stripe-payout">
				<?php echo wp_kses_post( wc_help_tip( __( 'Net amount that will be credited to your Stripe bank account.', 'checkout-plugins-stripe-woo' ) ) ); ?>
				<?php esc_html_e( 'Stripe Payout:', 'checkout-plugins-stripe-woo' ); ?>
			</td>
			<td style='width:"1%";'></td>
			<td class="net">
				<?php echo wp_kses_post( wc_price( $net, [ 'currency' => $currency ] ) ); ?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Get stripe currency
	 *
	 * @param object $order WooCommerce Order.
	 * @return string
	 * @since 1.3.0
	 */
	public function get_stripe_currency( $order ) {
		return $order->get_meta( '_cpsw_stripe_currency', true );
	}

	/**
	 * Get stripe fee amount
	 *
	 * @param object $order WooCommerce Order.
	 * @return string
	 * @since 1.3.0
	 */
	public function get_stripe_fee( $order ) {
		return (float) $order->get_meta( '_cpsw_stripe_fee', true );
	}

	/**
	 * Get stripe net amount
	 *
	 * @param object $order WooCommerce Order.
	 * @return string
	 * @since 1.3.0
	 */
	public function get_stripe_net( $order ) {
		return $order->get_meta( '_cpsw_stripe_net', true );
	}

	/**
	 * Update stripe transaction data in local database
	 *
	 * @param object $order WooCommerce order.
	 * @param array  $data array of stripe meta_data to be added.
	 * @return void
	 * @since 1.3.0
	 */
	public function update_stripe_order_data( $order, $data ) {
		foreach ( $data as $key => $value ) {
			$order->update_meta_data( '_cpsw_stripe_' . $key, $value );
		}
	}

	/**
	 * Update stripe transaction data balance from balance transactions api
	 *
	 * @param object  $order WooCommerce order.
	 * @param string  $transaction_id stripe transaction id.
	 * @param boolean $initiate send true if this call will initiate balance transation update, use for intent creations only.
	 * @return void
	 * @since 1.3.0
	 */
	public function update_balance( $order, $transaction_id, $initiate = false ) {
		if ( $initiate ) {
			$order->update_meta_data( '_cpsw_display_stripe_data', true );
		} elseif ( empty( $order->get_meta( '_cpsw_display_stripe_data' ) ) ) {
			return;
		}

		$stripe   = new Stripe_Api();
		$response = $stripe->balance_transactions( 'retrieve', [ $transaction_id ] );
		$balance  = $response['success'] ? $response['data'] : false;

		if ( ! $balance ) {
			Logger::error( __( 'Unable to update stripe transaction balance', 'checkout-plugins-stripe-woo' ) );
			return;
		}

		$fee_refund = ! empty( $balance->fee ) ? $this->format_amount( $order, $balance->fee ) : 0;
		$net_refund = ! empty( $balance->net ) ? $this->format_amount( $order, $balance->net ) : 0;

		$fee      = (float) $this->get_stripe_fee( $order ) + (float) $fee_refund;
		$net      = (float) $this->get_stripe_net( $order ) + (float) $net_refund;
		$currency = ! empty( $balance->currency ) ? strtoupper( $balance->currency ) : null;

		$data = [
			'fee'      => $fee,
			'net'      => $net,
			'currency' => $currency,
		];

		$this->update_stripe_order_data( $order, $data );

		if ( is_callable( [ $order, 'save' ] ) ) {
			$order->save();
		}
	}

	/**
	 * Format amount from stripe as per currency
	 *
	 * @param object $order WooCommerce order object.
	 * @param int    $amount fee or net amount to be formatted.
	 * @return float
	 * @since 1.3.0
	 */
	public function format_amount( $order, $amount ) {
		if ( ! is_object( $order ) ) {
			return;
		}
		$amount = $this->get_original_amount( $amount, $order->get_currency() );

		return number_format( $amount, 2, '.', '' );
	}

	/**
	 * Returns amount received from stripe in WooCommerce as per currency
	 *
	 * @since 1.3.0
	 *
	 * @param string $total amount to be processed.
	 * @param string $currency transaction currency.
	 *
	 * @return int
	 */
	public function get_original_amount( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		if ( in_array( strtoupper( $currency ), self::$zero_currencies, true ) ) {
			// Zero decimal currencies accepted by stripe.
			return absint( $total );
		} else {
			return (float) wc_format_decimal( ( (float) $total / 100 ), wc_get_price_decimals() ); // In cents.
		}
	}

	/**
	 * Create payment for saved payment method, and this function
	 * useful for CartFlows.
	 *
	 * @param array $request_args arguments.
	 *
	 * @return array
	 */
	public function create_payment_for_saved_payment_method( $request_args ) {
		$stripe_api = new Stripe_Api();
		return $stripe_api->payment_intents( 'create', [ $request_args ] );
	}

	/** Wrapper function to update stripe balance transaction data in order meta
	 *
	 * @param object  $order WooCommerce Order.
	 * @param string  $intent_id associated stripe intent id.
	 * @param boolean $initiate send true if this call will initiate balance transaction update, use for intent creations only.
	 * @return void
	 * @since 1.3.0
	 */
	public function update_stripe_balance( $order, $intent_id, $initiate = false ) {
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->payment_intents( 'retrieve', [ $intent_id ] );
		$intent     = $response['success'] ? $response['data'] : false;

		if ( $intent ) {

			$charge_obj = end( $intent->charges->data );
			// Update payout details.
			if ( isset( $charge_obj->balance_transaction ) ) {
				$this->update_balance( $order, $charge_obj->balance_transaction, $initiate );
			}
		}
	}

	/**
	 * Get stripe default currency.
	 *
	 * @since 1.4.0
	 *
	 * @return mixed
	 */
	public function get_stripe_default_currency() {
		if (
			empty( Helper::get_setting( 'cpsw_account_id' ) ) ||
			! isset( $this->match_stripe_currency )
		) {
			return false;
		}

		$account_default_currency = get_transient( 'cpsw_stripe_account_default_currency' );

		if ( false === $account_default_currency ) {
			$stripe_api   = new Stripe_Api();
			$response     = $stripe_api->accounts( 'retrieve', [ Helper::get_setting( 'cpsw_account_id' ) ] );
			$account_info = $response['success'] ? $response['data'] : false;

			if ( ! $account_info ) {
				return false;
			}

			$account_default_currency = strtolower( $account_info->default_currency );
			delete_transient( 'cpsw_stripe_account_default_currency' );
			set_transient( 'cpsw_stripe_account_default_currency', $account_default_currency, 60 * MINUTE_IN_SECONDS );
		}

		return array_merge( $this->match_stripe_currency, [ $account_default_currency ] );
	}

	/**
	 * Checks to see if request is invalid and that
	 * they are worth retrying.
	 *
	 * @since 1.4.2
	 * @param obj $error Stripe return error data.
	 */
	public function is_retryable_error( $error ) {
		return (
			'invalid_request_error' === $error->type ||
			'idempotency_error' === $error->type ||
			'rate_limit_error' === $error->type ||
			'api_connection_error' === $error->type ||
			'api_error' === $error->type
		);
	}

	/**
	 * Creates setup intent for update payment method
	 *
	 * @return void
	 */
	public function create_setup_intent() {
		if ( ! isset( $_POST['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_security'] ), 'cpsw_js_error_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Nonce', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( empty( $_POST['paymentMethod'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Payment Method not provided', 'checkout-plugins-stripe-woo' ) ] );
		}

		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->setup_intents( 'create', [ [ 'payment_method_types' => [ 'card' ] ] ] );
		$response   = $response['success'] ? $response['data'] : false;
		wp_send_json_success( [ 'client_secret' => $response->client_secret ] );
		exit();
	}
}
