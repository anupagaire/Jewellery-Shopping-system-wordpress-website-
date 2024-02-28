<?php
/**
 * CartFlows Flows Query.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Api\ApiBase;

/**
 * Class Admin_Query.
 */
class Flows extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/flows/';

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

		// Eg. http://example.local/wp-json/cartflows/v1/admin/flows/.
		$namespace = $this->get_api_namespace();

		register_rest_route(
			$namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => 'POST', // WP_REST_Server::READABLE.
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(), // get_collection_params may use.
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get items
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {

		$post_status = 'any';
		$post_count  = 10;

		$args = array(
			'post_type'   => CARTFLOWS_FLOW_POST_TYPE,
			'post_status' => $post_status,
			'orderby'     => 'ID',
		);

		// checking if store checkout is available and removing it from the list of flows.
		$store_checkout_id = intval( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) );
		if ( 0 !== $store_checkout_id ) {
			$args['post__not_in'] = array( $store_checkout_id );
		}

		if ( null !== $request->get_param( 'paged' ) ) {
			$args['paged'] = absint( $request->get_param( 'paged' ) );
		}

		if ( isset( $_REQUEST['mode'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'sandbox' === $_REQUEST['mode'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => 'wcf-testing',
						'value' => 'yes',
					),
				);
			} else {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'   => 'wcf-testing',
						'value' => 'no',
					),
					array(
						'key'     => 'wcf-testing',
						'compare' => 'NOT EXISTS',
					),
				);
			}
		}

		if ( 'any' === $post_status ) {

			if ( null !== $request->get_param( 's' ) ) {
				$args['s'] = sanitize_text_field( $request->get_param( 's' ) );
			}


			if ( null !== $request->get_param( 'post_status' ) ) {

				$status = $request->get_param( 'post_status' );

				if ( 'active' === $status ) {
					$args['post_status'] = 'publish';
				} elseif ( 'inactive' === $status ) {
					$args['post_status'] = 'draft';
				} else {
					$args['post_status'] = sanitize_text_field( wp_unslash( $status ) );
				}
			}

			if ( isset( $_REQUEST['start_date'] ) & isset( $_REQUEST['end_date'] ) ) { //phpcs:ignore
				$args['date_query'] = array(
					array(
						'after'     => sanitize_text_field( wp_unslash( $_REQUEST['start_date'] . ' 00:00:00' ) ), //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'before'    => sanitize_text_field( wp_unslash( $_REQUEST['end_date'] . ' 23:59:59' ) ), //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'inclusive' => true,
						'column'    => 'post_date',
					),
				);
			}
		}

		if ( ! empty( $post_count ) ) {
			$args['posts_per_page'] = $post_count;
		}

		$result = new \WP_Query( $args );

		$data = array(
			'items'      => array(),
			'pagination' => array(),
		);

		if ( $result->have_posts() ) {
			while ( $result->have_posts() ) {
				$result->the_post();

				global $post;

				$post_data = (array) $post;

				// Modify the date Format just to display it.
				$post_data['post_modified'] = date_format( date_create( $post_data['post_modified'] ), 'yy/m/d' );
				$post_data['post_status']   = ucwords( $post_data['post_status'] );

				$view                        = get_permalink( $post->ID );
				$edit                        = admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $post->ID );
				$delete                      = '#';
				$clone                       = '#';
				$export                      = '#';
				$post_data['flow_test_mode'] = ( 'yes' === wcf()->options->get_flow_meta_value( $post->ID, 'wcf-testing' ) ) ? true : false;
				$post_data['actions']        = array(
					'view'      => array(
						'action' => 'edit',
						'class'  => '',
						'attr'   => array( 'target' => '_blank' ),
						'text'   => __( 'View', 'cartflows' ),
						'link'   => $view,

					),
					'edit'      => array(
						'action' => 'edit',
						'class'  => '',
						'attr'   => array(),
						'text'   => __( 'Edit', 'cartflows' ),
						'link'   => $edit,

					),
					'duplicate' => array(
						'action' => 'clone',
						'attr'   => array(),
						'class'  => '',
						'text'   => __( 'Duplicate', 'cartflows' ),
						'link'   => $clone,
					),
					'export'    => array(
						'action' => 'export',
						'attr'   => array(),
						'class'  => '',
						'text'   => __( 'Export', 'cartflows' ),
						'link'   => $export,
					),
					'delete'    => array(
						'action' => 'delete',
						'attr'   => array(),
						'class'  => '',
						'text'   => __( 'Delete', 'cartflows' ),
						'link'   => $delete,
					),
				);

				// Fetch the revenue only for free version for the PRO it will be fetched and added by the filter later in the code.
				if ( ! _is_cartflows_pro() && ! is_wcf_pro_plan() ) {
					$post_data['revenue'] = $this->get_per_flow_revenue( $post->ID );
				}

				$data['items'][] = $post_data;
			}
		}

		$data['found_posts'] = $result->found_posts;
		$data['post_status'] = isset( $post_data['post_status'] ) ? $post_data['post_status'] : $args['post_status'];

		$data['active_flows_count'] = intval( wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->publish );
		$data['trash_flows_count']  = intval( wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->trash );
		$data['draft_flows_count']  = intval( wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->draft );

		$data['pagination'] = array(
			'found_posts' => $result->found_posts,
			'paged'       => $result->query['paged'],
			'max_pages'   => $result->max_num_pages,
		);

		// Reducing count of active_flows_count if store checkout is set.
		if ( 0 !== $store_checkout_id ) {
			$data['active_flows_count']--;
		}

		wp_reset_postdata();

		$data['status'] = true;

		// Retrieve the revenue data from the PRO to display it on the flow listing page.
		if ( _is_cartflows_pro() && is_wcf_pro_plan() ) {
			$data = apply_filters( 'cartflows_admin_flows_page_data', $data );
		}

		if ( ! $result->have_posts() ) {
			$data['status'] = false;
			$response       = new \WP_REST_Response( $data );
			$response->set_status( 200 );
			return $response;
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
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return new \WP_Error( 'cartflows_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cartflows' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get revenue of flow.
	 *
	 * @param int $flow_id flow id.
	 * @return int
	 */
	public function get_per_flow_revenue( $flow_id ) {

		$gross_sale = 0;

		// Return if WooCommerce is not active.
		if ( ! wcf()->is_woo_active ) {
			return $gross_sale;
		}

		// Fetch primary orders: Checkout, Order Bumps.
		$args = array(
			'status'       => array( 'completed', 'processing', 'cancelled' ), // Accepts a string: one of 'pending', 'processing', 'on-hold', 'completed', 'refunded, 'failed', 'cancelled', or a custom order status.
			'meta_key'     => '_wcf_flow_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'   => $flow_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_compare' => '=', // Possible values are ‘=’, ‘!=’, ‘>’, ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’, ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ (only in WP >= 3.5), and ‘NOT EXISTS’ (also only in WP >= 3.5). Values ‘REGEXP’, ‘NOT REGEXP’ and ‘RLIKE’ were added in WordPress 3.7. Default value is ‘=’.
			'return'       => 'ids', // Accepts a string: 'ids' or 'objects'. Default: 'objects'.
		);

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) && is_array( $orders ) ) {

			foreach ( $orders as $order_id ) {

				$order   = wc_get_order( $order_id );
				$user_id = $order->get_user_id();

				// skip the orders which are placed by the user whose user role is Administrator.
				if ( $user_id && user_can( $user_id, 'cartflows_manage_flows_steps' ) ) {
					continue;
				}

				$order_total = $order->get_total();
				if ( ! $order->has_status( 'cancelled' ) ) {
					$gross_sale += (float) $order_total;
				}
			}
		}

		return str_replace( '&nbsp;', '', wc_price( $gross_sale ) );

	}
}
