<?php
/**
 * CartFlows Flows Stats ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsAdmin\AdminCore\Inc\AdminHelper;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Class Flows.
 */
class FlowsStats extends AjaxBase {

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
	 * Register ajax events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_events() {

		$ajax_events = array(
			'get_all_flows_stats',
		);

		$this->init_ajax_events( $ajax_events );
	}


	/**
	 * Get all Flows Stats.
	 */
	public function get_all_flows_stats() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_get_all_flows_stats', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( ! wcf()->is_woo_active ) {
			wp_send_json_success(
				array(
					'flow_stats'       => array(),
					'order_data'       => array(),
					'last_week_orders' => array(),
					'recent_orders'    => array(),
				)
			);
		}

		$start_date = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$end_date   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		$dashboard_analytics_data = AdminHelper::get_earnings( $start_date, $end_date );

		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( $start_date . '00:00:00' ) );
		$end_date   = gmdate( 'Y-m-d H:i:s', strtotime( $end_date . '23:59:59' ) );

		global $wpdb;

		$decimal_point_pos     = wc_get_price_decimals();
		$order_revenue_by_date = '';

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';
			$order_type_key   = 'type';
			$order_table_id   = 'id';

			//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$order_revenue_by_date = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DATE_FORMAT(o.$order_date_key, '%%Y-%%m-%%d') AS OrderDate, ROUND(SUM(o.total_amount), $decimal_point_pos) AS Revenue
					FROM $order_table o
					WHERE o.$order_type_key = 'shop_order'
						AND o.$order_status_key IN ('wc-completed', 'wc-processing', 'wc-cancelled')
						AND o.$order_date_key >= %s
						AND o.$order_date_key <= %s
					GROUP BY OrderDate
					ORDER BY OrderDate ASC",
					$start_date,
					$end_date
				)
			);
			//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		} else {
			// Traditional CPT-based orders are in use.
			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';
			$order_type_key   = 'post_type';
			$order_table_id   = 'ID';

			//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$order_revenue_by_date = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DATE_FORMAT(o.$order_date_key, '%%Y-%%m-%%d') AS OrderDate, ROUND(SUM(m.meta_value), $decimal_point_pos) AS Revenue
					FROM $order_table o
					INNER JOIN $order_meta_table m ON o.$order_table_id = m.$order_id_key
					WHERE o.$order_type_key = 'shop_order'
						AND o.$order_status_key IN ('wc-completed', 'wc-processing', 'wc-cancelled')
						AND m.meta_key = '_order_total'
						AND o.$order_date_key >= %s
						AND o.$order_date_key <= %s
					GROUP BY OrderDate
					ORDER BY OrderDate ASC",
					$start_date,
					$end_date
				)
			);
			//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$orders_by_date = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT($order_date_key, '%%Y-%%m-%%d') AS OrderDate, COUNT(*) AS OrderCount
				FROM $order_table
				WHERE $order_type_key = 'shop_order'
					AND $order_status_key IN ('wc-completed', 'wc-processing', 'wc-cancelled')
					AND $order_date_key >= %s
					AND $order_date_key <= %s
					AND EXISTS (
						SELECT 1
						FROM $order_meta_table AS om
						WHERE $order_id_key = $order_table.$order_table_id
							AND (om.meta_key = '_wcf_flow_id' OR om.meta_key = '_cartflows_parent_flow_id')
					)
				GROUP BY OrderDate
				ORDER BY OrderDate ASC
				",
				$start_date,
				$end_date
			)
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$dashboard_analytics_data['orders_by_date']  = $orders_by_date;
		$dashboard_analytics_data['revenue_by_date'] = $order_revenue_by_date;

		$response = array(
			'flow_stats'    => $dashboard_analytics_data,
			'recent_orders' => $this->get_recent_orders(),
		);

		wp_send_json_success( $response );

	}

	/**
	 * Get all orders placed via CartFlows.
	 *
	 * @return array $recent_orders_data array of orders.
	 */
	public function get_recent_orders() {

		$recent_orders = wc_get_orders(
			array(
				'limit'        => 5,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'meta_key'     => '_wcf_flow_id', //phpcs:ignore
				'meta_compare' => 'EXISTS',
			)
		);

		$recent_orders_data = array();

		foreach ( $recent_orders as $recent_order ) {
			$recent_orders_data[] = array(
				'order_id'       => $recent_order->get_id(),
				'customer_name'  => $recent_order->get_billing_first_name() . ' ' . $recent_order->get_billing_last_name(),
				'customer_email' => $recent_order->get_billing_email(),
				'payment_method' => $recent_order->get_payment_method_title(),
				'order_total'    => get_woocommerce_currency_symbol( $recent_order->get_currency() ) . $recent_order->get_total(),
				'order_status'   => ucfirst( $recent_order->get_status() ),
				'order_currency' => $recent_order->get_currency(),
				'order_date'     => wc_format_datetime( $recent_order->get_date_created(), 'M j, Y' ),
				'order_time'     => wc_format_datetime( $recent_order->get_date_created(), 'g:i A' ),
			);
		}

		return $recent_orders_data;
	}
}
