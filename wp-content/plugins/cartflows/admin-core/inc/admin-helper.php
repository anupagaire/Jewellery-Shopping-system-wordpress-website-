<?php
/**
 * CartFlows Admin Helper.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

use Automattic\WooCommerce\Utilities\OrderUtil;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminHelper.
 */
class AdminHelper {

	/**
	 * Meta_options.
	 *
	 * @var object instance
	 */
	public static $meta_options = array();
	/**
	 * Common.
	 *
	 * @var object instance
	 */
	public static $common = null;

	/**
	 * Permalink setting.
	 *
	 * @var object instance
	 */
	public static $permalink_setting = null;

	/**
	 * Facebook.
	 *
	 * @var object instance
	 */
	public static $facebook = null;

	/**
	 * Google_analytics_settings.
	 *
	 * @var object instance
	 */
	public static $google_analytics_settings = null;

	/**
	 * Options.
	 *
	 * @var object instance
	 */
	public static $options = null;

	/**
	 * Get flow meta options.
	 *
	 * @param int $post_id post id.
	 * @return array.
	 */
	public static function get_flow_meta_options( $post_id ) {

		if ( ! isset( self::$meta_options[ $post_id ] ) ) {

			/**
			 * Set metabox options
			 */

			$default_meta = wcf()->options->get_flow_fields( $post_id );
			$stored_meta  = get_post_meta( $post_id );

			/**
			 * Get options
			 */
			self::$meta_options[ $post_id ] = self::get_prepared_meta_options( $default_meta, $stored_meta );
		}

		return self::$meta_options[ $post_id ];
	}

	/**
	 * Get step meta options.
	 *
	 * @param int $step_id step id.
	 * @return array.
	 */
	public static function get_step_meta_options( $step_id ) {

		if ( ! isset( self::$meta_options[ $step_id ] ) ) {

			$step_type   = wcf_get_step_type( $step_id );
			$step_fields = array();
			$step_tabs   = array();

			$default_meta = self::get_step_default_meta( $step_type, $step_id );

			$stored_meta = get_post_meta( $step_id );

			$prepared_options = self::get_prepared_meta_options( $default_meta, $stored_meta );

			$prepared_options = apply_filters( 'cartflows_admin_' . $step_type . '_step_meta_fields', $prepared_options, $step_id );

			$step_tabs = apply_filters( 'cartflows_admin_' . $step_type . '_step_tabs', $step_tabs );

			/**
			 * Get options
			 */
			self::$meta_options[ $step_id ]['type']    = $step_type;
			self::$meta_options[ $step_id ]['tabs']    = $step_tabs;
			self::$meta_options[ $step_id ]['options'] = $prepared_options;
		}

		return self::$meta_options[ $step_id ];
	}

	/**
	 * Merge default and saved meta options.
	 *
	 * @param array $default_meta Default meta.
	 * @param array $stored_meta Saved meta.
	 * @return array.
	 */
	public static function get_prepared_meta_options( $default_meta, $stored_meta ) {

		$meta_options = array();

		// Set stored and override defaults.
		foreach ( $default_meta as $key => $value ) {

			$meta_options[ $key ] = ( isset( $stored_meta[ $key ][0] ) ) ? maybe_unserialize( $stored_meta[ $key ][0] ) : $default_meta[ $key ]['default'];
		}

		return $meta_options;
	}

	/**
	 * Get Common settings.
	 *
	 * @return array.
	 */
	public static function get_common_settings() {

		$options = array();

		$common_default = apply_filters(
			'cartflows_common_settings_default',
			array(
				'global_checkout'          => '',
				'override_global_checkout' => 'enable',
				'disallow_indexing'        => 'disable',
				'default_page_builder'     => 'elementor',
			)
		);

		$common = self::get_admin_settings_option( '_cartflows_common', false, false );

		$common = wp_parse_args( $common, $common_default );

		foreach ( $common as $key => $data ) {
			$options[ '_cartflows_common[' . $key . ']' ] = $data;
		}

		return $options;
	}

	/**
	 * Get admin settings.
	 *
	 * @param string $key key.
	 * @param bool   $default key.
	 * @param bool   $network_override key.
	 *
	 * @return array.
	 */
	public static function get_admin_settings_option( $key, $default = false, $network_override = false ) {

		// Get the site-wide option if we're in the network admin.
		if ( $network_override && is_multisite() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		return $value;
	}

	/**
	 * Get Common settings.
	 *
	 * @return array.
	 */
	public static function get_permalink_settings() {

		$options = array();

		$permalink_default = apply_filters(
			'cartflows_permalink_settings_default',
			array(
				'permalink'           => CARTFLOWS_STEP_PERMALINK_SLUG,
				'permalink_flow_base' => CARTFLOWS_FLOW_PERMALINK_SLUG,
				'permalink_structure' => '',

			)
		);

		$permalink_data = self::get_admin_settings_option( '_cartflows_permalink', false, false );

		$permalink_data = wp_parse_args( $permalink_data, $permalink_default );

		foreach ( $permalink_data as $key => $data ) {
			$options[ '_cartflows_permalink[' . $key . ']' ] = $data;
		}

		return $options;
	}

	/**
	 * Get Common settings.
	 *
	 * @return array.
	 */
	public static function get_facebook_settings() {

		$options = array();

		$facebook_default = array(
			'facebook_pixel_id'                => '',
			'facebook_pixel_add_to_cart'       => 'enable',
			'facebook_pixel_view_content'      => 'enable',
			'facebook_pixel_initiate_checkout' => 'enable',
			'facebook_pixel_add_payment_info'  => 'enable',
			'facebook_pixel_purchase_complete' => 'enable',
			'facebook_pixel_optin_lead'        => 'enable',
			'facebook_pixel_tracking'          => 'disable',
			'facebook_pixel_tracking_for_site' => 'disable',
		);

		$facebook = self::get_admin_settings_option( '_cartflows_facebook', false, false );

		$facebook = wp_parse_args( $facebook, $facebook_default );

		$facebook = apply_filters( 'cartflows_facebook_settings_default', $facebook );

		foreach ( $facebook as $key => $data ) {
			$options[ '_cartflows_facebook[' . $key . ']' ] = $data;
		}

		return $options;
	}

	/**
	 * Get Common settings.
	 *
	 * @return array.
	 */
	public static function get_google_analytics_settings() {

		$options = array();

		$google_analytics_settings_default = apply_filters(
			'cartflows_google_analytics_settings_default',
			array(
				'enable_google_analytics'          => 'disable',
				'enable_google_analytics_for_site' => 'disable',
				'google_analytics_id'              => '',
				'enable_begin_checkout'            => 'disable',
				'enable_add_to_cart'               => 'disable',
				'enable_add_payment_info'          => 'disable',
				'enable_purchase_event'            => 'disable',
			)
		);

		$google_analytics_settings_data = self::get_admin_settings_option( '_cartflows_google_analytics', false, true );

		$google_analytics_settings_data = wp_parse_args( $google_analytics_settings_data, $google_analytics_settings_default );

		foreach ( $google_analytics_settings_data as $key => $data ) {
			$options[ '_cartflows_google_analytics[' . $key . ']' ] = $data;
		}

		return $options;
	}

	/**
	 * Get User role settings.
	 *
	 * @return array.
	 */
	public static function get_user_role_management_settings() {
		global $wp_roles;

		$options = array();

		// Get all user roles.
		$roles_names_array            = array_keys( $wp_roles->get_names() );
		$roles_names_array            = array_diff( $roles_names_array, array( 'administrator' ) );
		$user_role_management_default = array();

		foreach ( $roles_names_array as $role_name ) {
			$user_role_management_default[ $role_name ] = 'no_access';
		}

		$user_role_management = self::get_admin_settings_option( '_cartflows_roles', false, false );

		$user_role_management = wp_parse_args( $user_role_management, $user_role_management_default );

		$user_role_management = apply_filters( 'cartflows_user_role_default_settings', $user_role_management );

		foreach ( $user_role_management as $key => $data ) {
			$options[ '_cartflows_roles[' . $key . ']' ] = $data;
		}

		return $options;
	}

	/**
	 * Get Google Auto-Address Fields settings.
	 *
	 * @return array.
	 */
	public static function get_google_auto_fields_settings() {
		$options = array();

		$google_auto_fields_setting_default = apply_filters(
			'cartflows_google_auto_fields_setting_default',
			array(
				'google_map_api_key' => '',
			)
		);

		$google_auto_fields_settings_data = self::get_admin_settings_option( '_cartflows_google_auto_address', false, true );

		$google_auto_fields_settings_data = wp_parse_args( $google_auto_fields_settings_data, $google_auto_fields_setting_default );

		foreach ( $google_auto_fields_settings_data as $key => $data ) {
			$options[ '_cartflows_google_auto_address[' . $key . ']' ] = $data;
		}

		return $options;
	}

	/**
	 * Clear Page Builder Cache
	 */
	public static function clear_cache() {

		// Clear 'Elementor' file cache.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/**
	 * Get Flows count.
	 */
	public static function get_flows_count() {

		$flow_posts = get_posts(
			array(
				'posts_per_page' => 4,
				'post_type'      => CARTFLOWS_FLOW_POST_TYPE,
				'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
			)
		);

		return count( $flow_posts );
	}

	/**
	 * Font family field.
	 *
	 * @return array field.
	 */
	public static function get_font_family() {

		$font_family[0] = array(
			'value' => '',
			'label' => __( 'Default', 'cartflows' ),
		);

		$system_font_family = array();
		$google_font_family = array();

		foreach ( \CartFlows_Font_Families::get_system_fonts() as $name => $variants ) {
			array_push(
				$system_font_family,
				array(
					'value' => $name,
					'label' => esc_attr( $name ),
				)
			);
		}

		$font_family[1] = array(
			'label'   => __( 'System Fonts', 'cartflows' ),
			'options' => $system_font_family,
		);

		foreach ( \CartFlows_Font_Families::get_google_fonts() as $name => $single_font ) {
			$variants   = wcf_get_prop( $single_font, 'variants' );
			$category   = wcf_get_prop( $single_font, 'category' );
			$font_value = '\'' . esc_attr( $name ) . '\', ' . esc_attr( $category );
			array_push(
				$google_font_family,
				array(
					'value' => $font_value,
					'label' => esc_attr( $name ),
				)
			);
		}

		$font_family[2] = array(
			'label'   => __( 'Google Fonts', 'cartflows' ),
			'options' => $google_font_family,
		);

		return $font_family;
	}

	/**
	 * Get step default meta.
	 *
	 * @param string $step_type type.
	 * @param int    $step_id id.
	 */
	public static function get_step_default_meta( $step_type, $step_id ) {

		$step_default_fields = array();

		switch ( $step_type ) {
			case 'landing':
				$step_default_fields = wcf()->options->get_landing_fields( $step_id );
				break;

			case 'checkout':
				$step_default_fields = wcf()->options->get_checkout_fields( $step_id );
				break;

			case 'thankyou':
				$step_default_fields = wcf()->options->get_thankyou_fields( $step_id );
				break;

			case 'optin':
				$step_default_fields = wcf()->options->get_optin_fields( $step_id );
				break;

			default:
				break;
		}
		$step_default_fields = apply_filters( 'cartflows_admin_' . $step_type . '_step_default_meta_fields', $step_default_fields, $step_id );
		return $step_default_fields;
	}

	/**
	 * Get options.
	 */
	public static function get_options() {

		$general_settings   = self::get_common_settings();
		$permalink_settings = self::get_permalink_settings();
		$fb_settings        = self::get_facebook_settings();
		$ga_settings        = self::get_google_analytics_settings();
		$urm_settings       = self::get_user_role_management_settings();
		$auto_fields        = self::get_google_auto_fields_settings();
		$options            = array_merge( $general_settings, $permalink_settings, $fb_settings, $ga_settings, $urm_settings, $auto_fields );
		$options            = apply_filters( 'cartflows_admin_global_data_options', $options );

		return $options;
	}



	/**
	 * Prepare step data.
	 *
	 * @param  int   $flow_id Flow id.
	 * @param  array $meta_options Meta data.
	 *
	 * @return array
	 */
	public static function prepare_step_data( $flow_id, $meta_options ) {

		$steps = $meta_options['wcf-steps'];

		if ( is_array( $steps ) && ! empty( $steps ) ) {

			foreach ( $steps as $in => $step ) {
				$step_id                             = $step['id'];
				$steps[ $in ]['title']               = get_the_title( $step_id );
				$steps[ $in ]['is_product_assigned'] = \Cartflows_Helper::has_product_assigned( $step_id );

				$steps[ $in ]['actions']      = self::get_step_actions( $flow_id, $step_id );
				$steps[ $in ]['menu_actions'] = self::get_step_actions( $flow_id, $step_id, 'menu' );

				$steps[ $in ]['page_builder_edit'] = \Cartflows_Helper::get_page_builder_edit_link( $step_id );

				/* Add variation data */
				if ( ! empty( $steps[ $in ]['ab-test-variations'] ) ) {

					$ab_test_variations = $steps[ $in ]['ab-test-variations'];

					foreach ( $ab_test_variations as $variation_in => $variation ) {

						$ab_test_variations[ $variation_in ]['title']               = get_the_title( $variation['id'] );
						$ab_test_variations[ $variation_in ]['actions']             = self::get_ab_test_step_actions( $flow_id, $variation['id'] );
						$ab_test_variations[ $variation_in ]['menu_actions']        = self::get_ab_test_step_actions( $flow_id, $variation['id'], 'menu' );
						$ab_test_variations[ $variation_in ]['is_product_assigned'] = \Cartflows_Helper::has_product_assigned( $variation['id'] );
					}

					$steps[ $in ]['ab-test-variations'] = $ab_test_variations;
				}

				if ( ! empty( $steps[ $in ]['ab-test-archived-variations'] ) ) {

					$ab_test_archived_variations = $steps[ $in ]['ab-test-archived-variations'];

					foreach ( $ab_test_archived_variations as $variation_in => $variation ) {

						// Don't add hidden archived steps.
						if ( get_post_meta( $variation['id'], 'wcf-hide-step', true ) ) {
							unset( $ab_test_archived_variations[ $variation_in ] );
							continue;
						}

						$ab_test_archived_variations[ $variation_in ]['actions'] = self::get_ab_test_step_archived_actions( $flow_id, $variation['id'], $variation['deleted'] );
						$ab_test_archived_variations[ $variation_in ]['hide']    = get_post_meta( $variation['id'], 'wcf-hide-step', true );
					}

					$steps[ $in ]['ab-test-archived-variations'] = array_values( $ab_test_archived_variations );
				}
			}

			$steps = apply_filters( 'cartflows_admin_flows_step_data', $steps );
		}

		return $steps;

	}

	/**
	 * Get step actions.
	 *
	 * @param  int    $flow_id Flow id.
	 * @param  int    $step_id Step id.
	 * @param  string $type type.
	 *
	 * @return array
	 */
	public static function get_step_actions( $flow_id, $step_id, $type = 'inline' ) {

		if ( 'menu' === $type ) {
			$actions = array(
				'clone'  => array(
					'slug'       => 'clone',
					'class'      => 'wcf-step-clone',
					'icon_class' => 'dashicons dashicons-admin-page',
					'text'       => __( 'Duplicate', 'cartflows' ),
					'pro'        => true,
					'link'       => '#',
					'ajaxcall'   => 'cartflows_clone_step',
				),
				'abtest' => array(
					'slug'       => 'abtest',
					'class'      => 'wcf-step-abtest',
					'icon_class' => 'dashicons dashicons-forms',
					'text'       => __( 'A/B Test', 'cartflows' ),
					'pro'        => true,
					'link'       => '#',
				),
				'delete' => array(
					'slug'       => 'delete',
					'class'      => 'wcf-step-delete',
					'icon_class' => 'dashicons dashicons-trash',
					'text'       => __( 'Delete', 'cartflows' ),
					'link'       => '#',
					'ajaxcall'   => 'cartflows_delete_step',
				),
			);
		} else {
			$actions = array(
				'view' => array(
					'slug'       => 'view',
					'class'      => 'wcf-step-view',
					'icon_class' => 'dashicons dashicons-visibility',
					'target'     => 'blank',
					'link'       => get_permalink( $step_id ),
				),
				'edit' => array(
					'slug'       => 'edit',
					'class'      => 'wcf-step-edit',
					'icon_class' => 'dashicons dashicons-edit',
					'link'       => \Cartflows_Helper::get_page_builder_edit_link( $step_id ),
				),
			);
		}
		return $actions;
	}

	/**
	 * Get step actions.
	 *
	 * @param  int    $flow_id Flow id.
	 * @param  int    $step_id Step id.
	 * @param  string $type type.
	 *
	 * @return array
	 */
	public static function get_ab_test_step_actions( $flow_id, $step_id, $type = 'inline' ) {

		if ( 'menu' === $type ) {

			$actions = array(
				'clone'    => array(
					'slug'       => 'clone',
					'class'      => 'wcf-ab-test-step-clone',
					'icon_class' => 'dashicons dashicons-admin-page',
					'text'       => __( 'Duplicate', 'cartflows' ),
					'link'       => '#',
					'pro'        => true,
					'ajaxcall'   => 'cartflows_clone_ab_test_step',
				),
				'delete'   => array(
					'slug'       => 'delete',
					'class'      => 'wcf-ab-test-step-delete',
					'icon_class' => 'dashicons dashicons-trash',
					'text'       => __( 'Delete', 'cartflows' ),
					'link'       => '#',
					'ajaxcall'   => 'cartflows_delete_ab_test_step',
				),
				'archived' => array(
					'slug'       => 'archived',
					'class'      => 'wcf-ab-test-step-archived',
					'icon_class' => 'dashicons dashicons-archive',
					'text'       => __( 'Archive', 'cartflows' ),
					'link'       => '#',
				),
				'winner'   => array(
					'slug'       => 'winner',
					'class'      => 'wcf-declare-winner',
					'icon_class' => 'dashicons dashicons-yes-alt',
					'text'       => __( 'Declare as Winner', 'cartflows' ),
					'link'       => '#',
				),
			);

		} else {
			$action_slug = apply_filters( 'cartflows_admin_action_slug', 'wcf-edit-step', $flow_id );
			$actions     = array(
				'view' => array(
					'slug'       => 'view',
					'class'      => 'wcf-step-view',
					'icon_class' => 'dashicons dashicons-visibility',
					'target'     => 'blank',
					'link'       => get_permalink( $step_id ),
				),
				'edit' => array(
					'slug'       => 'edit',
					'class'      => 'wcf-step-edit',
					'icon_class' => 'dashicons dashicons-edit',
					'link'       => \Cartflows_Helper::get_page_builder_edit_link( $step_id ),
				),
			);
		}

		return $actions;
	}

	/**
	 * Get ab test step action.
	 *
	 * @param  int  $flow_id Flow id.
	 * @param  int  $step_id Step id.
	 * @param  bool $deleted Step deleted or archived.
	 * @return array
	 */
	public static function get_ab_test_step_archived_actions( $flow_id, $step_id, $deleted ) {

		if ( $deleted ) {
			$actions = array(
				'archive-hide'   => array(
					'slug'        => 'hide',
					'class'       => 'wcf-step-archive-hide',
					'icon_class'  => '',
					'target'      => 'blank',
					'before_text' => __( 'Deleted variation can\'t be restored.', 'cartflows' ),
					'text'        => __( 'Hide', 'cartflows' ),
					'link'        => '#',
				),
				'archive-delete' => array(
					'slug'       => 'deleteArch',
					'class'      => 'wcf-step-archive-delete',
					'icon_class' => '',
					'target'     => 'blank',
					'text'       => __( 'Delete', 'cartflows' ),
					'link'       => '#',
				),
			);
		} else {

			$actions = array(
				'archive-restore' => array(
					'slug'       => 'restore',
					'class'      => 'wcf-step-archive-restore',
					'icon_class' => '',
					'target'     => 'blank',
					'text'       => __( 'Restore', 'cartflows' ),
					'link'       => '#',
				),
				'archive-delete'  => array(
					'slug'       => 'delete',
					'class'      => 'wcf-step-archive-delete',
					'icon_class' => '',
					'target'     => 'blank',
					'text'       => __( 'Delete', 'cartflows' ),
					'link'       => '#',
				),
			);
		}

		return $actions;
	}

		/**
		 * Calculate earning.
		 *
		 * @param string $start_date start date.
		 * @param string $end_date end date.
		 *
		 * @return array
		 */
	public static function get_earnings( $start_date, $end_date ) {

		$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';

		$start_date = $start_date ? $start_date : gmdate( 'Y-m-d' );
		$end_date   = $end_date ? $end_date : gmdate( 'Y-m-d' );

		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( $start_date . '00:00:00' ) );
		$end_date   = gmdate( 'Y-m-d H:i:s', strtotime( $end_date . '23:59:59' ) );


		if ( _is_cartflows_pro() && is_wcf_pro_plan() ) {
			// Return All Stats.
			return apply_filters(
				'cartflows_home_page_analytics',
				array(
					'order_currency'       => $currency_symbol,
					'total_orders'         => '0',
					'total_revenue'        => '0',
					'total_bump_revenue'   => '0',
					'total_offers_revenue' => '0',
					'total_visits'         => '0',
				),
				$start_date,
				$end_date
			);
		}

		$orders      = self::get_orders_by_flow( $start_date, $end_date );
		$gross_sale  = 0;
		$order_count = 0;

		if ( ! empty( $orders ) ) {

			foreach ( $orders as $order ) {

				$order_id    = $order->ID;
				$order       = wc_get_order( $order_id );
				$order_total = $order ? $order->get_total() : 0;

				$order_count++;

				if ( $order && ! $order->has_status( 'cancelled' ) ) {
					$gross_sale += (float) $order_total;
				}
			}
		}

		// Return All Stats.
		return array(
			'order_currency'       => $currency_symbol,
			'total_orders'         => $order_count,
			'total_revenue'        => str_replace( '&nbsp;', '', wc_price( (float) $gross_sale ) ),
			'total_bump_revenue'   => '0',
			'total_offers_revenue' => '0',
			'total_visits'         => '0',
		);

	}



	/**
	 * Get orders data for flow.
	 *
	 * @since 1.6.15
	 *
	 * @param string $start_date start date.
	 * @param string $end_date end date.
	 * @return wc_order object.
	 */
	public static function get_orders_by_flow( $start_date, $end_date ) {

		global $wpdb;

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$conditions = array(
				'tb1.type' => 'shop_order',
			);

			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';

		} else {
			// Traditional CPT-based orders are in use.

			$conditions = array(
				'tb1.post_type' => 'shop_order',
			);

			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';
		}

		$where = self::get_items_query_where( $conditions );

		$where .= ' AND ( tb1.' . $order_date_key . " BETWEEN IF (tb2.meta_key='wcf-analytics-reset-date'>'" . $start_date . "', tb2.meta_key, '" . $start_date . "')  AND '" . $end_date . "' )";
		$where .= " AND ( ( tb2.meta_key = '_wcf_flow_id' ) OR ( tb2.meta_key = '_cartflows_parent_flow_id' ) )";
		$where .= ' AND tb1.' . $order_status_key . " IN ( 'wc-completed', 'wc-processing', 'wc-cancelled' )";

		$query = 'SELECT tb1.ID, DATE( tb1.' . $order_date_key . ' ) date, tb2.meta_value FROM ' . $order_table . ' tb1
		INNER JOIN ' . $order_meta_table . ' tb2
		ON tb1.ID = tb2.' . $order_id_key . ' '
		. $where;

		return $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
	}


	/**
	 * Prepare where items for query.
	 *
	 * @param array $conditions conditions to prepare WHERE query.
	 * @return string
	 */
	public static function get_items_query_where( $conditions ) {

		global $wpdb;

		$where_conditions = array();

		foreach ( $conditions as $key => $condition ) {

			if ( false !== stripos( $key, 'IN' ) ) {
				$where_conditions[] = $key . $wpdb->prepare( '( %s )', $condition );
			} else {
				$where_conditions[] = $key . $wpdb->prepare( '= %s', $condition );
			}
		}

		if ( ! empty( $where_conditions ) ) {
			return 'WHERE 1 = 1 AND ' . implode( ' AND ', $where_conditions );
		} else {
			return '';
		}
	}

	/**
	 * Check is error in the received response.
	 *
	 * @param object $response Received API Response.
	 * @return array $result Error result.
	 * @since 1.11.8
	 */
	public static function has_api_error( $response ) {

		$result = array(
			'error'          => false,
			'error_message'  => __( 'Ooops! Something went wrong. Please open a support ticket from the website.', 'cartflows' ),
			'call_to_action' => __( 'No error found.', 'cartflows' ),
			'error_code'     => 0,
		);

		if ( is_wp_error( $response ) ) {

			$msg        = $response->get_error_message();
			$error_code = $response->get_error_code();

			if ( 'http_request_failed' === $error_code ) {
				/* translators: %1$s: HTML, %2$s: HTML */
				$msg = $msg . '<br>' . sprintf( __( 'Request timeout error. Please check if the firewall or any security plugin is blocking the outgoing HTTP/HTTPS requests to templates.cartflows.com or not. %1$1sTo resolve this issue, please check this %2$2sarticle%3$3s.', 'cartflows' ), '<br><br>', '<a target="_blank" href="https://cartflows.com/docs/request-timeout-error-while-importing-the-flow-step-templates/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs">', '</a>' );
			}

			$result['error']          = true;
			$result['call_to_action'] = $msg;
			$result['error_code']     = $error_code;

		} elseif ( ! empty( wp_remote_retrieve_response_code( $response ) ) && ! in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201, 204 ), true ) ) {

			$error_message = ! empty( wp_remote_retrieve_response_message( $response ) ) ? wp_remote_retrieve_response_message( $response ) : '';
			$error_body    = ! empty( wp_remote_retrieve_body( $response ) ) ? wp_kses( wp_remote_retrieve_body( $response ), '<p>' ) : '';

			if ( false !== strpos( $error_body, 'MalCare' ) ) {
				/* translators: %1$s: HTML, %2$s: HTML, %3$s: HTML */
				$error_message     = $error_message . '<br>' . sprintf( __( 'Sorry for the inconvenience, but your website seems to be having trouble connecting to our server. %1$s Please open a technical %2$ssupport ticket%3$s and share the server\'s outgoing IP address.', 'cartflows' ), '<br><br>', '<a href="https://cartflows.com/support" target="_blank">', '</a>' );
				$ip_address        = self::get_valid_ip_address();
				$result['message'] = ! empty( $ip_address ) ? __( 'Server\'s outgoing IP address: ', 'cartflows' ) . $ip_address : '';
			}

			$result['error']          = true;
			$result['call_to_action'] = $error_message;
			$result['error_code']     = wp_remote_retrieve_response_code( $response );
		} else {
			$result['response_code'] = wp_remote_retrieve_response_code( $response );
		}

		return $result;
	}

	/**
	 * Convert the array format in the WooCommerce's expected format for display.
	 * Received array format is array( 0=>key, 1=>Key_value ) expected is array( key=>key_value )
	 * This is because of the core rules structure which we have created.
	 *
	 * @param array $options Array of key & value in the array format.
	 * @return array $options
	 */
	public static function sanitize_array_values( $options ) {

		if ( is_array( $options ) ) {

			$sanitized_options = array();

			foreach ( $options as $option_key => $option_value ) {
				$sanitized_options[ trim( $option_value ) ] = trim( $option_value );
			}

			$options = $sanitized_options;
		}

		return $options;
	}

	/**
	 * Get the IP address of the user's server.
	 * To check weather the IP is not blocked by any firewall.
	 *
	 * Note: This IP address is not stored any where in the database.
	 *
	 * @return string A valid IP address.
	 */
	public static function get_valid_ip_address() {

		if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) { //phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders
			return self::validate_ip_address( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) { //phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders
			return self::validate_ip_address( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) ); //phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders, WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__REMOTE_ADDR__
		} elseif ( isset( $_SERVER['SERVER_ADDR'] ) ) { //phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders
			return self::validate_ip_address( sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) );
		} else {
			return '';
		}
	}

	/**
	 * Validate the provided IP address safe to printing.
	 *
	 * @param string $ip_address IP address to validate.
	 * @return string $ip_address Validated IP address for display/use.
	 */
	public static function validate_ip_address( $ip_address ) {

		$ip_address = filter_var( $ip_address, FILTER_VALIDATE_IP, array() );

		return $ip_address ? $ip_address : '';
	}
}

