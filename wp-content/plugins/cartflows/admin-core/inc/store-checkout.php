<?php
/**
 * CartFlows Store Checkout.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class StoreCheckout.
 */
class StoreCheckout {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function
	 *
	 * @since X.X.X
	 */
	public function __construct() {
		add_filter( 'cartflows_woo_active_steps_data', array( $this, 'store_checkout_import_steps' ) );
		add_filter( 'cartflows_admin_updated_flow_steps', array( $this, 'update_flow_order' ), 10, 2 );
		add_filter( 'cartflows_admin_action_slug', array( $this, 'store_action_slug' ), 10, 2 );
		add_filter( 'cartflows_admin_required_meta_keys', array( $this, 'required_meta_keys' ), 10, 2 );
		add_filter( 'cartflows_admin_flow_data', array( $this, 'modify_store_checkout_flow_data' ), 10, 2 );
		add_action( 'cartflows_admin_after_delete_flow', array( $this, 'delete_store_checkout_meta' ), 10, 1 );
	}

	/**
	 * Steps for store checkout when created from scratch
	 *
	 * @param array $steps existing steps.
	 * @return array
	 * @since X.X.X
	 */
	public function store_checkout_import_steps( $steps ) {
		return array(
			'order-form'         => array(
				'title' => __( 'Checkout (Store)', 'cartflows' ),
				'type'  => 'checkout',
			),
			'order-confirmation' => array(
				'title' => __( 'Thank You (Store)', 'cartflows' ),
				'type'  => 'thankyou',
			),
		);
	}

	/**
	 * Updates steps order in flow
	 *
	 * @param array $flows array of updated flow.
	 * @param int   $flow_id flow id.
	 * @return array
	 * @since X.X.X
	 */
	public function update_flow_order( $flows, $flow_id ) {
		if ( absint( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) ) !== $flow_id ) {
			return $flows;
		}

		$key = array_search( 'thankyou', wp_list_pluck( $flows, 'type' ), true );
		if ( ! $key ) {
			return $flows;
		}

		$thankyou = array_splice( $flows, $key, 1 );
		$flows[]  = $thankyou[0];

		return $flows;
	}

	/**
	 * Deletes Store Checkout metadata when store checkout is deleted
	 *
	 * @param int $flow_id flow id.
	 * @return void
	 * @since X.X.X
	 */
	public function delete_store_checkout_meta( $flow_id ) {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_delete_flow', 'security', false ) ) {
			$response_data = array( 'message' => __( 'Nonce verification failed.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		if ( absint( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) ) !== $flow_id ) {
			return;
		}

		delete_option( '_cartflows_store_checkout' );
		delete_option( '_cartflows_old_global_checkout' );
		$common_settings                             = \Cartflows_Helper::get_common_settings();
		$common_settings['global_checkout']          = '';
		$common_settings['override_global_checkout'] = 'disable';
		update_option( '_cartflows_common', $common_settings );
	}

	/**
	 * Provides correct action slug for flows / Store Checkout
	 *
	 * @param string $slug actual flow slug 'wcf-edit-flow'.
	 * @param int    $flow_id current flow id.
	 * @return string
	 * @since X.X.X
	 */
	public function store_action_slug( $slug, $flow_id ) {
		if ( intval( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) ) === $flow_id ) {
			$slug = 'wcf-edit-store-step';
		}

		return $slug;
	}

	/**
	 * Removing fields not required for store checkout
	 *
	 * @param array $flow_data current $flow_data array.
	 * @param int   $flow_id Current flow id.
	 * @return array
	 * @since X.X.X
	 */
	public function modify_store_checkout_flow_data( $flow_data, $flow_id ) {
		if ( absint( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) ) !== $flow_id ) {
			return $flow_data;
		}

		unset( $flow_data['settings_data']['settings']['general']['fields']['flow_indexing'] );

		return $flow_data;
	}
}

StoreCheckout::get_instance();
