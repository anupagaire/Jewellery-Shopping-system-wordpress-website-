<?php
/**
 * CARTFLOWS Helper.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Helper.
 */
class Cartflows_Helper {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @var object Class object.
	 * @access private
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
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $common = null;

	/**
	 * Common Debug data
	 *
	 * @var zapier
	 */
	private static $debug_data = null;


	/**
	 * Permalink settings
	 *
	 * @var permalink_setting
	 */
	private static $permalink_setting = null;

	/**
	 * Google Analytics Settings
	 *
	 * @var permalink_setting
	 */
	private static $google_analytics_settings = null;

	/**
	 * Installed Plugins
	 *
	 * @since 1.1.4
	 *
	 * @access private
	 * @var array Installed plugins list.
	 */
	private static $installed_plugins = null;

	/**
	 * Checkout Fields
	 *
	 * @var checkout_fields
	 */
	private static $checkout_fields = null;

	/**
	 * Facebook pixel global data
	 *
	 * @var faceboook
	 */
	private static $facebook = null;


	/**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @param  string  $key     The option key.
	 * @param  mixed   $default Option default value if option is not available.
	 * @param  boolean $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return string           Return the option value
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
	 * Updates an option from the admin settings page.
	 *
	 * @param string $key       The option key.
	 * @param mixed  $value     The value to update.
	 * @param bool   $network   Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	public static function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}

	/**
	 * Get single setting
	 *
	 * @since 1.1.4
	 *
	 * @param  string $key Option key.
	 * @param  string $default Option default value if not exist.
	 * @return mixed
	 */
	public static function get_common_setting( $key = '', $default = '' ) {
		$settings = self::get_common_settings();

		if ( $settings && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $default;
	}

	/**
	 * This function retrieves global option from database
	 *
	 * @param string $key option meta_key.
	 * @return mixed
	 * @since X.X.X
	 */
	public static function get_global_setting( $key ) {
		$default_global = apply_filters(
			'cartflows_global_settings_default',
			array(
				'_cartflows_store_checkout' => false,
			)
		);

		$value = get_option( $key, false );

		if ( empty( $value ) && isset( $default_global[ $key ] ) ) {
			$value = $default_global[ $key ];
		}

		return $value;

	}

	/**
	 * Get single debug options
	 *
	 * @since 1.1.4
	 *
	 * @param  string $key Option key.
	 * @param  string $default Option default value if not exist.
	 * @return mixed
	 */
	public static function get_debug_setting( $key = '', $default = '' ) {
		$debug_data = self::get_debug_settings();

		if ( $debug_data && array_key_exists( $key, $debug_data ) ) {
			return $debug_data[ $key ];
		}

		return $default;
	}

	/**
	 * Get required plugins for page builder
	 *
	 * @since 1.1.4
	 *
	 * @param  string $page_builder_slug Page builder slug.
	 * @param  string $default Default page builder.
	 * @return array selected page builder required plugins list.
	 */
	public static function get_required_plugins_for_page_builder( $page_builder_slug = '', $default = 'elementor' ) {
		$plugins = self::get_plugins_groupby_page_builders();

		if ( array_key_exists( $page_builder_slug, $plugins ) ) {
			return $plugins[ $page_builder_slug ];
		}

		return $plugins[ $default ];
	}

	/**
	 * Get Plugins list by page builder.
	 *
	 * @since 1.1.4
	 *
	 * @return array Required Plugins list.
	 */
	public static function get_plugins_groupby_page_builders() {

		$divi_status  = self::get_plugin_status( 'divi-builder/divi-builder.php' );
		$theme_status = 'not-installed';
		if ( $divi_status ) {
			if ( true === Cartflows_Compatibility::get_instance()->is_divi_theme_installed() ) {
				$theme_status = 'installed';
				if ( false === Cartflows_Compatibility::get_instance()->is_divi_enabled() ) {
					$theme_status = 'deactivate';
					$divi_status  = 'activate';
				} else {
					$divi_status = '';
				}
			}
		}

		$plugins = array(
			'elementor'      => array(
				'title'   => 'Elementor',
				'plugins' => array(
					array(
						'slug'   => 'elementor', // For download from wp.org.
						'init'   => 'elementor/elementor.php',
						'status' => self::get_plugin_status( 'elementor/elementor.php' ),
					),
				),
			),
			'gutenberg'      => array(
				'title'   => 'Spectra',
				'plugins' => array(
					array(
						'slug'   => 'ultimate-addons-for-gutenberg', // For download from wp.org.
						'init'   => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
						'status' => self::get_plugin_status( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ),
					),
				),
			),
			'divi'           => array(
				'title'         => 'Divi',
				'theme-status'  => $theme_status,
				'plugin-status' => $divi_status,
				'plugins'       => array(
					array(
						'slug'   => 'divi-builder', // For download from wp.org.
						'init'   => 'divi-builder/divi-builder.php',
						'status' => $divi_status,
					),
				),
			),
			'beaver-builder' => array(
				'title'   => 'Beaver Builder',
				'plugins' => array(),
			),
		);

		// Check Pro Exist.
		if ( file_exists( WP_PLUGIN_DIR . '/bb-plugin/fl-builder.php' ) && ! is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ) {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'bb-plugin',
				'init'   => 'bb-plugin/fl-builder.php',
				'status' => self::get_plugin_status( 'bb-plugin/fl-builder.php' ),
			);
		} else {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'beaver-builder-lite-version', // For download from wp.org.
				'init'   => 'beaver-builder-lite-version/fl-builder.php',
				'status' => self::get_plugin_status( 'beaver-builder-lite-version/fl-builder.php' ),
			);
		}

		if ( file_exists( WP_PLUGIN_DIR . '/bb-ultimate-addon/bb-ultimate-addon.php' ) && ! is_plugin_active( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ) ) {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'bb-ultimate-addon',
				'init'   => 'bb-ultimate-addon/bb-ultimate-addon.php',
				'status' => self::get_plugin_status( 'bb-ultimate-addon/bb-ultimate-addon.php' ),
			);
		} else {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'ultimate-addons-for-beaver-builder-lite', // For download from wp.org.
				'init'   => 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php',
				'status' => self::get_plugin_status( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ),
			);
		}

		return $plugins;
	}

	/**
	 * Get plugin status
	 *
	 * @since 1.1.4
	 *
	 * @param  string $plugin_init_file Plguin init file.
	 * @return mixed
	 */
	public static function get_plugin_status( $plugin_init_file ) {

		if ( null == self::$installed_plugins ) {
			self::$installed_plugins = get_plugins();
		}

		if ( ! isset( self::$installed_plugins[ $plugin_init_file ] ) ) {
			return 'install';
		} elseif ( ! is_plugin_active( $plugin_init_file ) ) {
			return 'activate';
		} else {
			return 'inactive';
		}
	}

	/**
	 * Get zapier settings.
	 *
	 * @return  array.
	 */
	public static function get_common_settings() {

		if ( null === self::$common ) {

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

			if ( ! did_action( 'wp' ) ) {
				return $common;
			} else {
				self::$common = $common;
			}
		}

		return self::$common;
	}

	/**
	 * Get debug settings data.
	 *
	 * @return  array.
	 */
	public static function get_debug_settings() {

		if ( null === self::$debug_data ) {

			$debug_data_default = apply_filters(
				'cartflows_debug_settings_default',
				array(
					'allow_minified_files' => 'disable',
				)
			);

			$debug_data = self::get_admin_settings_option( '_cartflows_debug_data', false, false );

			$debug_data = wp_parse_args( $debug_data, $debug_data_default );

			if ( ! did_action( 'wp' ) ) {
				return $debug_data;
			} else {
				self::$debug_data = $debug_data;
			}
		}

		return self::$debug_data;
	}


	/**
	 * Get debug settings data.
	 *
	 * @return  array.
	 */
	public static function get_permalink_settings() {

		if ( null === self::$permalink_setting ) {

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

			if ( ! did_action( 'wp' ) ) {
				return $permalink_data;
			} else {
				self::$permalink_setting = $permalink_data;
			}
		}

		return self::$permalink_setting;
	}


	/**
	 * Get debug settings data.
	 *
	 * @return  array.
	 */
	public static function get_google_analytics_settings() {

		if ( null === self::$google_analytics_settings ) {

			$google_analytics_settings_default = apply_filters(
				'cartflows_google_analytics_settings_default',
				array(
					'enable_google_analytics'          => 'disable',
					'enable_google_analytics_for_site' => 'disable',
					'google_analytics_id'              => '',
					'enable_begin_checkout'            => 'enable',
					'enable_add_to_cart'               => 'enable',
					'enable_optin_lead'                => 'enable',
					'enable_add_payment_info'          => 'enable',
					'enable_purchase_event'            => 'enable',
				)
			);

			$google_analytics_settings_data = self::get_admin_settings_option( '_cartflows_google_analytics', false, true );

			$google_analytics_settings_data = wp_parse_args( $google_analytics_settings_data, $google_analytics_settings_default );

			if ( ! did_action( 'wp' ) ) {
				return $google_analytics_settings_data;
			} else {
				self::$google_analytics_settings = $google_analytics_settings_data;
			}
		}

		return self::$google_analytics_settings;
	}

	/**
	 * Get Checkout field.
	 *
	 * @param string $key Field key.
	 * @param int    $post_id Post id.
	 * @return array.
	 */
	public static function get_checkout_fields( $key, $post_id ) {

		$saved_fields = get_post_meta( $post_id, 'wcf_fields_' . $key, true );

		if ( ! $saved_fields ) {
			$saved_fields = array();
		}

		$fields = array_filter( $saved_fields );

		if ( empty( $fields ) ) {
			if ( 'billing' === $key || 'shipping' === $key ) {

				$fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), $key . '_' );

				update_post_meta( $post_id, 'wcf_fields_' . $key, $fields );
			}
		}

		return $fields;
	}

	/**
	 * Get checkout fields settings.
	 *
	 * @return  array.
	 */
	public static function get_checkout_fields_settings() {

		if ( null === self::$checkout_fields ) {
			$checkout_fields_default = array(
				'enable_customization'  => 'disable',
				'enable_billing_fields' => 'disable',
			);

			$billing_fields = self::get_checkout_fields( 'billing' );

			if ( is_array( $billing_fields ) && ! empty( $billing_fields ) ) {

				foreach ( $billing_fields as $key => $value ) {

					$checkout_fields_default[ $key ] = 'enable';
				}
			}

			$checkout_fields = self::get_admin_settings_option( '_wcf_checkout_fields', false, false );

			self::$checkout_fields = wp_parse_args( $checkout_fields, $checkout_fields_default );
		}

		return self::$checkout_fields;
	}

	/**
	 * Get Optin fields.
	 *
	 * @return array.
	 */
	public static function get_optin_default_fields() {

		$optin_fields = array(
			'billing_first_name' => array(
				'label'        => __( 'First name', 'cartflows' ),
				'required'     => true,
				'class'        => array(
					'form-row-first',
				),
				'autocomplete' => 'given-name',
				'priority'     => 10,
			),
			'billing_last_name'  => array(
				'label'        => __( 'Last name', 'cartflows' ),
				'required'     => true,
				'class'        => array(
					'form-row-last',
				),
				'autocomplete' => 'family-name',
				'priority'     => 20,
			),
			'billing_email'      => array(
				'label'        => __( 'Email address', 'cartflows' ),
				'required'     => true,
				'type'         => 'email',
				'class'        => array(
					'form-row-wide',
				),
				'validate'     => array(
					'email',
				),
				'autocomplete' => 'email username',
				'priority'     => 30,
			),
		);

		return $optin_fields;
	}

	/**
	 * Get Optin field.
	 *
	 * @param string $key Field key.
	 * @param int    $post_id Post id.
	 * @return array.
	 */
	public static function get_optin_fields( $key, $post_id ) {

		$saved_fields = get_post_meta( $post_id, 'wcf_fields_' . $key, true );

		if ( ! $saved_fields ) {
			$saved_fields = array();
		}

		$fields = array_filter( $saved_fields );

		if ( empty( $fields ) ) {
			if ( 'billing' === $key ) {

				$fields = self::get_optin_default_fields();

				update_post_meta( $post_id, 'wcf_fields_' . $key, $fields );
			}
		}

		return $fields;
	}

	/**
	 * Get meta options
	 *
	 * @since 1.0.0
	 * @param  int    $post_id     Product ID.
	 * @param  string $key      Meta Key.
	 * @param  string $default      Default value.
	 * @return string           Meta Value.
	 */
	public static function get_meta_option( $post_id, $key, $default = '' ) {

		$value = get_post_meta( $post_id, $key, true );

		if ( ! $value ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Check if Elementor page builder is installed
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public static function is_elementor_installed() {
		$path    = 'elementor/elementor.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}

	/**
	 * Check if Step has product assigned.
	 *
	 * @since 1.0.0
	 * @param int $step_id step ID.
	 *
	 * @access public
	 */
	public static function has_product_assigned( $step_id ) {

		$step_type = get_post_meta( $step_id, 'wcf-step-type', true );

		$has_product_assigned = false;

		if ( 'checkout' === $step_type ) {
			$product = get_post_meta( $step_id, 'wcf-checkout-products', true );

			if ( ! empty( $product ) && isset( $product[0]['product'] ) ) {
				$has_product_assigned = true;
			}
		} elseif ( 'optin' === $step_type ) {
			$product = get_post_meta( $step_id, 'wcf-optin-product', true );
			if ( ! empty( $product ) && ! empty( $product[0] ) ) {
				$has_product_assigned = true;
			}
		} else {
			$product = get_post_meta( $step_id, 'wcf-offer-product', true );
			if ( ! empty( $product ) && ! empty( $product[0] ) ) {
				$has_product_assigned = true;
			}
		}

		return $has_product_assigned;

	}

	/**
	 * Get attributes for cartflows wrap.
	 *
	 * @since 1.1.4
	 *
	 * @access public
	 */
	public static function get_cartflows_container_atts() {

		$attributes  = apply_filters(
			'cartflows_container_atts',
			array()
		);
		$atts_string = '';

		foreach ( $attributes as $key => $value ) {

			if ( ! $value ) {
				continue;
			}

			if ( true === $value ) {
				$atts_string .= esc_html( $key ) . ' ';
			} else {
				$atts_string .= sprintf( '%s="%s" ', esc_html( $key ), esc_attr( $value ) );
			}
		}

		return $atts_string;
	}

	/**
	 * Get facebook pixel settings.
	 *
	 * @return  facebook array.
	 */
	public static function get_facebook_settings() {

		if ( null === self::$facebook ) {

			$facebook_default = array(
				'facebook_pixel_id'                => '',
				'facebook_pixel_view_content'      => 'enable',
				'facebook_pixel_add_to_cart'       => 'enable',
				'facebook_pixel_initiate_checkout' => 'enable',
				'facebook_pixel_add_payment_info'  => 'enable',
				'facebook_pixel_purchase_complete' => 'enable',
				'facebook_pixel_optin_lead'        => 'enable',
				'facebook_pixel_tracking'          => 'disable',
				'facebook_pixel_tracking_for_site' => 'disable',
			);

			$facebook = self::get_admin_settings_option( '_cartflows_facebook', false, false );

			$facebook = wp_parse_args( $facebook, $facebook_default );

			self::$facebook = apply_filters( 'cartflows_facebook_settings_default', $facebook );

		}

		return self::$facebook;
	}


	/**
	 * Prepare response data for facebook.
	 *
	 * @todo Remove this function in 1.6.18 as it is added in cartflows-tracking file.
	 * @param int   $order_id order_id.
	 * @param array $offer_data offer data.
	 * @return void
	 */
	public static function send_fb_response_if_enabled( $order_id, $offer_data = array() ) {

		_deprecated_function( __METHOD__, '1.6.15' );

		// Stop Execution if WooCommerce is not installed & don't set the cookie.
		if ( ! Cartflows_Loader::get_instance()->is_woo_active ) {
			return;
		}

		// @Since 1.6.15 It will only trigger offer purchase event.
		$fb_settings = self::get_facebook_settings();
		if ( 'enable' === $fb_settings['facebook_pixel_tracking'] ) {
			setcookie( 'wcf_order_details', wp_json_encode( self::prepare_purchase_data_fb_response( $order_id, $offer_data ) ), strtotime( '+1 year' ), '/' ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
		}

	}

	/**
	 * Prepare purchase response for facebook purcase event.
	 *
	 * @todo Remove this function in 1.6.18 as it is added in cartflows-tracking file.
	 *
	 * @param integer $order_id order id.
	 * @param array   $offer_data offer data.
	 * @return mixed
	 */
	public static function prepare_purchase_data_fb_response( $order_id, $offer_data ) {

		_deprecated_function( __METHOD__, '1.6.15' );

		$purchase_data = array();

		if ( empty( $offer_data ) ) {
			return $purchase_data;
		}

		if ( ! Cartflows_Loader::get_instance()->is_woo_active ) {
			return $purchase_data;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return $purchase_data;
		}

		$purchase_data['content_type'] = 'product';
		$purchase_data['currency']     = wcf()->options->get_checkout_meta_value( $order_id, '_order_currency' );
		$purchase_data['userAgent']    = wcf()->options->get_checkout_meta_value( $order_id, '_customer_user_agent' );
		$purchase_data['plugin']       = 'CartFlows-Offer';

		$purchase_data['content_ids'][]      = (string) $offer_data['id'];
		$purchase_data['content_names'][]    = $offer_data['name'];
		$purchase_data['content_category'][] = wp_strip_all_tags( wc_get_product_category_list( $offer_data['id'] ) );
		$purchase_data['value']              = $offer_data['total'];
		$purchase_data['transaction_id']     = $order_id;

		return $purchase_data;
	}

	/**
	 * Get the image url of size.
	 *
	 * @param int    $post_id post id.
	 * @param array  $key key.
	 * @param string $size image size.
	 *
	 * @return array
	 */
	public static function get_image_url( $post_id, $key, $size = false ) {

		$url     = get_post_meta( $post_id, $key, true );
		$img_obj = get_post_meta( $post_id, $key . '-obj', true );

		if ( is_array( $img_obj ) && ! empty( $img_obj ) && false !== $size ) {

			$url = ! empty( $img_obj['url'][ $size ] ) ? $img_obj['url'][ $size ] : $url;
		}

		return $url;
	}

	/**
	 * Download File Into Uploads Directory
	 *
	 * @since 1.6.15
	 *
	 * @param  string $file Download File URL.
	 * @param  array  $overrides Upload file arguments.
	 * @param  int    $timeout_seconds Timeout in downloading the XML file in seconds.
	 * @return array        Downloaded file data.
	 */
	public static function download_file( $file = '', $overrides = array(), $timeout_seconds = 300 ) {

		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Download file to temp dir.
		$temp_file = download_url( $file, $timeout_seconds );

		// WP Error.
		if ( is_wp_error( $temp_file ) ) {
			return array(
				'success' => false,
				'data'    => $temp_file->get_error_message(),
			);
		}

		// Array based on $_FILE as seen in PHP file uploads.
		$file_args = array(
			'name'     => basename( $file ),
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize( $temp_file ),
		);

		$defaults = array(

			// Tells WordPress to not look for the POST form
			// fields that would normally be present as
			// we downloaded the file from a remote server, so there
			// will be no form fields
			// Default is true.
			'test_form'   => false,

			// Setting this to false lets WordPress allow empty files, not recommended.
			// Default is true.
			'test_size'   => true,

			// A properly uploaded file will pass this test. There should be no reason to override this one.
			'test_upload' => true,

			'mimes'       => array(
				'xml'  => 'text/xml',
				'json' => 'text/plain',
			),
		);

		$overrides = wp_parse_args( $overrides, $defaults );

		// Move the temporary file into the uploads directory.
		$results = wp_handle_sideload( $file_args, $overrides );

		if ( isset( $results['error'] ) ) {
			return array(
				'success' => false,
				'data'    => $results,
			);
		}

		// Success.
		return array(
			'success' => true,
			'data'    => $results,
		);
	}

	/**
	 * Get an instance of WP_Filesystem_Direct.
	 *
	 * @since 1.6.15
	 * @return object A WP_Filesystem_Direct instance.
	 */
	public static function get_filesystem() {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';

		WP_Filesystem();

		return $wp_filesystem;
	}

	/**
	 * Get all flow and steps
	 *
	 * @since 1.6.15
	 * @return array
	 */
	public function get_all_flows_and_steps() {
		$all_flows = array(
			'elementor'      => array(),
			'divi'           => array(),
			'gutenberg'      => array(),
			'beaver-builder' => array(),
		);
		foreach ( $all_flows as $slug => $value ) {
			$all_flows[ $slug ] = $this->get_flows_and_steps( $slug );
		}

		return $all_flows;
	}

	/**
	 * Get flow and steps
	 *
	 * @since 1.6.15
	 *
	 * @param  string $page_builder_slug Page builder slug.
	 * @param string $templates templates category to fetch.
	 *
	 * @return array
	 */
	public function get_flows_and_steps( $page_builder_slug = '', $templates = '' ) {
		$page_builder_slug = ( ! empty( $page_builder_slug ) ) ? $page_builder_slug : wcf()->get_site_slug();

		$suffix = 'store-checkout' === $templates ? 'store-checkout-' : '';

		$pages_count = get_site_option( 'cartflows-' . $suffix . $page_builder_slug . '-requests', 0 );

		$flows = array();
		if ( $pages_count ) {
			for ( $page_no = 1; $page_no <= $pages_count; $page_no++ ) {

				$data = get_site_option( 'cartflows-' . $suffix . $page_builder_slug . '-flows-and-steps-' . $page_no, '' );

				if ( ! empty( $data ) ) {

					$data = 'string' === gettype( $data ) ? json_decode( $data ) : $data;

					foreach ( $data as $key => $flow ) {
						$flows[] = $flow;
					}
				} else {
					// Return store templates from JSON files if no data found.
					$flows = $this->get_stored_page_builder_templates( $page_builder_slug );
				}
			}
		} else {
			// All flows.
			$flows = $this->get_stored_page_builder_templates( $page_builder_slug );
		}

		return $flows;
	}

	/**
	 * Get page builder name
	 *
	 * @since 1.1.4
	 *
	 * @param  string $page_builder Page builder slug.
	 * @return mixed
	 */
	public static function get_page_builder_name( $page_builder = '' ) {

		$pb_data = array(
			'elementor'      => 'Elementor',
			'gutenberg'      => 'Spectra',
			'beaver-builder' => 'Beaver Builder',
			'divi'           => 'Divi',
		);

		if ( isset( $pb_data[ $page_builder ] ) ) {

			return $pb_data[ $page_builder ];
		}

		return '';
	}

	/**
	 * Create Edit page link for the widgets.
	 *
	 * @since 1.6.15
	 * @param string $tab The Tab which has to display.
	 * @access public
	 */
	public static function get_current_page_edit_url( $tab ) {

		$url = add_query_arg(
			array(
				'wcf-tab' => $tab,
			),
			get_edit_post_link()
		);

		return $url;
	}

	/**
	 * Get product price.
	 *
	 * @param object $product product data.
	 */
	public static function get_product_original_price( $product ) {

		$custom_price = '';
		$product_id   = 0;

		if ( $product->is_type( 'variable' ) ) {

			$default_attributes = $product->get_default_attributes();

			if ( ! empty( $default_attributes ) ) {

				foreach ( $product->get_children() as $c_in => $variation_id ) {

					if ( 0 === $c_in ) {
						$product_id = $variation_id;
					}

					$single_variation = new \WC_Product_Variation( $variation_id );

					if ( $default_attributes == $single_variation->get_attributes() ) {

						$product_id = $variation_id;
						break;
					}
				}
			} else {

				$product_childrens = $product->get_children();

				if ( is_array( $product_childrens ) && ! empty( $product_childrens ) ) {

					foreach ( $product_childrens  as $c_in => $c_id ) {

						$_child_product = wc_get_product( $c_id );

						if ( $_child_product->is_in_stock() && 'publish' === $_child_product->get_status() ) {
							$product_id = $c_id;
							break;
						}
					}
				} else {

					// Return if no childrens found.
					return;
				}
			}

			$product = wc_get_product( $product_id );
		}

		if ( $product ) {
			$custom_price = $product->get_price( 'edit' );
		}

		return $custom_price;
	}

	/**
	 * Get flows from json files.
	 *
	 * @param string $page_builder_slug Selected page builder slug.
	 */
	public function get_stored_page_builder_templates( $page_builder_slug ) {
		$flows = array();

		$dir        = CARTFLOWS_DIR . 'admin-core/assets/importer-data';
		$list_files = \list_files( $dir );
		if ( ! empty( $list_files ) ) {
			$list_files = array_map( 'basename', $list_files );
			foreach ( $list_files as $file_key => $file_name ) {
				if ( false !== strpos( $file_name, 'cartflows-' . $page_builder_slug . '-flows-and-steps' ) ) {
					// file_get_contents is fine for local files. https://github.com/WordPress/WordPress-Coding-Standards/pull/1374/files#diff-400e43bc09c24262b43f26fce487fdabR43-R52.
					$data = json_decode( file_get_contents( $dir . '/' . $file_name ), true ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					if ( ! empty( $data ) ) {
						/*
						Commented.
							// $option_name = str_replace( '.json', '', $file_name );
							// update_site_option( $option_name, json_decode( $data, true ) );
						*/
						foreach ( $data as $key => $flow ) {
							$flows[] = $flow;
						}
					}
				}
			}
		}

		return $flows;

	}

	/**
	 * Get meta keys to exclude.
	 *
	 * @param int $step_id post id.
	 */
	public function get_meta_keys_to_exclude_from_import( $step_id = 0 ) {

		$meta_keys = array(
			'_wp_old_slug',
			'wcf-checkout-products',
			'wcf-optin-product',
			'wcf-testing',
		);

		if ( $step_id && 'yes' === get_post_meta( $step_id, 'cartflows_imported_step', true ) ) {

			$meta_keys = array_merge(
				$meta_keys,
				array(
					'wcf_fields_billing',
					'wcf_fields_shipping',
				)
			);
		}

		return apply_filters(
			'cartflows_admin_exclude_import_meta_keys',
			$meta_keys
		);

	}

	/**
	 * Maybe update flow step order.
	 *
	 * @param int   $flow_id flow id.
	 * @param array $flow_steps step list.
	 *
	 * @return array flow_steps step list.
	 */
	public function maybe_update_flow_steps( $flow_id, $flow_steps ) {

		if ( absint( self::get_global_setting( '_cartflows_store_checkout' ) ) === $flow_id ) {
			$key = array_search( 'thankyou', wp_list_pluck( $flow_steps, 'type' ), true );
			if ( ! $key ) {
				return $flow_steps;
			} else {
				$thankyou     = array_splice( $flow_steps, intval( $key ), 1 );
				$flow_steps[] = $thankyou[0];
			}
		}

		return $flow_steps;
	}

	/**
	 * Prepare cusom field settings array
	 *
	 * @param array  $data_array field array.
	 * @param string $key field key.
	 * @param array  $field_args field args.
	 * @param array  $fields_type field type.
	 * @param string $step_type step type.
	 *
	 * @return array field data array.
	 */
	public function prepare_custom_field_settings( $data_array, $key, $field_args, $fields_type, $step_type = 'checkout' ) {

		if ( 'checkout' === $step_type ) {
			$name = 'billing' === $fields_type ? 'wcf_field_order_billing[' . $key . ']' : 'wcf_field_order_shipping[' . $key . ']';
		} else {
			$name = 'wcf-optin-fields-billing[' . $key . ']';
		}

		$type            = $field_args['type'];
		$is_checkbox     = 'checkbox' == $type ? true : false;
		$is_radiobutton  = 'radio' == $type ? true : false;
		$is_select       = 'select' == $type ? true : false;
		$is_custom_field = isset( $field_args['custom'] ) && true === (bool) $field_args['custom'] ? true : false;

		$data_array['field_options'] = array(
			'enable-field'  => array(
				'label' => __( 'Enable Field', 'cartflows' ),
				'name'  => $name . '[enabled]',
				'value' => $field_args['enabled'],
			),
			'select-width'  => array(
				'type'    => 'select',
				'label'   => __( 'Field Width', 'cartflows' ),
				'name'    => $name . '[width]',
				'value'   => $field_args['width'],
				'options' => array(
					array(
						'value' => '33',
						'label' => esc_html__( '33%', 'cartflows' ),
					),
					array(
						'value' => '50',
						'label' => esc_html__( '50%', 'cartflows' ),
					),
					array(
						'value' => '100',
						'label' => esc_html__( '100%', 'cartflows' ),
					),
				),

			),
			'field-label'   => array(
				'type'  => 'text',
				'label' => __( 'Field Label', 'cartflows' ),
				'name'  => $name . '[label]',
				'value' => $field_args['label'],
			),
			'field-name'    => array(
				'label'    => __( 'Field ID', 'cartflows' ),
				'name'     => $name . '[key]',
				'value'    => $field_args['key'],
				'readonly' => true,
				'tooltip'  => __( 'Copy this field id to use in Order Custom Field rule of dynamic offers.', 'cartflows' ),
			),

			'field-default' => $is_checkbox ?
				array(
					'type'    => 'select',
					'label'   => __( 'Default', 'cartflows' ),
					'name'    => $name . '[default]',
					'value'   => $field_args['default'],
					'options' => array(
						array(
							'value' => '1',
							'label' => esc_html__( 'Checked', 'cartflows' ),
						),
						array(
							'value' => '0',
							'label' => esc_html__( 'Un-Checked', 'cartflows' ),
						),
					),
				) :

				array(
					'type'  => 'text',
					'label' => __( 'Default', 'cartflows' ),
					'name'  => $name . '[default]',
					'value' => $field_args['default'],
				),
		);

		if ( $is_select || $is_radiobutton ) {

			$data_array['field_options']['select-options'] = array(
				'type'  => 'text',
				'label' => __( 'Options', 'cartflows' ),
				'name'  => $name . '[options]',
				'value' => $field_args['options'],
			);
		}

		if ( in_array( $field_args['type'], array( 'datetime-local', 'date', 'time' ), true ) ) {

			switch ( $field_args['type'] ) {
				case 'datetime-local':
					$date_placeholder = 'yyyy-mm-dd hh:mm';
					break;
				case 'date':
					$date_placeholder = 'yyyy-mm-dd';
					break;
				case 'time':
					$date_placeholder = 'hh:mm';
					break;
				default:
					$date_placeholder = 'yyyy-mm-dd hh:mm';
			}

			$data_array['field_options']['field-min-date'] = array(
				'type'        => 'text',
				'label'       => __( 'Min Date', 'cartflows' ),
				'name'        => $name . '[custom_attributes][min]',
				'value'       => $field_args['custom_attributes']['min'],
				'placeholder' => $date_placeholder,
			);
			$data_array['field_options']['field-max-date'] = array(
				'type'        => 'text',
				'label'       => __( 'Max Date', 'cartflows' ),
				'name'        => $name . '[custom_attributes][max]',
				'value'       => $field_args['custom_attributes']['max'],
				'placeholder' => $date_placeholder,
			);

			$data_array['field_options']['field-default']['placeholder'] = $date_placeholder;
		}

		if ( ! in_array( $type, array( 'checkbox', 'select', 'radio', 'datetime-local', 'date', 'time', 'number' ), true ) ) {
			$data_array['field_options']['field-placeholder'] = array(
				'type'  => 'text',
				'label' => __( 'Placeholder', 'cartflows' ),
				'name'  => $name . '[placeholder]',
				'value' => $field_args['placeholder'],
			);
		}

		if ( 'number' === $type ) {
			$data_array['field_options']['field-min'] = array(
				'type'  => 'number',
				'label' => __( 'Min Number', 'cartflows' ),
				'name'  => $name . '[custom_attributes][min]',
				'value' => $field_args['custom_attributes']['min'],
			);
			$data_array['field_options']['field-max'] = array(
				'type'  => 'number',
				'label' => __( 'Max Number', 'cartflows' ),
				'name'  => $name . '[custom_attributes][max]',
				'value' => $field_args['custom_attributes']['max'],
			);
		}

		if ( $is_custom_field ) {
			$data_array['field_options']['show-in-email'] = array(
				'type'  => 'checkbox',
				'label' => __( 'Show In Email', 'cartflows' ),
				'name'  => $name . '[show_in_email]',
				'value' => $field_args['show_in_email'],
			);
		}

		$data_array['field_options']['required-field'] = array(
			'label' => __( 'Required', 'cartflows' ),
			'name'  => $name . '[required]',
			'value' => $field_args['required'],
		);

		if ( 'checkout' === $step_type ) {
			$data_array['field_options']['collapsed-field'] = array(
				'type'  => 'checkbox',
				'label' => __( 'Collapsible', 'cartflows' ),
				'name'  => $name . '[optimized]',
				'value' => $field_args['optimized'],
			);
		}

		return $data_array;
	}

	/**
	 * Get step edit link.
	 *
	 * @param int $step_id Step id.
	 */
	public static function get_page_builder_edit_link( $step_id ) {

		$edit_step         = get_edit_post_link( $step_id, 'edit' );
		$view_step         = get_permalink( $step_id );
		$page_builder      = self::get_common_setting( 'default_page_builder' );
		$page_builder_edit = $edit_step;

		switch ( $page_builder ) {
			case 'beaver-builder':
				$page_builder_edit = strpos( $view_step, '?' ) ? $view_step . '&fl_builder' : $view_step . '?fl_builder';
				break;
			case 'elementor':
				$page_builder_edit = admin_url( 'post.php?post=' . $step_id . '&action=elementor' );
				break;
		}

		return $page_builder_edit;
	}

	/**
	 * Get CartFlows Global Color Pallet CSS_Vars data.
	 *
	 * @since x.x.x.
	 * @return array Array of GCP vars slugs and label,
	 */
	public static function get_gcp_vars() {
		return array(
			'wcf-gcp-primary-color'   => __( 'CartFlows Primary Color', 'cartflows' ),
			'wcf-gcp-secondary-color' => __( 'CartFlows Secondary Color', 'cartflows' ),
			'wcf-gcp-text-color'      => __( 'CartFlows Text Color', 'cartflows' ),
			'wcf-gcp-accent-color'    => __( 'CartFlows Heading/Accent Color', 'cartflows' ),
		);
	}

	/**
	 * Generate GCP styles CSS and assign the colors to the GCP CSS VARs.
	 *
	 * @param int $flow_id The current flow ID.
	 * @return array $gcp_vars Array of generated CSS.
	 *
	 * @since x.x.x
	 */
	public static function generate_gcp_css_style( $flow_id = 0 ) {

		$gcp_vars = '';

		if ( empty( $flow_id ) ) {
			return $gcp_vars;
		}

		$gcp_vars_array = array_keys( self::get_gcp_vars() );

		foreach ( $gcp_vars_array as $slug ) {
			// Gather value of global color VAR.
			$color_value = wcf()->options->get_flow_meta_value( $flow_id, $slug, '' );

			if ( empty( $color_value ) ) {
				continue;
			}

			// Convert it into the CSS var.
			$gcp_vars .= '--' . $slug . ': ' . $color_value . '; ';
		}

		return $gcp_vars;
	}

	/**
	 * Generate the array of CSS vars to add in the Gutenberg color pallet.
	 *
	 * @param int $flow_id The current Flow ID.
	 * @return array $new_color_palette Prepared array of CSS vars.
	 *
	 * @since x.x.x
	 */
	public static function generate_css_var_array( $flow_id = 0 ) {

		if ( empty( $flow_id ) ) {
			$flow_id = wcf()->utils->get_flow_id();
		}

		// Default Color Pallet used as a separator.
		$new_color_palette[] = array(
			'name'  => 'CartFlows Separator',
			'slug'  => 'wcf-gcp-separator',
			'color' => '#ff0000',
		);

		$cf_gcp_data = self::get_gcp_vars();

		// Prepare new colors css vars.
		foreach ( $cf_gcp_data as $slug => $label ) {

			$color_value = get_post_meta( $flow_id, $slug, true );

			if ( empty( $color_value ) ) {
				continue;
			}

			// Add CartFlows Global Color Pallets CSS vars options.
			$new_color_palette[] = array(
				'name'  => $label,
				'slug'  => $slug,
				'color' => 'var( --' . $slug . ')',
			);
		}

		return $new_color_palette;
	}

	/**
	 * Check is the Global Color pallet is enabled or not.
	 *
	 * @param int $flow_id Current flow id.
	 * @return boolean
	 */
	public static function is_gcp_styling_enabled( $flow_id ) {

		if ( empty( $flow_id ) ) {
			return false;
		}

		return 'yes' === wcf()->options->get_flow_meta_value( $flow_id, 'wcf-enable-gcp-styling', 'no' );
	}
}

