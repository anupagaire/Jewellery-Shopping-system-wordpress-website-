<?php
/**
 * Trait.
 *
 * @package checkout-plugins-stripe-woo
 */

namespace CPSW\Inc\Traits;

use WC_Subscriptions;
use WC_Subscriptions_Cart;

/**
 * Trait for Subscriptions utility functions.
 */
trait Subscription_Helper {

	/**
	 * Checks if subscriptions are enabled on the site.
	 *
	 * @return bool Whether subscriptions is enabled or not.
	 */
	public function is_subscriptions_enabled() {
		return class_exists( 'WC_Subscriptions' ) && version_compare( WC_Subscriptions::$version, '2.2.0', '>=' );
	}

	/**
	 * Adding subscription filter
	 *
	 * @param array $supports already enabled filters.
	 * @return array
	 */
	public function add_subscription_filters( $supports ) {
		return array_merge(
			$supports,
			[
				'subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change',
				'subscription_payment_method_change_customer',
				'subscription_payment_method_change_admin',
				'multiple_subscriptions',
			]
		);
	}

	/**
	 * Is $order_id a subscription?
	 *
	 * @param  int $order_id current woocommerce order id.
	 * @return boolean
	 */
	public function has_subscription( $order_id ) {
		return ( function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_is_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) ) );
	}

	/**
	 * Returns whether this user is changing the payment method for a subscription.
	 *
	 * @return bool
	 */
	public function is_changing_payment_method_for_subscription() {
		if ( isset( $_GET['change_payment_method'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return wcs_is_subscription( wc_clean( wp_unslash( $_GET['change_payment_method'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}
		return false;
	}

	/**
	 * Returns boolean value indicating whether payment for an order will be recurring,
	 * as opposed to single.
	 *
	 * @param int $order_id ID for corresponding WC_Order in process.
	 *
	 * @return bool
	 */
	public function is_payment_recurring( $order_id ) {
		if ( ! $this->is_subscriptions_enabled() ) {
			return false;
		}
		return $this->is_changing_payment_method_for_subscription() || $this->has_subscription( $order_id );
	}

	/**
	 * Display checkbox for non subscription order if save card enabled
	 *
	 * @param bool $display is save card feature enabled.
	 *
	 * @return bool
	 */
	public function display_save_payment_method_checkbox( $display ) {
		if ( WC_Subscriptions_Cart::cart_contains_subscription() || $this->is_changing_payment_method_for_subscription() ) {
			return false;
		}
		// Only render the "Save payment method" checkbox if there are no subscription products in the cart.
		return $display;
	}

	/**
	 * Checks whether cart has subscription or not
	 *
	 * @return bool
	 */
	public function is_subscription_item_in_cart() {
		if ( $this->is_subscriptions_enabled() ) {
			return WC_Subscriptions_Cart::cart_contains_subscription() || $this->cart_contains_renewal();
		}
		return false;
	}

	/**
	 * Checks if cart contains a subscription renewal.
	 *
	 * @return mixed
	 */
	public function cart_contains_renewal() {
		if ( ! function_exists( 'wcs_cart_contains_renewal' ) ) {
			return false;
		}
		return wcs_cart_contains_renewal();
	}

}
