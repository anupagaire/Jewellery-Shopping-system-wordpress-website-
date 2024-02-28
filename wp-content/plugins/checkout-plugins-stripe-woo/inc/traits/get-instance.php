<?php
/**
 * Trait.
 *
 * @package checkout-plugins-stripe-woo
 */

namespace CPSW\Inc\Traits;

/**
 * Trait Get_Instance.
 */
trait Get_Instance {

	/**
	 * Instance object.
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
