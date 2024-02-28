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
use CartflowsAdmin\AdminCore\Inc\StepMeta;

/**
 * Class StepData.
 */
class StepData extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/step-data/';

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
						'description' => __( 'Step ID.', 'cartflows' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get step data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item( $request ) {

		$step_id = $request->get_param( 'id' );

		$meta_options = array();
		$meta_options = AdminHelper::get_step_meta_options( $step_id );

		$step_type         = $meta_options['type'];
		$flow_id           = get_post_meta( $step_id, 'wcf-flow-id', true );
		$edit_step         = get_edit_post_link( $step_id, 'edit' );
		$view_step         = get_permalink( $step_id );
		$page_builder_edit = \Cartflows_Helper::get_page_builder_edit_link( $step_id );

		/* Get Settings */
		$settings_data = StepMeta::get_meta_settings( $step_id, $step_type );

		/* Prepare data */
		$data = apply_filters(
			'cartflows_admin_' . $step_type . '_step_data',
			array(
				'id'                => $step_id,
				'flow_title'        => get_the_title( $flow_id ),
				'title'             => get_the_title( $step_id ),
				'type'              => $step_type,
				'tabs'              => isset( $settings_data['tabs'] ) ? $settings_data['tabs'] : '',
				'settings_data'     => isset( $settings_data['settings'] ) ? $settings_data['settings'] : '',
				'page_settings'     => isset( $settings_data['page_settings'] ) ? $settings_data['page_settings'] : '',
				'design_settings'   => isset( $settings_data['design_settings'] ) ? $settings_data['design_settings'] : '',
				'options'           => isset( $meta_options['options'] ) ? $meta_options['options'] : '',
				'view'              => $view_step,
				'edit'              => $edit_step,
				'page_builder_edit' => $page_builder_edit,
				'slug'              => get_post_field( 'post_name', $step_id, 'edit' ),
			),
			$step_id
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
