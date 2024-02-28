<?php
/**
 * Subscriptions Trait.
 *
 * @package checkout-plugins-stripe-woo
 */

namespace CPSW\Inc\Traits;

use CPSW\Inc\Traits\Subscription_Helper as SH;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Logger;
use WC_Emails;
use WC_Subscriptions_Change_Payment_Gateway;
use Exception;
use WC_AJAX;

/**
 * Trait for Subscriptions utility functions.
 */
trait Subscriptions {
	use SH;

	/**
	 * Initialize subscription support and hooks.
	 */
	public function maybe_init_subscriptions() {
		if ( ! $this->is_subscriptions_enabled() ) {
			return;
		}

		$this->supports = $this->add_subscription_filters( $this->supports );

		add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, [ $this, 'scheduled_subscription_payment' ], 10, 2 );
		add_action( 'woocommerce_subscription_failing_payment_method_updated_' . $this->id, [ $this, 'update_failing_payment_method' ], 10, 2 );
		add_action( 'wcs_resubscribe_order_created', [ $this, 'delete_resubscribe_meta' ], 10, 1 );
		add_action( 'wcs_renewal_order_created', [ $this, 'delete_renewal_meta' ], 10 );
		add_action( 'cpsw_payment_fields_' . $this->id, [ $this, 'display_update_subs_payment_checkout' ] );
		add_action( 'cpsw_add_payment_method_' . $this->id . '_success', [ $this, 'handle_add_payment_method_success' ], 10, 2 );
		add_action( 'woocommerce_subscriptions_change_payment_before_submit', [ $this, 'differentiate_change_payment_method_form' ] );

		add_filter( 'woocommerce_subscription_payment_meta', [ $this, 'add_subscription_payment_meta' ], 10, 2 );
		add_filter( 'woocommerce_subscription_validate_payment_meta', [ $this, 'validate_subscription_payment_meta' ], 10, 2 );
		add_filter( 'cpsw_display_save_payment_method_checkbox', [ $this, 'display_save_payment_method_checkbox' ] );

		add_action( 'template_redirect', [ $this, 'remove_order_pay_var' ], 99 );
		add_action( 'template_redirect', [ $this, 'restore_order_pay_var' ], 101 );
	}

	/**
	 * Checkbox to update all subcription to new payment method
	 */
	public function display_update_subs_payment_checkout() {
		$subs_statuses = apply_filters( 'cpsw_update_subs_payment_method_card_status', [ 'active' ] );
		if (
			apply_filters( 'cpsw_display_update_subs_payment_method_card_checkbox', true ) &&
			wcs_user_has_subscription( get_current_user_id(), '', $subs_statuses ) &&
			is_add_payment_method_page()
		) {
			$id = sprintf( '%1$s-update-subs-payment-method-card', $this->id );
			echo '<span class="cpsw-save-cards"><label><input type="checkbox" name="' . esc_attr( $id ) . '" value="1"/>' . wp_kses_post( apply_filters( 'cpsw_save_to_subs_text', __( 'Update the payment method used for all of my active subscriptions.', 'checkout-plugins-stripe-woo' ) ) ) . '</label></span>';
		}
	}

	/**
	 * Updates all active subscriptions payment method.
	 *
	 * @param string $source_id source id.
	 * @param object $source_object source object.
	 */
	public function handle_add_payment_method_success( $source_id, $source_object ) {
		if ( isset( $_POST[ $this->id . '-update-subs-payment-method-card' ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$all_subs        = wcs_get_users_subscriptions();
			$subs_statuses   = apply_filters( 'cpsw_update_subs_payment_method_card_status', [ 'active' ] );
			$stripe_customer = $this->get_customer_id();

			if ( ! empty( $all_subs ) ) {
				foreach ( $all_subs as $sub ) {
					if ( $sub->has_status( $subs_statuses ) ) {
						WC_Subscriptions_Change_Payment_Gateway::update_payment_method(
							$sub,
							$this->id,
							[
								'post_meta' => [
									'_cpsw_source_id'   => [ 'value' => $source_id ],
									'_cpsw_customer_id' => [ 'value' => $stripe_customer ],
								],
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Renders hidden element.
	 */
	public function differentiate_change_payment_method_form() {
		echo '<input type="hidden" id="wc-cpsw_stripe-change-payment-method" />';
	}

	/**
	 * Maybe process payment method change for subscriptions.
	 *
	 * @param int $order_id current order id.
	 * @return bool
	 */
	public function maybe_change_subscription_payment_method( $order_id ) {
		return (
			$this->is_subscriptions_enabled() &&
			$this->has_subscription( $order_id ) &&
			$this->is_changing_payment_method_for_subscription()
		);
	}

	/**
	 * Process the payment method change for subscriptions.
	 *
	 * @param int $order_id current order id.
	 * @return array|null
	 */
	public function process_change_subscription_payment_method( $order_id ) {
		try {
			$subscription    = wc_get_order( $order_id );
			$prepared_source = $this->prepare_source( $order_id, get_current_user_id(), true );

			$this->save_payment_method_to_order( $subscription, $prepared_source );

			do_action( 'cpsw_change_subs_payment_method_success', $prepared_source->source, $prepared_source );

			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $subscription ),
			];
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage(), true );
		}
	}

	/**
	 * Scheduled_subscription_payment function.
	 *
	 * @param float  $amount_to_charge float The amount to charge.
	 * @param object $renewal_order WC_Order A WC_Order object created to record the renewal payment.
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
		$this->process_subscription_payment( $amount_to_charge, $renewal_order, true, false );
	}

	/**
	 * Process_subscription_payment function.
	 *
	 * @param float  $amount order amount.
	 * @param mixed  $renewal_order renewal order object.
	 * @param bool   $retry Should we retry the process.
	 * @param object $previous_error previous error for same order.
	 * @throws Exception Stripe Exception.
	 * @return void
	 */
	public function process_subscription_payment( $amount, $renewal_order, $retry = true, $previous_error = false ) {
		try {
			$order_id = $renewal_order->get_id();

			if ( isset( $_REQUEST['process_early_renewal'] ) && 'cpsw_stripe' === $this->id ) { // phpcs:ignore WordPress.Security.NonceVerification
				$response = $this->process_payment( $order_id, true, false, $previous_error, true );

				if ( 'success' === $response['result'] && isset( $response['payment_intent_secret'] ) ) {
					$verification_url = add_query_arg(
						[
							'order'         => $order_id,
							'nonce'         => wp_create_nonce( 'wc_stripe_confirm_pi' ),
							'redirect_to'   => remove_query_arg( [ 'process_early_renewal', 'subscription_id', 'wcs_nonce' ] ),
							'early_renewal' => true,
						],
						WC_AJAX::get_endpoint( 'wc_stripe_verify_intent' )
					);

					echo wp_json_encode(
						[
							'stripe_sca_required' => true,
							'intent_secret'       => $response['payment_intent_secret'],
							'redirect_url'        => $verification_url,
						]
					);

					exit;
				}

				// Hijack all other redirects in order to do the redirection in JavaScript.
				add_action( 'wp_redirect', [ $this, 'redirect_after_early_renewal' ], 100 );

				return;
			}

			// Check for an existing intent, which is associated with the order.
			if ( $this->has_authentication_already_failed( $renewal_order ) ) {
				return;
			}

			// Get source from order.
			$prepared_source = $this->prepare_order_source( $renewal_order );
			$source_object   = $prepared_source->source_object;

			if ( ! $prepared_source->customer ) {
				throw new Exception(
					'Failed to process renewal for order ' . $renewal_order->get_id() . '. Stripe customer id is missing in the order',
					__( 'Customer not found', 'checkout-plugins-stripe-woo' )
				);
			}

			Logger::info( "Begin processing subscription payment for order {$order_id} for the amount of {$amount}" );
			$response                   = $this->create_and_confirm_intent_for_off_session( $renewal_order, $prepared_source, $amount );
			$is_authentication_required = $this->is_authentication_required_for_payment( $response );

			// error not of the type 'authentication_required'.
			if ( isset( $response['error'] ) && ! $is_authentication_required ) {
				if ( $this->is_retryable_error( $response['error'] ) ) {
					// We want to retry.
					if ( $retry ) {
						// Don't do anymore retries after this.
						if ( 5 <= $this->retry_interval ) {
							return $this->process_subscription_payment( $amount, $renewal_order, false, $response['error'] );
						}

						sleep( $this->retry_interval );

						$this->retry_interval++;

						return $this->process_subscription_payment( $amount, $renewal_order, true, $response['error'] );
					} else {
						$localized_message = __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'checkout-plugins-stripe-woo' );
						$renewal_order->add_order_note( $localized_message );
					}
				}

				$renewal_order->add_order_note( $response['message'] );
			}

			// Either the charge was successfully captured, or it requires further authentication.
			if ( $is_authentication_required ) {
				do_action( 'cpsw_stripe_process_payment_authentication_required', $renewal_order, $response );

				$error_message = __( 'This transaction requires authentication.', 'checkout-plugins-stripe-woo' );
				$renewal_order->add_order_note( $error_message );

				$charge   = end( $response['error']->payment_intent->charges->data );
				$id       = $charge->id;
				$order_id = $renewal_order->get_id();

				$renewal_order->set_transaction_id( $id );
				/* translators: %s is the stripe charge Id */
				$renewal_order->update_status( 'failed', sprintf( __( 'Stripe charge awaiting authentication by user: %s.', 'checkout-plugins-stripe-woo' ), $id ) );
				if ( is_callable( [ $renewal_order, 'save' ] ) ) {
					$renewal_order->save();
				}
			} else {
				// The charge was successfully captured.
				do_action( 'cpsw_stripe_process_payment', $response, $renewal_order );

				if ( isset( $response['error'] ) ) {
					$renewal_order->update_status( 'failed', $response['message'] );
					return;
				}

				if ( $response && 'cpsw_sepa' === $this->id ) {
					set_transient( 'cpsw_stripe_sepa_client_secret', $response->client_secret, 1 * MINUTE_IN_SECONDS );
				}

				// Use the last charge within the intent or the full response body in case of SEPA.
				$this->process_response( ( isset( $response->charges ) && ! empty( $response->charges->data ) ) ? end( $response->charges->data ) : $response, $renewal_order );

			}
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage(), true );

			do_action( 'cpsw_stripe_process_payment_error', $e, $renewal_order );

			/* translators: error message */
			$renewal_order->update_status( 'failed' );
		}
	}

	/**
	 * Create and confirm intents for subscriptions
	 *
	 * @param WC_Orde $order current order.
	 * @param Object  $source prepared source to be charged.
	 * @param string  $amount amount of order.
	 * @return object
	 */
	public function create_and_confirm_intent_for_off_session( $order, $source, $amount ) {
		$order_id = $order->get_id();

		$request = [
			'amount'               => $amount ? $this->get_formatted_amount( $order->get_total() ) : 0,
			'currency'             => $order->get_currency(),
			'payment_method_types' => [ $this->payment_method_types ],
			'description'          => $this->get_order_description( $order ),
			'confirmation_method'  => 'automatic',
			'customer'             => $source->customer,
			'metadata'             => $this->get_metadata( $order_id ),
			'payment_method'       => $source->source,
		];

		if ( 'cpsw_sepa' !== $this->id ) {
			$request['off_session'] = 'true';
			$request['confirm']     = 'true';
		}

		if ( ! empty( trim( $this->statement_descriptor ) ) ) {
			$request['statement_descriptor'] = $this->statement_descriptor;
		}

		if ( ! empty( $this->capture_method ) ) {
			$request['capture_method'] = $this->capture_method;
		}

		Logger::info( "Stripe Payment initiated for order $order_id" );
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->payment_intents( 'create', [ apply_filters( 'cpsw_create_and_confirm_intent_post_data', $request ) ] );
		$intent     = $response['success'] ? $response['data'] : false;

		if ( $intent ) {
			$intent_data = [
				'id'            => $intent->id,
				'client_secret' => $intent->client_secret,
			];
			$order->update_meta_data( '_cpsw_intent_secret', $intent_data );
			$order->save();
		} else {
			$intent = [
				'message' => isset( $response['message'] ) ? $response['message'] : __( 'Payment processing failed. Please retry.', 'checkout-plugins-stripe-woo' ),
				'type'    => $response['type'],
				'code'    => $response['code'],
				'error'   => $response['error'],
			];
		}

		return $intent;
	}

	/**
	 * Check if authentication is required for payment
	 *
	 * @param object $response intent response.
	 * @return boolean
	 */
	public function is_authentication_required_for_payment( $response ) {
		return ( ! empty( $response['code'] ) && 'authentication_required' === $response['code'] );
	}

	/**
	 * Prepare Sorce for current order
	 *
	 * @param WC_Order $order Current order.
	 * @return object
	 */
	public function prepare_order_source( $order = null ) {
		$stripe_customer = [ 'id' => '' ];
		$stripe_source   = false;
		$token_id        = false;
		$source_object   = false;

		if ( $order ) {
			$order_id = $order->get_id();

			$stripe_customer_id = $this->get_cpsw_customer_id( $order );

			if ( $stripe_customer_id ) {
				$stripe_customer['id'] = $stripe_customer_id;
			}

			$source_id = $order->get_meta( '_cpsw_source_id', true );

			if ( $source_id ) {
				$stripe_source = $source_id;
				$stripe_api    = new Stripe_Api();
				$response      = $stripe_api->payment_methods( 'retrieve', [ $stripe_source ] );
				$source_object = $response['success'] ? $response['data'] : false;
			} elseif ( apply_filters( 'cpsw_use_default_customer_source', true ) ) {
				// Attempting with empty source id.
				$stripe_source = '';
			}
		}

		return (object) [
			'token_id'       => $token_id,
			'customer'       => $stripe_customer ? $stripe_customer['id'] : false,
			'source'         => $stripe_source,
			'source_object'  => $source_object,
			'payment_method' => null,
		];
	}

	/**
	 * Get customer id from meta for current order.
	 *
	 * @param WC_Order $order current woocommerce order.
	 * @return string
	 */
	public function get_cpsw_customer_id( $order ) {
		// Try to get it via the order first.
		$customer = $order->get_meta( '_cpsw_customer_id', true );

		if ( empty( $customer ) ) {
			$customer = get_user_option( '_cpsw_customer_id', $order->get_customer_id() );
		}

		return $customer;
	}

	/**
	 * Updates other subscription sources
	 *
	 * @param WC_Order      $order Current order.
	 * @param Stripe_Source $source payment source to be used.
	 * @return void
	 */
	public function maybe_update_source_on_subscription_order( $order, $source ) {
		if ( ! $this->is_subscriptions_enabled() ) {
			return;
		}

		$order_id = $order->get_id();

		// Also store it on the subscriptions being purchased or paid for in the order.
		if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {
			$subscriptions = wcs_get_subscriptions_for_order( $order_id );
		} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {
			$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
		} else {
			$subscriptions = [];
		}

		foreach ( $subscriptions as $subscription ) {
			$subscription->update_meta_data( '_cpsw_customer_id', $source->customer );
			if ( ! empty( $source->payment_method ) ) {
				$subscription->update_meta_data( '_cpsw_source_id', $source->payment_method );
			} else {
				$subscription->update_meta_data( '_cpsw_source_id', $source->source );
			}
			$subscription->save();
		}

	}

	/**
	 * Don't transfer Stripe customer/token meta to resubscribe orders.
	 *
	 * @param object $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription.
	 */
	public function delete_resubscribe_meta( $resubscribe_order ) {
		$resubscribe_order->delete_meta_data( '_cpsw_customer_id' );
		$resubscribe_order->delete_meta_data( '_cpsw_source_id' );

		if ( is_callable( [ $resubscribe_order, 'save' ] ) ) {
			$resubscribe_order->save();
		}

		$this->delete_renewal_meta( $resubscribe_order );
	}

	/**
	 * Don't transfer Stripe fee/ID meta to renewal orders.
	 *
	 * @param object $renewal_order The order created for the customer to resubscribe to the old expired/cancelled subscription.
	 * @return object
	 */
	public function delete_renewal_meta( $renewal_order ) {
		$this->delete_stripe_fee( $renewal_order );
		$this->delete_stripe_net( $renewal_order );

		// Delete payment intent ID.
		$renewal_order->delete_meta_data( '_stripe_intent_id' );
		if ( is_callable( [ $renewal_order, 'save' ] ) ) {
			$renewal_order->save();
		}

		return $renewal_order;
	}

	/**
	 * Deleting stripe fee meta
	 *
	 * @param WC_Order $order Current woocommerce order.
	 * @return bool | void
	 */
	public function delete_stripe_fee( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}

		$order->delete_meta_data( '_stripe_fee' );
		$order->delete_meta_data( 'Stripe Fee' );
		if ( is_callable( [ $order, 'save' ] ) ) {
			$order->save();
		}
	}

	/**
	 * Deleting Stripe renewal meta
	 *
	 * @param WC_Order $order Current woocommerce order.
	 * @return bool | void
	 */
	public static function delete_stripe_net( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}

		$order->delete_meta_data( 'stripe_net' );
		$order->delete_meta_data( 'Net Revenue From Stripe' );
		if ( is_callable( [ $order, 'save' ] ) ) {
			$order->save();
		}
	}

	/**
	 * Update the customer_id for after subscription completion
	 *
	 * @param WC_Subscription $subscription The subscription for which the failing payment method relates.
	 * @param WC_Order        $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
	 * @return void
	 */
	public function update_failing_payment_method( $subscription, $renewal_order ) {
		$subscription->update_meta_data( '_cpsw_customer_id', $renewal_order->get_meta( '_cpsw_customer_id', true ) );
		$subscription->update_meta_data( '_cpsw_source_id', $renewal_order->get_meta( '_cpsw_source_id', true ) );
		$subscription->save();
	}

	/**
	 * Saves the payment meta data required to process automatic recurring payments
	 *
	 * @param array           $payment_meta associative array of meta data required for automatic payments.
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @return array
	 */
	public function add_subscription_payment_meta( $payment_meta, $subscription ) {
		$source_id = $subscription->get_meta( '_cpsw_source_id' );

		$payment_meta[ $this->id ] = [
			'post_meta' => [
				'_cpsw_customer_id' => [
					'value' => $subscription->get_meta( '_cpsw_customer_id' ),
					'label' => 'Stripe Customer ID',
				],
				'_cpsw_source_id'   => [
					'value' => $source_id,
					'label' => 'Stripe Source ID',
				],
			],
		];

		return $payment_meta;
	}

	/**
	 * Validate the payment meta data
	 *
	 * @param string $payment_method_id The ID of the payment method to validate.
	 * @param array  $payment_meta associative array of meta data required for automatic payments.
	 * @throws Exception Stripe Exception.
	 * @return void
	 */
	public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) { //phpcs:ignore WordPressVIPMinimum.Hooks.AlwaysReturnInFilter.MissingReturnStatement
		if ( $this->id === $payment_method_id ) {

			if ( ! isset( $payment_meta['post_meta']['_cpsw_customer_id']['value'] ) || empty( $payment_meta['post_meta']['_cpsw_customer_id']['value'] ) ) {

				// Allow empty stripe customer id during subscription renewal. It will be added when processing payment if required.
				if ( ! isset( $_POST['wc_order_action'] ) || 'wcs_process_renewal' !== $_POST['wc_order_action'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
					throw new Exception( __( 'A "Stripe Customer ID" value is required.', 'checkout-plugins-stripe-woo' ) );
				}
			} elseif ( 0 !== strpos( $payment_meta['post_meta']['_cpsw_customer_id']['value'], 'cus_' ) ) {
				throw new Exception( __( 'Invalid customer ID. A valid "Stripe Customer ID" must begin with "cus_".', 'checkout-plugins-stripe-woo' ) );
			}

			if (
				! empty( $payment_meta['post_meta']['_cpsw_source_id']['value'] ) && (
					0 !== strpos( $payment_meta['post_meta']['_cpsw_source_id']['value'], 'card_' )
					&& 0 !== strpos( $payment_meta['post_meta']['_cpsw_source_id']['value'], 'src_' )
					&& 0 !== strpos( $payment_meta['post_meta']['_cpsw_source_id']['value'], 'pm_' )
				)
			) {
				throw new Exception( __( 'Invalid source ID. A valid source "Stripe Source ID" must begin with "src_", "pm_", or "card_".', 'checkout-plugins-stripe-woo' ) );
			}
		}
	}

	/**
	 * Removes sca variable if not required.
	 */
	public function remove_order_pay_var() {
		global $wp;
		if ( isset( $_GET['wc-stripe-confirmation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->order_pay_var         = $wp->query_vars['order-pay'];
			$wp->query_vars['order-pay'] = null;
		}
	}

	/**
	 * Restore the variable that was removed in remove_order_pay_var()
	 */
	public function restore_order_pay_var() {
		global $wp;
		if ( isset( $this->order_pay_var ) ) {
			$wp->query_vars['order-pay'] = $this->order_pay_var;
		}
	}

	/**
	 * Checks if a renewal already failed because a manual authentication is required.
	 *
	 * @param WC_Order $renewal_order The renewal order.
	 * @return boolean
	 */
	public function has_authentication_already_failed( $renewal_order ) {
		$existing_intent = $this->get_intent_from_order( $renewal_order );

		if (
			! $existing_intent
			|| 'requires_payment_method' !== $existing_intent->status
			|| empty( $existing_intent->last_payment_error )
			|| 'authentication_required' !== $existing_intent->last_payment_error->code
		) {
			return false;
		}

		// Make sure all emails are instantiated.
		WC_Emails::instance();

		/**
		 * Action when authentication already failed.
		 *
		 * @param WC_Order $renewal_order The order that is being renewed.
		 */
		do_action( 'cpsw_has_authentication_already_failed', $renewal_order );

		// Fail the payment attempt (order would be currently pending because of retry rules).
		$charge    = end( $existing_intent->charges->data );
		$charge_id = $charge->id;
		/* translators: %s is the stripe charge Id */
		$renewal_order->update_status( 'failed', sprintf( __( 'Stripe charge awaiting authentication by user: %s.', 'checkout-plugins-stripe-woo' ), $charge_id ) );

		return true;
	}

	/**
	 * Get intent from order
	 *
	 * @param WC_Order $order order object.
	 * @return mixed intent id or false.
	 */
	public function get_intent_from_order( $order ) {
		$intent_id = $order->get_meta( '_stripe_intent_id' );

		if ( $intent_id ) {
			return $this->get_intent( 'payment_intents', $intent_id );
		}

		// The order doesn't have a payment intent, but it may have a setup intent.
		$intent_id = $order->get_meta( '_stripe_setup_intent' );

		if ( $intent_id ) {
			return $this->get_intent( 'setup_intents', $intent_id );
		}

		return false;
	}

	/**
	 * Hijacks `wp_redirect` in order to generate a JS-friendly object with the URL.
	 *
	 * @param string $url The URL that Subscriptions attempts a redirect to.
	 * @return void
	 */
	public function redirect_after_early_renewal( $url ) {
		echo wp_json_encode(
			[
				'stripe_sca_required' => false,
				'redirect_url'        => $url,
			]
		);

		exit;
	}

	/**
	 * Once an intent has been verified, perform some final actions for early renewals.
	 *
	 * @param WC_Order $order The renewal order.
	 * @param stdClass $intent The Payment Intent object.
	 */
	protected function maybe_process_subscription_early_renewal_success( $order, $intent ) {
		if ( $this->is_subscriptions_enabled() && isset( $_GET['early_renewal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wcs_update_dates_after_early_renewal( wcs_get_subscription( $order->get_meta( '_subscription_renewal' ) ), $order );
		}
	}

	/**
	 * Process early renewal.
	 *
	 * @param WC_Order $order The renewal order.
	 * @param stdClass $intent The Payment Intent object (unused).
	 */
	protected function maybe_process_subscription_early_renewal_failure( $order, $intent ) {
		if ( $this->is_subscriptions_enabled() && isset( $_GET['early_renewal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$order->delete( true );
			$renewal_url = wcs_get_early_renewal_url( wcs_get_subscription( $order->get_meta( '_subscription_renewal' ) ) );
			wp_safe_redirect( $renewal_url );
			exit;
		}
	}

	/**
	 * Prepare source for user
	 *
	 * @param int     $order_id current woocommerce order id.
	 * @param int     $user_id current user id.
	 * @param boolean $force_save_source if saved a source is required.
	 * @param string  $existing_customer_id stripe customer id if available.
	 * @throws Exception Stripe Exceptions.
	 * @return mixed
	 */
	public function prepare_source( $order_id, $user_id, $force_save_source = false, $existing_customer_id = null ) {
		// Nonce verification already done in parent woocommerce function process_checkout.
		$order          = wc_get_order( $order_id );
		$customer_id    = ( ! empty( $existing_customer_id ) ) ? $existing_customer_id : $this->get_customer_id( $order );
		$source_object  = '';
		$source_id      = '';
		$wc_token_id    = false;
		$payment_method = isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : 'cpsw_stripe'; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$is_token       = false;

		// New CC info was entered and we have a new source to process.
		if ( ! empty( $_POST['payment_method_created'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe_source = wc_clean( wp_unslash( $_POST['payment_method_created'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe_api    = new Stripe_Api();
			$response      = $stripe_api->payment_methods( 'retrieve', [ $stripe_source ] );
			$source_object = $response['success'] ? $response['data'] : false;

			if ( ! $source_object ) {
				return;
			}

			$source_id = $source_object->id;
			// This checks to see if customer opted to save the payment method to file.
			$maybe_saved_card = isset( $_POST[ 'wc-' . $payment_method . '-new-payment-method' ] ) && ! empty( $_POST[ 'wc-' . $payment_method . '-new-payment-method' ] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Either saved card is enabled or forced by flow.
			if ( ( $user_id && $this->enable_saved_cards && $maybe_saved_card && 'reusable' === $source_object->usage ) || $force_save_source ) {
				$stripe_api->payment_methods( 'attach', [ $source_id, [ 'customer' => $customer_id ] ] );
				$this->create_payment_token_for_user( $user_id, $source_object );
				if ( ! empty( $response->error ) ) {
					throw new Exception( print_r( $response, true ), $this->get_localized_error_message_from_response( $response ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				}
				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message(), $response->get_error_message() );
				}
			}
		} elseif ( $this->is_using_saved_payment_method() ) {
			// Use an existing token, and then process the payment.
			$wc_token    = $this->get_token_from_request( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$wc_token_id = $wc_token->get_id();
			if ( ! $wc_token || $wc_token->get_user_id() !== get_current_user_id() ) {
				WC()->session->set( 'refresh_totals', true );
				throw new Exception( 'Invalid payment method', __( 'Invalid payment method. Please input a new card number.', 'checkout-plugins-stripe-woo' ) );
			}

			$source_id = $wc_token->get_token();

			if ( ! empty( $source_id ) ) {
				$is_token = true;
			}
			// nonce verification already done in parent woocommerce function process_checkout.
		} elseif ( isset( $_POST['stripe_token'] ) && 'new' !== $_POST['stripe_token'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe_token     = wc_clean( wp_unslash( $_POST['stripe_token'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$maybe_saved_card = isset( $_POST[ 'wc-' . $payment_method . '-new-payment-method' ] ) && ! empty( $_POST[ 'wc-' . $payment_method . '-new-payment-method' ] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			// This is true if the user wants to store the card to their account.
			if ( ( $user_id && $this->enable_saved_cards && $maybe_saved_card ) || $force_save_source ) {
				$response = $customer->attach_source( $stripe_token );

				if ( ! empty( $response->error ) ) {
					throw new Exception( print_r( $response, true ), $response->error->message ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				}
				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message(), $response->get_error_message() );
				}
				$source_id = $response->id;
			} else {
				$source_id = $stripe_token;
				$is_token  = true;
			}
		}

		if ( ! $customer_id ) {
			$customer = $this->create_stripe_customer( $source_id, $order_id, $user_email );
		}

		if ( empty( $source_object ) && ! $is_token ) {
			$source_object = $this->get_source_object( $source_id );
		}

		return (object) [
			'token_id'       => $wc_token_id,
			'customer'       => $customer_id,
			'source'         => $source_id,
			'source_object'  => $source_object,
			'payment_method' => null,
		];
	}

	/**
	 * Get source object by source id.
	 *
	 * @since 1.4.5
	 *
	 * @param string $source_id The source ID to get source object for.
	 */
	public function get_source_object( $source_id = '' ) {
		if ( empty( $source_id ) ) {
			return '';
		}

		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_methods( 'retrieve', [ $source_id ] );
		$source_object = $response['success'] ? $response['data'] : false;

		return $source_object;
	}
}
