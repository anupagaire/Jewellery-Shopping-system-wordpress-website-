<?php
/**
 * CartFlows Flows ajax actions.
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

/**
 * Class Steps.
 */
class MetaData extends AjaxBase {

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
			'json_search_products',
			'json_search_coupons',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Clone step with its meta.
	 */
	public function json_search_products() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows_json_search_products', 'security' );

		global $wpdb;

		if ( ! isset( $_POST['term'] ) ) {
			return;
		}

		$term = ! empty( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

		// CartFlows supported product types.
		$supported_product_types = array( 'simple', 'variable', 'variation', 'subscription', 'variable-subscription', 'subscription_variation', 'course' );

		// Allowed product types.
		if ( isset( $_POST['allowed_products'] ) && ! empty( $_POST['allowed_products'] ) ) {

			$allowed_product_types = sanitize_text_field( ( wp_unslash( $_POST['allowed_products'] ) ) );

			$allowed_product_types = $this->sanitize_data_attributes( $allowed_product_types );

			$supported_product_types = $allowed_product_types;
		}

		// Include product types.
		if ( isset( $_POST['include_products'] ) && ! empty( $_POST['include_products'] ) ) {

			$include_product_types = sanitize_text_field( ( wp_unslash( $_POST['include_products'] ) ) );

			$include_product_types = $this->sanitize_data_attributes( $include_product_types );

			$supported_product_types = array_merge( $supported_product_types, $include_product_types );
		}

		// Exclude product types.
		if ( isset( $_POST['exclude_products'] ) && ! empty( $_POST['exclude_products'] ) ) {

			$excluded_product_types = sanitize_text_field( ( wp_unslash( $_POST['exclude_products'] ) ) );

			$excluded_product_types = $this->sanitize_data_attributes( $excluded_product_types );

			$supported_product_types = array_diff( $supported_product_types, $excluded_product_types );
		}

		// Get all products data.
		$data = \WC_Data_Store::load( 'product' );
		$ids  = $data->search_products( $term, '', true, false, 11 );

		// Get all product objects.
		$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );

		// Remove the product objects whose product type are not in supported array.
		$product_objects = array_filter(
			$product_objects,
			function ( $arr ) use ( $supported_product_types ) {
				return $arr && is_a( $arr, 'WC_Product' ) && in_array( $arr->get_type(), $supported_product_types, true );
			}
		);

		$products_found = array();

		foreach ( $product_objects as $product_object ) {
			$formatted_name = $product_object->get_name();
			$managing_stock = $product_object->managing_stock();

			if ( $managing_stock && ! empty( $_GET['display_stock'] ) ) {
				$stock_amount = $product_object->get_stock_quantity();
				/* Translators: %d stock amount */
				$formatted_name .= ' &ndash; ' . sprintf( __( 'Stock: %d', 'cartflows' ), wc_format_stock_quantity_for_display( $stock_amount, $product_object ) );
			}

			array_push(
				$products_found,
				array(
					'value'          => $product_object->get_id(),
					'label'          => $formatted_name,
					'original_price' => \Cartflows_Helper::get_product_original_price( $product_object ),
					'product_name'   => $product_object->get_name(),
					'product_desc'   => $product_object->get_short_description(),
					'product_image'  => get_the_post_thumbnail_url( $product_object->get_id() ),
				)
			);
		}

		wp_send_json( $products_found );

	}

	/**
	 * Function to search coupons
	 */
	public function json_search_coupons() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows_json_search_coupons', 'security' );

		global $wpdb;

		if ( ! isset( $_POST['term'] ) ) {
			return;
		}

		$term = ! empty( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		if ( empty( $term ) ) {
			die();
		}

		$posts = wp_cache_get( 'wcf_search_coupons', 'wcf_funnel_Cart' );

		if ( false === $posts ) {
			$posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT *
								FROM {$wpdb->prefix}posts
								WHERE post_type = %s
								AND post_title LIKE %s
								AND post_status = %s",
					'shop_coupon',
					$wpdb->esc_like( $term ) . '%',
					'publish'
				)
			); // db call ok.
			wp_cache_set( 'wcf_search_coupons', $posts, 'wcf_funnel_Cart' );
		}

		$coupons_found      = array();
		$all_discount_types = wc_get_coupon_types();

		if ( $posts ) {
			foreach ( $posts as $post ) {

				$discount_type = get_post_meta( $post->ID, 'discount_type', true );

				if ( ! empty( $all_discount_types[ $discount_type ] ) ) {
					array_push(
						$coupons_found,
						array(
							'value' => get_the_title( $post->ID ),
							'label' => get_the_title( $post->ID ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')',
						)
					);
				}
			}
		}

		wp_send_json( $coupons_found );
	}



	/**
	 * Function to sanitize the product type data attribute.
	 *
	 * @param array $product_types product types.
	 */
	public function sanitize_data_attributes( $product_types = array() ) {

		if ( ! is_array( $product_types ) ) {
				$product_types = explode( ',', $product_types );
		}

			// Sanitize the excluded types against valid product types.
		foreach ( $product_types as $index => $value ) {
			$product_types[ $index ] = strtolower( trim( $value ) );
		}
			return $product_types;
	}


}
