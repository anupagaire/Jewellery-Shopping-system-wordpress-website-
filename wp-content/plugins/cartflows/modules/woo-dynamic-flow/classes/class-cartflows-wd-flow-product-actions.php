<?php
/**
 * Dynamic flow product actions.
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Wd_Flow_Product_Actions {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'redirect_to_next_flow_step' ), 99, 2 );

		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'change_add_to_cart_button_text' ), 10, 2 );
	}

	/**
	 * Redirect to flow step.
	 *
	 * @param string $redirect_url Flow URL.
	 * @param object $product Product.
	 */
	public function redirect_to_next_flow_step( $redirect_url, $product ) {

		if ( $product ) {

			$flow_id = $product->get_meta( 'cartflows_redirect_flow_id' );

			if ( ! empty( $flow_id ) ) {
				$steps        = get_post_meta( $flow_id, 'wcf-steps', true );
				$next_step_id = false;

				if ( is_array( $steps ) && ! empty( $steps ) && isset( $steps[0]['id'] ) ) {

					$next_step_id = intval( $steps[0]['id'] );
				}

				if ( ! empty( $next_step_id ) ) {

					$redirect_url = add_query_arg(
						array(
							'cf-redirect' => true,
						),
						get_permalink( $next_step_id )
					);

					wc_clear_notices();
				}
			}
		}

		return $redirect_url;
	}

	/**
	 * Change Add to cart text.
	 *
	 * @param string $add_to_cart_text Default text.
	 * @param object $product Product.
	 * @return string $add_to_cart_text Modified button name.
	 */
	public function change_add_to_cart_button_text( $add_to_cart_text, $product ) {

		if ( $product ) {

			$new_text = esc_html( wp_unslash( $product->get_meta( 'cartflows_add_to_cart_text' ) ) );

			if ( ! empty( $new_text ) ) {

				$add_to_cart_text = $new_text;
			}
		}

		return $add_to_cart_text;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Wd_Flow_Product_Actions::get_instance();
