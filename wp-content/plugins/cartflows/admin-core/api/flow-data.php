<?php
/**
 * CartFlows Flow data.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Api\ApiBase;
use CartflowsAdmin\AdminCore\Inc\AdminHelper;

use CartflowsAdmin\AdminCore\Inc\FlowMeta;

/**
 * Class Admin_Query.
 */
class FlowData extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/flow-data/';

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
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

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {

		$namespace = $this->get_api_namespace();

		register_rest_route(
			$namespace,
			$this->rest_base . '(?P<id>[\d-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Flow ID.', 'cartflows' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),

				/*
				Start - Not yet working but needed
					array(
						'methods'             => \WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'update_items_permissions_check' ),
					),
					End - Not yet working but needed
				*/
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get flow data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item( $request ) {

		$flow_id = $request->get_param( 'id' );

		$meta_options = AdminHelper::get_flow_meta_options( $flow_id );

		/* Setup steps data */
		$steps = AdminHelper::prepare_step_data( $flow_id, $meta_options );

		$data = apply_filters(
			'cartflows_admin_flow_data',
			array(
				'id'            => $flow_id,
				'title'         => get_the_title( $flow_id ),
				'slug'          => get_post_field( 'post_name', $flow_id, 'edit' ),
				'link'          => get_permalink( $flow_id ),
				'status'        => get_post_status( $flow_id ),
				'steps'         => $steps,
				'options'       => $meta_options,
				'settings_data' => FlowMeta::get_meta_settings( $flow_id ),
			),
			$flow_id
		);

		$response = new \WP_REST_Response( $data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Check whether a given request has permission to read notes.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return new \WP_Error( 'cartflows_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cartflows' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
