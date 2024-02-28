<?php
/**
 * CartFlows Frontend.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Cartflows_Frontend.
 */
class Cartflows_Frontend {

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
	 * Constructor
	 */
	public function __construct() {

		/* Set / Destroy Flow Sessions. Set data */
		add_action( 'wp', array( $this, 'init_actions' ), 1 );

		add_action( 'init', array( $this, 'debug_data_setting_actions' ) );
		add_action( 'init', array( $this, 'setup_optin_checkout_filter' ) );
		/* Enqueue global required scripts */
		add_action( 'wp', array( $this, 'wp_actions' ), 55 );

		/* Modify the checkout order received url to go thank you page in our flow */
		add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'redirect_to_thankyou_page' ), 10, 2 );
	}

	/**
	 * Redirect to thank page if upsell not exists
	 *
	 * @param string $order_receive_url url.
	 * @param object $order order object.
	 * @return string $order_receive_url next step URL.
	 * @since 1.0.0
	 */
	public function redirect_to_thankyou_page( $order_receive_url, $order ) {

		/* Only for thank you page */
		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Only for thank you page' );

		if ( wcf()->flow->is_thankyou_page_exists( $order ) ) {

			if ( _is_wcf_doing_checkout_ajax() ) {

				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();

				if ( ! $checkout_id ) {
					$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );
				}
			} else {
				$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );
			}

			wcf()->logger->log( 'Checkout ID : ' . $checkout_id );

			if ( $checkout_id ) {

				$thankyou_step_id = wcf()->flow->get_thankyou_page_id( $order );

				$thankyou_step_id = apply_filters( 'cartflows_checkout_next_step_id', $thankyou_step_id, $order, $checkout_id );

				if ( $thankyou_step_id ) {

					$query_param = wcf()->utils->may_be_append_query_string(
						// Default query string args.
						array(
							'wcf-key'   => $order->get_order_key(),
							'wcf-order' => $order->get_id(),
						)
					);

					$order_receive_url = add_query_arg(
						$query_param,
						get_permalink( $thankyou_step_id )
					);

				}
			}
		}

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );

		return $order_receive_url;
	}

	/**
	 * Cancel and redirect to checkout
	 *
	 * @param string $return_url url.
	 * @since 1.0.0
	 */
	public function redirect_to_checkout_on_cancel( $return_url ) {

		if ( _is_wcf_doing_checkout_ajax() ) {

			$checkout_id = wcf()->utils->get_checkout_id_from_post_data();

			if ( ! $checkout_id ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );
			}
		} else {
			$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );
		}

		if ( $checkout_id ) {

			$return_url = add_query_arg(
				array(
					'cancel_order' => 'true',
					'_wpnonce'     => wp_create_nonce( 'woocommerce-cancel_order' ),
				),
				get_permalink( $checkout_id )
			);
		}

		return $return_url;
	}


	/**
	 * Remove theme styles.
	 *
	 * @since 1.0.0
	 */
	public function remove_theme_styles() {

		if ( Cartflows_Compatibility::get_instance()->is_compatibility_theme_enabled() ) {
			return;
		}

		$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

		$page_template = apply_filters( 'cartflows_page_template', $page_template );

		if ( ! _wcf_supported_template( $page_template ) ) {
			return;
		}

		// get all styles data.
		global $wp_styles;
		global $wp_scripts;

		$get_stylesheet = 'themes/' . get_stylesheet() . '/';
		$get_template   = 'themes/' . get_template() . '/';

		$remove_styles = apply_filters( 'cartflows_remove_theme_styles', true );

		if ( $remove_styles ) {

			// loop over all of the registered scripts..
			foreach ( $wp_styles->registered as $handle => $data ) {

				if ( strpos( $data->src, $get_template ) !== false || strpos( $data->src, $get_stylesheet ) !== false ) {

					// remove it.
					wp_deregister_style( $handle );
					wp_dequeue_style( $handle );
				}
			}
		}

		$remove_scripts = apply_filters( 'cartflows_remove_theme_scripts', true );

		if ( $remove_scripts ) {

			// loop over all of the registered scripts.
			foreach ( $wp_scripts->registered as $handle => $data ) {

				if ( strpos( $data->src, $get_template ) !== false || strpos( $data->src, $get_stylesheet ) !== false ) {

					// remove it.
					wp_deregister_script( $handle );
					wp_dequeue_script( $handle );
				}
			}
		}

	}

	/**
	 * Update main order data in transient.
	 *
	 * @param array $woo_styles new styles array.
	 * @since 1.0.0
	 * @return array.
	 */
	public function woo_default_css( $woo_styles ) {

		$woo_styles = array(
			'woocommerce-layout'      => array(
				'src'     => plugins_url( 'assets/css/woocommerce-layout.css', WC_PLUGIN_FILE ),
				'deps'    => '',
				'version' => WC_VERSION,
				'media'   => 'all',
				'has_rtl' => true,
			),
			'woocommerce-smallscreen' => array(
				'src'     => plugins_url( 'assets/css/woocommerce-smallscreen.css', WC_PLUGIN_FILE ),
				'deps'    => 'woocommerce-layout',
				'version' => WC_VERSION,
				'media'   => 'only screen and (max-width: ' . apply_filters( 'woocommerce_style_smallscreen_breakpoint', '768px' ) . ')',
				'has_rtl' => true,
			),
			'woocommerce-general'     => array(
				'src'     => plugins_url( 'assets/css/woocommerce.css', WC_PLUGIN_FILE ),
				'deps'    => '',
				'version' => WC_VERSION,
				'media'   => 'all',
				'has_rtl' => true,
			),
		);

		return $woo_styles;
	}

	/**
	 * Init Actions.
	 *
	 * @since 1.0.0
	 */
	public function init_actions() {

		if ( wcf()->utils->is_step_post_type() ) {

			global $post;

			$GLOBALS['wcf_step'] = wcf_get_step( $post->ID );

				do_action( 'cartflows_wp', $post->ID );

			$this->set_flow_session();

			$this->delete_checkout_cookies();
		}
	}

	/**
	 * Set flow session.
	 *
	 * @since 1.0.0
	 */
	public function set_flow_session() {

		global $wp;

		add_action( 'wp_head', array( $this, 'noindex_flow' ) );

		wcf()->utils->do_not_cache();

		if ( _is_wcf_thankyou_type() ) {
			/*
			Set key to support pixel
			*/
			//phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_GET['wcf-key'] ) ) {

				$wcf_key = sanitize_text_field( wp_unslash( $_GET['wcf-key'] ) );

				$_GET['key']     = $wcf_key;
				$_REQUEST['key'] = $wcf_key;
			}

			if ( isset( $_GET['wcf-order'] ) ) {
				$wcf_order = intval( wp_unslash( $_GET['wcf-order'] ) );
				//phpcs:enable WordPress.Security.NonceVerification

				$_GET['order']              = $wcf_order;
				$_REQUEST['order']          = $wcf_order;
				$_GET['order-received']     = $wcf_order;
				$_REQUEST['order-received'] = $wcf_order;

				$wp->set_query_var( 'order-received', $wcf_order );
			}
		}
	}

	/**
	 * Delete checkout cookies.
	 *
	 * @since 1.0.0
	 */
	public function delete_checkout_cookies() {

		if ( _is_wcf_thankyou_type() ) {

			if ( ! wcf()->is_woo_active || ! isset( WC()->session ) ) {
				return;
			}

			$user_key = WC()->session->get_customer_id();

			if ( isset( $_COOKIE[ CARTFLOWS_ACTIVE_CHECKOUT ] ) ) {
				delete_transient( 'wcf_user_' . $user_key . '_checkout_' . sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_ACTIVE_CHECKOUT ] ) ) ); //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
				unset( $_COOKIE[ CARTFLOWS_ACTIVE_CHECKOUT ] ); //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
				setcookie( CARTFLOWS_ACTIVE_CHECKOUT, '', time() - 3600 ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
			}
		}
	}

	/**
	 * Add noindex, nofollow.
	 *
	 * @since 1.0.0
	 */
	public function noindex_flow() {

		$common = Cartflows_Helper::get_common_settings();

		global $post;

		$flow_id       = wcf()->utils->get_flow_id_from_step_id( $post->ID );
		$flow_indexing = get_post_meta( $flow_id, 'wcf-flow-indexing', true );

		if ( ( '' === $flow_indexing && 'enable' === $common['disallow_indexing'] ) || 'disallow' === $flow_indexing ) {
			echo '<meta name="robots" content="noindex,nofollow">';
		}
	}

	/**
	 * WP Actions.
	 *
	 * @since 1.0.0
	 */
	public function wp_actions() {

		if ( wcf()->utils->is_step_post_type() ) {

			if ( ! wcf()->is_woo_active && wcf()->utils->check_is_woo_required_page() ) {
				wp_die( ' This page requires WooCommerce plugin installed and activated!', 'WooCommerce Required' );
			}

			/* CSS Compatibility for All theme */
			add_filter( 'woocommerce_enqueue_styles', array( $this, 'woo_default_css' ), 9999 );

			add_action( 'wp_enqueue_scripts', array( $this, 'remove_theme_styles' ), 9999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'global_flow_scripts' ), 20 );

			/* Load woo templates from plugin */
			add_filter( 'woocommerce_locate_template', array( $this, 'override_woo_template' ), 20, 3 );

			/* Add version class to body in frontend. */
			add_filter( 'body_class', array( $this, 'add_cartflows_lite_version_to_body' ) );

			/* Custom Script Option */
			add_action( 'wp_head', array( $this, 'custom_script_option' ) );

			/* Remove the action applied by the Flatsome theme */
			if ( Cartflows_Compatibility::get_instance()->is_flatsome_enabled() ) {
				$this->remove_flatsome_action();
			}
		}
	}

	/**
	 * Debug Data Setting Actions.
	 *
	 * @since 1.1.14
	 */
	public function debug_data_setting_actions() {

		add_filter( 'cartflows_load_min_assets', array( $this, 'allow_load_minify' ) );
	}

	/**
	 * Get/Set the allow minify option.
	 *
	 * @since 1.1.14
	 */
	public function allow_load_minify() {
		$debug_data     = Cartflows_Helper::get_debug_settings();
		$allow_minified = $debug_data['allow_minified_files'];
		$allow_minify   = false;

		if ( 'enable' === $allow_minified ) {
			$allow_minify = true;
		}

		return $allow_minify;
	}

	/**
	 * Global flow scripts.
	 *
	 * @since 1.0.0
	 */
	public function global_flow_scripts() {

		global $post, $wcf_step;

		$flow           = $wcf_step->get_flow_id();
		$current_step   = $wcf_step->get_current_step();
		$control_step   = $wcf_step->get_control_step();
		$next_step_link = '';
		$compatibility  = Cartflows_Compatibility::get_instance();
		$is_checkout    = _is_wcf_checkout_type();
		$is_optin       = _is_wcf_optin_type();

		if ( _is_wcf_landing_type() ) {

			$next_step_id   = $wcf_step->get_direct_next_step_id();
			$next_step_link = get_permalink( $next_step_id );
		}

		$page_template = get_post_meta( $current_step, '_wp_page_template', true );

		$fb_tracking_settings = Cartflows_Helper::get_facebook_settings();
		$ga_tracking_settings = Cartflows_Helper::get_google_analytics_settings();

		$localize = array(
			'ajax_url'               => admin_url( 'admin-ajax.php', 'relative' ),
			'is_pb_preview'          => $compatibility->is_page_builder_preview(),
			'current_theme'          => $compatibility->get_current_theme(),
			'current_flow'           => $flow,
			'current_step'           => $current_step,
			'control_step'           => $control_step,
			'next_step'              => $next_step_link,
			'page_template'          => $page_template,
			'is_checkout_page'       => $is_checkout,
			'fb_setting'             => $fb_tracking_settings,
			'ga_setting'             => $ga_tracking_settings,
			'active_checkout_cookie' => CARTFLOWS_ACTIVE_CHECKOUT,
			'is_optin'               => $is_optin,
		);

		if ( $is_checkout ) {
			$localize['ajax_url'] = add_query_arg(
				array(
					'wcf_checkout_id' => $current_step,
				),
				$localize['ajax_url']
			);
		}

		$localize = apply_filters( 'global_cartflows_js_localize', $localize );

		$localize_script  = '<!-- script to print the admin localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var cartflows = ' . wp_json_encode( $localize ) . ';';
		$localize_script .= '</script>';

		echo $localize_script; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( _wcf_supported_template( $page_template ) ) {

			wp_enqueue_style( 'wcf-normalize-frontend-global', wcf()->utils->get_css_url( 'cartflows-normalize' ), array(), CARTFLOWS_VER );
		}

		if ( ! wcf()->is_woo_active ) {
			wp_register_script(
				'jquery-cookie',
				CARTFLOWS_URL . 'assets/js/lib/jquery-cookie/jquery.cookie.min.js',
				array( 'jquery' ),
				CARTFLOWS_VER,
				false
			);
		}

		wp_enqueue_style( 'wcf-frontend-global', wcf()->utils->get_css_url( 'frontend' ), array(), CARTFLOWS_VER );

		wp_enqueue_script(
			'wcf-frontend-global',
			wcf()->utils->get_js_url( 'frontend' ),
			array( 'jquery', 'jquery-cookie' ),
			CARTFLOWS_VER,
			false
		);
	}

	/**
	 * Custom Script in head.
	 *
	 * @since 1.0.0
	 */
	public function custom_script_option() {

		/* Add custom script to header in frontend. */
		$script = $this->get_custom_script();

		$flow_script = $this->get_flow_custom_script();

		if ( '' !== $flow_script ) {
			if ( false === strpos( $flow_script, htmlentities( '<script' ) ) ) {
				$flow_script = '<script>' . $flow_script . '</script>';
			}
			echo '<!-- Flow Custom CartFlows Script -->';
			echo html_entity_decode( $flow_script ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<!-- End Flow Custom CartFlows Script -->';
		}

		if ( '' !== $script ) {
			if ( false === strpos( $script, htmlentities( '<script' ) ) ) {
				$script = '<script>' . $script . '</script>';
			}
			echo '<!-- Custom CartFlows Script -->';
			echo html_entity_decode( $script ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<!-- End Custom CartFlows Script -->';
		}
	}

	/**
	 * Override woo templates.
	 *
	 * @param string $template new  Template full path.
	 * @param string $template_name Template name.
	 * @param string $template_path Template Path.
	 * @since 1.1.5
	 * @return string.
	 */
	public function override_woo_template( $template, $template_name, $template_path ) {

		global $woocommerce;

		$_template = $template;

		$plugin_path = CARTFLOWS_DIR . 'woocommerce/template/';

		if ( file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		if ( ! $template ) {
			$template = $_template;
		}

		return $template;
	}

	/**
	 * Remove the action applied by the Flatsome theme.
	 *
	 * @since 1.1.5
	 * @return void.
	 */
	public function remove_flatsome_action() {

		// Remove action where flatsome dequeued the woocommerce's default styles.
		remove_action( 'wp_enqueue_scripts', 'flatsome_woocommerce_scripts_styles', 98 );
	}

	/**
	 * Add version class to body in frontend.
	 *
	 * @since 1.1.5
	 * @param array $classes classes.
	 * @return array $classes classes.
	 */
	public function add_cartflows_lite_version_to_body( $classes ) {

		$classes[] = 'cartflows-' . CARTFLOWS_VER;

		return $classes;

	}

	/**
	 *  Get custom script data.
	 *
	 * @since 1.0.0
	 */
	public function get_custom_script() {

		global $post;

		$script = get_post_meta( $post->ID, 'wcf-custom-script', true );

		$script = $this->maybe_replace_vars( $script );

		return $script;
	}

	/**
	 * Replace the dynamic vars in the custom script.
	 *
	 * @param string $script custom script.
	 * @return string $script modified custom script.
	 *
	 * @since 1.10.0
	 */
	public function maybe_replace_vars( $script ) {
		//phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['wcf-order'] ) && isset( $_GET['wcf-key'] ) ) {

			$order_id  = intval( wp_unslash( $_GET['wcf-order'] ) );
			$order_key = wc_clean( wp_unslash( $_GET['wcf-key'] ) );
			//phpcs:enable WordPress.Security.NonceVerification
			$order = wc_get_order( $order_id );

			if ( $order || $order_key === $order->get_order_key() ) {

				// These variables will be available to use on Upsell, Downsell, Thank you pages of CartFlows only.
				$script = str_replace( '{{order_id}}', $order_id, $script );
				$script = str_replace( '{{txn_id}}', $order->get_transaction_id(), $script );
				$script = str_replace( '{{order_total}}', $order->get_total(), $script );

				$script = apply_filters( 'cartflows_dynamic_js_vars', $script );
			}
		}

		return $script;

	}

	/**
	 *  Get custom script data.
	 *
	 * @since 1.0.0
	 */
	public function get_flow_custom_script() {

		global $post;

		$step_id = $post->ID;

		$flow_id = wcf()->utils->get_flow_id_from_step_id( $step_id );

		$script = get_post_meta( $flow_id, 'wcf-flow-custom-script', true );

		$script = $this->maybe_replace_vars( $script );

		return $script;
	}


	/**
	 * Set appropriate filter sctions.
	 *
	 * @since 1.1.14
	 */
	public function setup_optin_checkout_filter() {

		if ( _is_wcf_doing_optin_ajax() ) {
			/* Modify the optin order received url to go next step */
			remove_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'redirect_to_thankyou_page' ), 10, 2 );
			add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'redirect_optin_to_next_step' ), 10, 2 );
		}
	}

	/**
	 * Redirect to thank page if upsell not exists
	 *
	 * @param string $order_receive_url url.
	 * @param object $order order object.
	 * @return string $order_receive_url next step URL.
	 * @since 1.0.0
	 */
	public function redirect_optin_to_next_step( $order_receive_url, $order ) {

		/* Only for optin page */
		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Only for optin page' );

		if ( _is_wcf_doing_optin_ajax() ) {

			$optin_id = wcf()->utils->get_optin_id_from_post_data();

			if ( ! $optin_id ) {
				$optin_id = wcf()->utils->get_optin_id_from_order( $order );
			}
		} else {
			$optin_id = wcf()->utils->get_optin_id_from_order( $order );
		}

		wcf()->logger->log( 'Optin ID : ' . $optin_id );

		if ( $optin_id ) {

			$wcf_step_obj = wcf_get_step( $optin_id );
			$next_step_id = $wcf_step_obj->get_direct_next_step_id();

			if ( $next_step_id ) {

				$order_receive_url = get_permalink( $next_step_id );
				$query_param       = array(
					'wcf-key'   => $order->get_order_key(),
					'wcf-order' => $order->get_id(),
				);

				if ( 'yes' === wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-pass-fields' ) ) {

					$fields_string = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-pass-specific-fields' );

					$fields = array_map( 'trim', explode( ',', $fields_string ) );

					if ( is_array( $fields ) ) {

						foreach ( $fields as $in => $key ) {
							switch ( $key ) {
								case 'first_name':
									$query_param[ $key ] = $order->get_billing_first_name();
									break;
								case 'last_name':
									$query_param[ $key ] = $order->get_billing_last_name();
									break;
								case 'email':
									$query_param[ $key ] = $order->get_billing_email();
									break;
								default:
									$query_param[ $key ] = $order->get_meta( '_billing_' . $key, true );
									break;
							}
						}
					}
				}

				// Add any existing URL parameters at the end of the URL before redirecting.
				$query_param = wcf()->utils->may_be_append_query_string( $query_param );

				$order_receive_url = add_query_arg(
					$query_param,
					$order_receive_url
				);
			}
		}

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );

		return $order_receive_url;
	}
}

/**
 *  Prepare if class 'Cartflows_Frontend' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Frontend::get_instance();
