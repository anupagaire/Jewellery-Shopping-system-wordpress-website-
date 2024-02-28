<?php
/**
 * Stripe Webhook Class
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Gateway\Abstract_Payment_Gateway;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Inc\Helper;
use CPSW\Inc\Logger;
use DateTime;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Webhook endpoints
 */
class Webhook extends Abstract_Payment_Gateway {

	const CPSW_LIVE_BEGAN_AT        = 'cpsw_live_webhook_began_at';
	const CPSW_LIVE_LAST_SUCCESS_AT = 'cpsw_live_webhook_last_success_at';
	const CPSW_LIVE_LAST_FAILURE_AT = 'cpsw_live_webhook_last_failure_at';
	const CPSW_LIVE_LAST_ERROR      = 'cpsw_live_webhook_last_error';

	const CPSW_TEST_BEGAN_AT        = 'cpsw_test_webhook_began_at';
	const CPSW_TEST_LAST_SUCCESS_AT = 'cpsw_test_webhook_last_success_at';
	const CPSW_TEST_LAST_FAILURE_AT = 'cpsw_test_webhook_last_failure_at';
	const CPSW_TEST_LAST_ERROR      = 'cpsw_test_webhook_last_error';

	use Get_Instance;

	/**
	 * Constructor function
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Returns message about interaction with stripe webhook
	 *
	 * @param mixed $mode mode of operation.
	 * @return string
	 */
	public static function get_webhook_interaction_message( $mode = false ) {
		if ( ! $mode ) {
			$mode = Helper::get_payment_mode();
		}
		$last_success    = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_SUCCESS_AT' );
		$last_success_at = get_option( $last_success );

		$last_failure    = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_FAILURE_AT' );
		$last_failure_at = get_option( $last_failure );

		$began    = constant( 'self::CPSW_' . strtoupper( $mode ) . '_BEGAN_AT' );
		$began_at = get_option( $began );

		$status = 'none';

		if ( $last_success_at && $last_failure_at ) {
			$status = ( $last_success_at >= $last_failure_at ) ? 'success' : 'failure';
		} elseif ( $last_success_at ) {
			$status = 'success';
		} elseif ( $last_failure_at ) {
			$status = 'failure';
		} elseif ( $began_at ) {
			$status = 'began';
		}

		switch ( $status ) {
			case 'success':
				/* translators: time, status */
				return sprintf( __( 'Last webhook call was %1$1s. Status : %2$2s', 'checkout-plugins-stripe-woo' ), self::time_elapsed_string( gmdate( 'Y-m-d H:i:s e', $last_success_at ) ), '<b>' . ucfirst( $status ) . '</b>' );

			case 'failure':
				$err_const = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_ERROR' );
				$error     = get_option( $err_const );
				/* translators: error message */
				$reason = ( $error ) ? sprintf( __( 'Reason : %1s', 'checkout-plugins-stripe-woo' ), '<b>' . $error . '</b>' ) : '';
				/* translators: time, status, reason */
				return sprintf( __( 'Last webhook call was %1$1s. Status : %2$2s. %3$3s', 'checkout-plugins-stripe-woo' ), self::time_elapsed_string( gmdate( 'Y-m-d H:i:s e', $last_success_at ) ), '<b>' . ucfirst( $status ) . '</b>', $reason );

			case 'began':
				/* translators: timestamp */
				return sprintf( __( 'No webhook call since %1s.', 'checkout-plugins-stripe-woo' ), gmdate( 'Y-m-d H:i:s e', $began_at ) );

			default:
				if ( 'live' === $mode ) {
					$endpoint_secret = Helper::get_setting( 'cpsw_live_webhook_secret' );
				} elseif ( 'test' === $mode ) {
					$endpoint_secret = Helper::get_setting( 'cpsw_test_webhook_secret' );
				}
				if ( ! empty( trim( $endpoint_secret ) ) ) {
					$current_time = time();
					update_option( $began, $current_time );
					/* translators: timestamp */
					return sprintf( __( 'No webhook call since %1s.', 'checkout-plugins-stripe-woo' ), gmdate( 'Y-m-d H:i:s e', $current_time ) );
				}
				return '';
		}
	}

	/**
	 * Registers endpoint for stripe webhook
	 *
	 * @return void
	 */
	public function register_endpoints() {
		register_rest_route(
			'cpsw',
			'/v1/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => [ $this, 'webhook_listener' ],
				'permission_callback' => function() {
					return true;
				},
			)
		);
	}

	/**
	 * This function listens webhook events from stripe.
	 *
	 * @return void
	 */
	public function webhook_listener() {
		$mode = Helper::get_payment_mode();
		if ( 'live' === $mode ) {
			$endpoint_secret = Helper::get_setting( 'cpsw_live_webhook_secret' );
		} elseif ( 'test' === $mode ) {
			$endpoint_secret = Helper::get_setting( 'cpsw_test_webhook_secret' );
		}

		if ( empty( trim( $endpoint_secret ) ) ) {
			// Empty webhook secret or webhook not initialized.
			http_response_code( 400 );
			exit();
		}

		$began = constant( 'self::CPSW_' . strtoupper( $mode ) . '_BEGAN_AT' );
		if ( ! get_option( $began ) ) {
			update_option( $began, time() );
		}

		$payload    = file_get_contents( 'php://input' );
		$sig_header = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? sanitize_text_field( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) : '';
		$event      = null;

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload,
				$sig_header,
				$endpoint_secret
			);
		} catch ( \UnexpectedValueException $e ) {
			// Invalid payload.
			Logger::error( 'Webhook error : ' . $e->getMessage() );
			$error_at = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_FAILURE_AT' );
			update_option( $error_at, time() );
			$error = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_ERROR' );
			update_option( $error, $e->getMessage() );
			http_response_code( 400 );
			exit();
		} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
			// Invalid signature.
			Logger::error( 'Webhook error : ' . $e->getMessage() );
			$error_at = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_FAILURE_AT' );
			update_option( $error_at, time() );
			$error = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_ERROR' );
			update_option( $error, $e->getMessage() );
			http_response_code( 400 );
			exit();
		}

		Logger::info( 'intent type: ' . $event->type );

		switch ( $event->type ) {
			case 'charge.captured':
				$charge = $event->data->object;
				$this->charge_capture( $charge );
				break;
			case 'charge.refunded':
				$charge = $event->data->object;
				$this->charge_refund( $charge );
				break;
			case 'charge.dispute.created':
				$charge = $event->data->object;
				$this->charge_dispute_created( $charge );
				break;
			case 'charge.dispute.closed':
				$dispute = $event->data->object;
				$this->charge_dispute_closed( $dispute );
				break;
			case 'payment_intent.succeeded':
				$intent = $event->data->object;
				$this->payment_intent_succeeded( $intent );
				break;
			case 'payment_intent.payment_failed':
				$intent = $event->data->object;
				$this->payment_intent_failed( $intent );
				break;
			case 'review.opened':
				$review = $event->data->object;
				$this->review_opened( $review );
				break;
			case 'review.closed':
				$review = $event->data->object;
				$this->review_closed( $review );
				break;

		}
		$success = constant( 'self::CPSW_' . strtoupper( $mode ) . '_LAST_SUCCESS_AT' );
		update_option( $success, time() );
		http_response_code( 200 );
	}

	/**
	 * Captures charge for uncaptured charges via webhook calls
	 *
	 * @param object $charge Stripe Charge object.
	 * @return void
	 */
	public function charge_capture( $charge ) {
		$payment_intent = sanitize_text_field( $charge->payment_intent );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via charge ID: ' . $charge->id );
			return;
		}

		$order = wc_get_order( $order_id );

		if ( 'cpsw_stripe' === $order->get_payment_method() ) {
			$order->set_transaction_id( $charge->id );
			$this->make_charge( $charge, $order );
		}

	}

	/**
	 * Make charge via webhook call
	 *
	 * @param object $intent Stripe intent object.
	 * @param object $order WC order object.
	 * @return void
	 */
	public function make_charge( $intent, $order ) {
			// Check and see if capture is partial.
		if ( $intent->amount_refunded > 0 ) {
			$partial_amount = $intent->amount_captured;
			$currency       = strtoupper( $intent->currency );
			$partial_amount = $this->get_original_amount( $partial_amount, $currency );
			$order->set_total( $partial_amount );
			/* translators: order id */
			Logger::info( sprintf( __( 'Stripe charge partially captured with amount %1$1s Order id - %2$2s', 'checkout-plugins-stripe-woo' ), $partial_amount, $order->get_id() ), true );
			/* translators: partial captured amount */
			$order->add_order_note( sprintf( __( 'This charge was partially captured via Stripe Dashboard with the amount : %s', 'checkout-plugins-stripe-woo' ), $partial_amount ) );
		} else {
			$order->payment_complete( $intent->id );
			/* translators: order id */
			Logger::info( sprintf( __( 'Stripe charge completely captured Order id - %1s', 'checkout-plugins-stripe-woo' ), $order->get_id() ), true );
			/* translators: transaction id */
			$order->add_order_note( sprintf( __( 'Stripe charge complete (Charge ID: %s)', 'checkout-plugins-stripe-woo' ), $intent->id ) );
		}

		if ( isset( $intent->balance_transaction ) ) {
			$this->update_balance( $order, $intent->balance_transaction, true );
		}

		if ( is_callable( [ $order, 'save' ] ) ) {
			$order->save();
		}
	}

	/**
	 * Refunds WooCommerce order via webhook call
	 *
	 * @param object $charge Stripe Charge object.
	 * @return void
	 */
	public function charge_refund( $charge ) {
		$payment_intent = sanitize_text_field( $charge->payment_intent );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via charge ID: ' . $charge->id );
			return;
		}

		$order = wc_get_order( $order_id );

		if ( 0 === strpos( $order->get_payment_method(), 'cpsw_' ) ) {
			$transaction_id = $order->get_transaction_id();
			$captured       = $charge->captured;
			$refund_id      = get_transient( '_cpsw_refund_id_cache_' . $order_id );
			$refund_status  = get_transient( '_cpsw_refund_status_cache_' . $order_id );
			$currency       = strtoupper( $charge->currency );
			$raw_amount     = $charge->refunds->data[0]->amount;

			$raw_amount = $this->get_original_amount( $raw_amount, $currency );

			$amount = wc_price( $raw_amount, [ 'currency' => $currency ] );

			// If charge wasn't captured, no need to refund.
			if ( ! $captured ) {
				// Handling cancellation of unauthorized charge.
				if ( 'cancelled' !== $order->get_status() ) {
					/* translators: amount (including currency symbol) */
					$order->add_order_note( sprintf( __( 'Pre-Authorization for %s voided from the Stripe Dashboard.', 'checkout-plugins-stripe-woo' ), $amount ) );
					$order->update_status( 'cancelled' );
				}

				return;
			}

			// If the refund ID matches, don't continue to prevent double refunding.
			if ( false !== $refund_id && $charge->refunds->data[0]->id === $refund_id && 'pending' !== $refund_status ) {
				return;
			}

			if ( $transaction_id ) {
				$reason = __( 'Refunded via stripe dashboard', 'checkout-plugins-stripe-woo' );

				// Create the refund.
				$refund = wc_create_refund(
					[
						'order_id' => $order_id,
						'amount'   => ( $charge->amount_refunded > 0 ) ? $raw_amount : false,
						'reason'   => $reason,
					]
				);

				if ( is_wp_error( $refund ) ) {
					Logger::error( $refund->get_error_message() );
				}

				$refund_id = $charge->refunds->data[0]->id;
				set_transient( '_cpsw_refund_id_cache_' . $order_id, $refund_id, 60 );
				$order->update_meta_data( '_cpsw_refund_id', $refund_id );

				if ( isset( $charge->refunds->data[0]->balance_transaction ) ) {
					$this->update_balance( $order, $charge->refunds->data[0]->balance_transaction );
				}

				$status      = __( 'Success', 'checkout-plugins-stripe-woo' );
				$refund_time = gmdate( 'Y-m-d H:i:s', time() );
				$order->add_order_note( __( 'Reason : ', 'checkout-plugins-stripe-woo' ) . $reason . '.<br>' . __( 'Amount : ', 'checkout-plugins-stripe-woo' ) . $amount . '.<br>' . __( 'Status : ', 'checkout-plugins-stripe-woo' ) . $status . ' [ ' . $refund_time . ' ] <br>' . __( 'Transaction ID : ', 'checkout-plugins-stripe-woo' ) . $refund_id );
				Logger::info( $reason . ' : ' . __( 'Amount : ', 'checkout-plugins-stripe-woo' ) . get_woocommerce_currency_symbol() . str_pad( $raw_amount, 2, 0 ) . __( ' Transaction ID : ', 'checkout-plugins-stripe-woo' ) . $refund_id, true );
			}
		}
	}

	/**
	 * Handles charge.dispute.create webhook and changes order status to 'On Hold'
	 *
	 * @param int $charge stripe webhook object.
	 * @return void
	 * @since 1.2.0
	 */
	public function charge_dispute_created( $charge ) {
		$payment_intent = sanitize_text_field( $charge->payment_intent );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via charge ID: ' . $charge->id );
			return;
		}

		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', __( 'This order is under dispute. Please respond via stripe dashboard.', 'checkout-plugins-stripe-woo' ) );
		$order->update_meta_data( 'cpsw_status_before_dispute', $order->get_status() );
		$this->send_failed_order_email( $order_id );
	}

	/**
	 * Handles carge.dispute.closed webhook and update order status accordingly
	 *
	 * @param object $dispute dispute object recevied from stripe webhook.
	 * @return void
	 * @since 1.2.0
	 */
	public function charge_dispute_closed( $dispute ) {
		$payment_intent = sanitize_text_field( $dispute->payment_intent );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order for dispute ID: ' . $dispute->id );
			return;
		}

		$order = wc_get_order( $order_id );

		switch ( $dispute->status ) {
			case 'lost':
				$message = __( 'The disputed order lost or accepted.', 'checkout-plugins-stripe-woo' );
				break;

			case 'won':
				$message = __( 'The disputed order resolved in your favour.', 'checkout-plugins-stripe-woo' );
				break;

			case 'warning_closed':
				$message = __( 'The inquiry or retrieval closed.', 'checkout-plugins-stripe-woo' );
				break;
		}

		$status = 'lost' === $dispute->status ? 'failed' : $order->get_meta( 'cpsw_status_before_dispute' );
		$order->update_status( $status, $message );
	}

	/**
	 * Handles webhook call of event payment_intent.succeeded
	 *
	 * @param object $intent intent object received from stripe.
	 * @return void
	 */
	public function payment_intent_succeeded( $intent ) {
		$payment_intent = sanitize_text_field( $intent->id );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via payment intent: ' . $intent->id );
			return;
		}

		$order = wc_get_order( $order_id );
		if ( 'cpsw_stripe' === $order->get_payment_method() ) {
			return;
		}

		if ( 'manual' === $intent->capture_method && 0 === strpos( $order->get_payment_method(), 'cpsw_' ) ) {
			$this->make_charge( $intent, $order );
		} else {
			if ( ! $order->has_status(
				[ 'pending', 'failed', 'on-hold' ],
				$order
			) ) {
				return;
			}

			$charge = end( $intent->charges->data );
			/* translators: transaction id, order id */
			Logger::info( sprintf( __( 'Stripe PaymentIntent %1$1s succeeded for order %2$2s', 'checkout-plugins-stripe-woo' ), $charge->id, $order_id ) );
			$this->process_response( $charge, $order );
		}
	}

	/**
	 * Handles webhook call payment_intent.payment_failed
	 *
	 * @param object $intent stripe webhook object.
	 * @return void
	 * @since 1.2.0
	 */
	public function payment_intent_failed( $intent ) {
		$payment_intent = sanitize_text_field( $intent->id );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via payment intent: ' . $intent->id );
			return;
		}

		$order = wc_get_order( $order_id );
		/* translators: The error message that was received from Stripe. */
		$error_message = $intent->last_payment_error ? sprintf( __( 'Reason: %s', 'checkout-plugins-stripe-woo' ), $intent->last_payment_error->message ) : '';
		/* translators: The error message that was received from Stripe. */
		$message = sprintf( __( 'Stripe SCA authentication failed. %s', 'checkout-plugins-stripe-woo' ), $error_message );
		$order->update_status( 'failed', $message );

		$this->send_failed_order_email( $order_id );
	}

	/**
	 * Handles review.opened webhook
	 *
	 * @param int $review stripe webhook object.
	 * @return void
	 * @since 1.2.0
	 */
	public function review_opened( $review ) {
		$payment_intent = sanitize_text_field( $review->payment_intent );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via review ID: ' . $review->id );
			return;
		}

		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', __( 'This order is under review. Please respond via stripe dashboard.', 'checkout-plugins-stripe-woo' ) );
		$order->update_meta_data( 'cpsw_status_before_review', $order->get_status() );
		$this->send_failed_order_email( $order_id );
	}

	/**
	 * Handles review.closed webhook
	 *
	 * @param int $review stripe webhook object.
	 * @return void
	 * @since 1.2.0
	 */
	public function review_closed( $review ) {
		$payment_intent = sanitize_text_field( $review->payment_intent );
		$order_id       = $this->get_order_id_from_intent( $payment_intent );
		if ( ! $order_id ) {
			Logger::error( 'Could not find order via review ID: ' . $review->id );
			return;
		}

		$order = wc_get_order( $order_id );
		/* translators: Review reson from stripe */
		$message = sprintf( __( 'Review for this order has been resolved. Reason: %s', 'checkout-plugins-stripe-woo' ), $review->reason );
		$order->update_status( $order->get_meta( 'cpsw_status_before_review' ), $message );
	}

	/**
	 * Fetch WooCommerce order id from payment intent
	 *
	 * @param string $payment_intent payment intent received from stripe.
	 * @return int order id.
	 * @since 1.2.0
	 */
	public function get_order_id_from_intent( $payment_intent ) {
		global $wpdb;
		// Need to fetch custom data hence custom DB query is required.
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && method_exists( '\Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return $wpdb->get_var( $wpdb->prepare( "SELECT order_id from {$wpdb->prefix}wc_orders_meta where meta_key = '_cpsw_intent_secret' and meta_value like %s", '%' . $payment_intent . '%' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		} else {
			return $wpdb->get_var( $wpdb->prepare( "SELECT post_id from {$wpdb->prefix}postmeta where meta_key = '_cpsw_intent_secret' and meta_value like %s", '%' . $payment_intent . '%' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

	}

	/**
	 * Sends order failure email.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return void
	 * @since 1.2.0
	 */
	public function send_failed_order_email( $order_id ) {
		$emails = WC()->mailer()->get_emails();
		if ( ! empty( $emails ) && ! empty( $order_id ) ) {
			$emails['WC_Email_Failed_Order']->trigger( $order_id );
		}
	}

	/**
	 * Shows time difference as  - XX minutes ago.
	 *
	 * @param datetime $datetime time of last event.
	 * @param boolean  $full show full time difference.
	 * @return string
	 * @since 0.0.1
	 */
	public static function time_elapsed_string( $datetime, $full = false ) {
		$now  = new DateTime();
		$ago  = new DateTime( $datetime );
		$diff = $now->diff( $ago );

		$diff->w  = floor( $diff->d / 7 );
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ( $string as $k => &$v ) {
			if ( $diff->$k ) {
				$v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
			} else {
				unset( $string[ $k ] );
			}
		}

		if ( ! $full ) {
			$string = array_slice( $string, 0, 1 );
		}
		return $string ? implode( ', ', $string ) . ' ago' : 'just now';
	}

}
