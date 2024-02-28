<?php
/**
 * Stripe Api Wrapper
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Logger;
use \Stripe\StripeClient;
use Exception;

/**
 * Stripe Api Class
 */
class Stripe_Api {

	/**
	 * Instance of Stripe
	 *
	 * @var \Stripe\StripeClient
	 */
	public $stripe;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$secret_key = apply_filters( 'cpsw_get_secret_key', ( 'live' === get_option( 'cpsw_mode' ) ) ? get_option( 'cpsw_secret_key' ) : get_option( 'cpsw_test_secret_key' ) );
		if ( ! empty( $secret_key ) ) {
			$this->stripe = new StripeClient(
				[
					'api_key'        => $secret_key,
					'stripe_version' => '2020-08-27',
				]
			);

			\Stripe\Stripe::setAppInfo(
				'Checkout Plugins - Stripe Woo',
				CPSW_VERSION,
				'https://wordpress.org/plugins/checkout-plugins-stripe-woo/',
				'pp_partner_KOjySVEy3ClX6G'
			);
		}
	}

	/**
	 * Executes all stripe calls
	 *
	 * @param string $api Api.
	 * @param string $method name of method.
	 * @param array  $args arguments.
	 * @return array
	 */
	private function execute( $api, $method, $args ) {
		if ( is_null( $this->stripe ) ) {
			$error_message = __( 'Stripe not initialized', 'checkout-plugins-stripe-woo' );
			Logger::error( $error_message, true );
			return [
				'success' => false,
				'message' => $error_message,
			];
		}

		$error_message = false;
		$response      = false;
		$get_error     = false;

		try {
			$response = $this->stripe->{$api}->{$method}( ...$args );
		} catch ( \Stripe\Exception\CardException $e ) {
			Logger::error( $e->getError()->message, true );
			$error_message = $e->getError()->message;
			$get_error     = $e->getError();
		} catch ( \Stripe\Exception\RateLimitException $e ) {
			// Too many requests made to the API too quickly.
			Logger::error( $e->getError()->message, true );
			$get_error     = $e->getError();
			$error_message = $e->getError()->message;
		} catch ( \Stripe\Exception\InvalidRequestException $e ) {
			// Invalid parameters were supplied to Stripe's API.
			Logger::error( $e->getError()->message, true );
			$get_error     = $e->getError();
			$error_message = $e->getError()->message;
		} catch ( \Stripe\Exception\AuthenticationException $e ) {
			// Authentication with Stripe's API failed.
			// (maybe you changed API keys recently).
			Logger::error( $e->getError()->message, true );
			$get_error     = $e->getError();
			$error_message = $e->getError()->message;
		} catch ( \Stripe\Exception\ApiConnectionException $e ) {
			// Network communication with Stripe failed.
			$get_error     = $e->getError();
			$error_message = is_null( $e->getError() ) ? $e->getMessage() : $e->getError()->message;
			Logger::error( $error_message, true );
		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			Logger::error( $e->getError()->message, true );
			$get_error     = $e->getError();
			$error_message = $e->getError()->message;
			// Display a very generic error to the user, and maybe send.
			// yourself an email.
		} catch ( Exception $e ) {
			// Something else happened, completely unrelated to Stripe.
			Logger::error( $e->getMessage(), true );
			$error_message = $e->getMessage();
		}

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
		}

		if ( ! $error_message ) {
			return [
				'success' => true,
				'data'    => $response,
				'message' => '',
			];
		} else {
			return [
				'success' => false,
				'message' => $error_message,
				'type'    => isset( $get_error->param ) ? $get_error->param : '',
				'code'    => isset( $get_error->code ) ? $get_error->code : '',
				'error'   => $get_error,
			];
		}
	}

	/**
	 * Stripe wrapper for paymentIntents Api
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 *
	 * @return array
	 */
	public function payment_intents( $method, $args ) {
		return $this->execute( 'paymentIntents', $method, $args );
	}

	/**
	 * Stripe wrapper for paymentMethods Api
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function payment_methods( $method, $args ) {
		return $this->execute( 'paymentMethods', $method, $args );
	}

	/**
	 * Executes stripe customers query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function customers( $method, $args ) {
		return $this->execute( 'customers', $method, $args );
	}

	/**
	 * Executes Stripe refunds query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function refunds( $method, $args ) {
		return $this->execute( 'refunds', $method, $args );
	}

	/**
	 * Executes Stripe setupIntents query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function setup_intents( $method, $args ) {
		return $this->execute( 'setupIntents', $method, $args );
	}

	/**
	 * Executes Stripe accounts query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function accounts( $method, $args ) {
		return $this->execute( 'accounts', $method, $args );
	}

	/**
	 * Executes Stripe webhook query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function webhooks( $method, $args ) {
		return $this->execute( 'webhookEndpoints', $method, $args );
	}

	/**
	 * Executes Stripe apple pay domains query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 */
	public function apple_pay_domains( $method, $args ) {
		return $this->execute( 'applePayDomains', $method, $args );
	}

	/**
	 * Executes Stripe balance transactions query
	 *
	 * @param string $method method to be used.
	 * @param array  $args parameter.
	 * @return array
	 * @since 1.3.0
	 */
	public function balance_transactions( $method, $args ) {
		return $this->execute( 'balanceTransactions', $method, $args );
	}
}
