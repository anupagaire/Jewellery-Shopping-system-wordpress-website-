<?php
/**
 * CartFlows Flow data.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Api\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
use CartflowsAdmin\AdminCore\Api\ApiBase;

/**
 * Class ProductData.
 */
class ProductData extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/product-data/';

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

		$product_id = $request->get_param( 'id' );

		/* Prepare data */
		$data = array(
			'id' => $product_id,
		);

		$product = wc_get_product( $product_id );

		if ( $product ) {

			$data['img_url']       = get_the_post_thumbnail_url( $product_id );
			$data['regular_price'] = \Cartflows_Helper::get_product_original_price( $product );
		}

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
