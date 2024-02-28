<?php
/**
 * Dyanamic Flow Actions
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Wd_Flow_Actions {

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

		add_filter( 'cartflows_skip_configure_cart', array( $this, 'skip_cf_cart_configuration' ), 10, 2 );

		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_cf_hidden_fields' ) );
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'redirect_to_next_flow_step' ), 10, 2 );

	}

	/**
	 * Skip Configure Cart Data.
	 *
	 * @param bool $skip_cart is skip cart.
	 * @param int  $checkout_id checkout id..
	 */
	public function skip_cf_cart_configuration( $skip_cart, $checkout_id ) {

		if ( isset( $_GET['cf-redirect'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$skip_cart = true;
		}

		return $skip_cart;
	}

	/**
	 * Add hidden fields.
	 */
	public function add_cf_hidden_fields() {

		global $post, $wcf_step;

		if ( $post && $wcf_step ) {

			$flow_id = $wcf_step->get_flow_id();
			$step_id = $wcf_step->get_step_id();

			echo '<input type="hidden" name="wcf-flow-id" value="' . absint( $flow_id ) . '"/>';
			echo '<input type="hidden" name="wcf-step-id" value="' . absint( $step_id ) . '"/>';
		}
	}

	/**
	 * Redirect to flow next step.
	 *
	 * @param string $redirect_url next step URL.
	 * @param string $product Product adding in cart.
	 */
	public function redirect_to_next_flow_step( $redirect_url, $product ) {

		if ( isset( $_REQUEST['wcf-step-id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$step_id = intval( $_REQUEST['wcf-step-id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$wcf_step_obj = wcf_get_step( $step_id );
			$next_step_id = $wcf_step_obj->get_direct_next_step_id();

			if ( $next_step_id ) {

				$redirect_url = add_query_arg(
					array(
						'cf-redirect' => true,
					),
					get_permalink( $next_step_id )
				);

				wc_clear_notices();
			}
		}

		return $redirect_url;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Wd_Flow_Actions::get_instance();
