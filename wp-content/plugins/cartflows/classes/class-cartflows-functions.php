<?php
/**
 * CartFlows Functions.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fatal error mb_strpos fallback.
 *
 * @param string $string string where to find.
 * @param string $search string to search.
 * @since 1.0.0
 */
function wcf_mb_strpos( $string, $search ) {

	if ( function_exists( 'mb_strpos' ) ) {
		return mb_strpos( $string, $search, 0, 'utf-8' );
	} else {
		return strpos( $string, $search );
	}
}

/**
 * Check if cartflows pro activated.
 *
 * @since 1.0.0
 */
function _is_cartflows_pro() {

	if ( defined( 'CARTFLOWS_PRO_FILE' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if cartflows pro's license is been activated.
 *
 * @since 1.11.8
 */
function _is_cartflows_pro_license_activated() {

	if ( _is_cartflows_pro() && class_exists( 'CartFlows_Pro_Licence' ) ) {
		return CartFlows_Pro_Licence::get_instance()->activate_status;
	}

	return false;
}

/**
 * Returns pro version number.
 *
 * @param int $version version number.
 * @since 1.0.0
 */
function _is_cartflows_pro_ver_less_than( $version ) {

	if ( defined( 'CARTFLOWS_PRO_VER' ) && version_compare( CARTFLOWS_PRO_VER, $version, '<' ) ) {
		return true;
	}

	return false;
}

/**
 * Returns step id.
 *
 * @since 1.0.0
 */
function _get_wcf_post_id() {

	global $post;

	if ( isset( $post->ID ) ) {
		return $post->ID;
	}

	return 0;
}

/**
 * Returns step id.
 *
 * @since 1.0.0
 */
function _get_wcf_step_id() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if it is a landing page?
 *
 * @since 1.0.0
 */
function _is_wcf_landing_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'landing' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Returns landing id.
 *
 * @since 1.0.0
 */
function _get_wcf_landing_id() {

	if ( _is_wcf_landing_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Is custom checkout?
 *
 * @param int $checkout_id checkout ID.
 * @since 1.0.0
 */
function _is_wcf_meta_custom_checkout( $checkout_id ) {

	$is_custom = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-custom-checkout-fields' );

	if ( 'yes' === $is_custom ) {

		return true;
	}

	return false;
}

/**
 * Check if page is cartflow checkout.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_checkout_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'checkout' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Check if AJAX call is in progress.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_doing_checkout_ajax() {

	//phpcs:disable WordPress.Security.NonceVerification
	if ( wp_doing_ajax() || isset( $_GET['wc-ajax'] ) ) {

		if ( ( isset( $_GET['wc-ajax'] ) && isset( $_POST['_wcf_checkout_id'] ) ) || isset( $_GET['wcf_checkout_id'] )
		) {
			return true;
		}
	}
	//phpcs:enable WordPress.Security.NonceVerification

	return false;
}

/**
 * Check if optin AJAX call is in progress.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_doing_optin_ajax() {

	//phpcs:disable WordPress.Security.NonceVerification
	if ( wp_doing_ajax() || isset( $_GET['wc-ajax'] ) ) {

		if ( isset( $_GET['wc-ajax'] ) &&
			isset( $_POST['_wcf_optin_id'] )
		) {
			return true;
		}
	}
	//phpcs:enable WordPress.Security.NonceVerification

	return false;
}

/**
 * Returns checkout ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_checkout_id() {

	if ( _is_wcf_checkout_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if it is checkout shortcode.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_checkout_shortcode() {

	global $post;

	if ( ! empty( $post ) && has_shortcode( $post->post_content, 'cartflows_checkout' ) ) {

		return true;
	}

	return false;
}

/**
 * Check if it is checkout shortcode.
 *
 * @since 1.0.0
 * @param string $content shortcode content.
 * @return bool
 */
function _get_wcf_checkout_id_from_shortcode( $content = '' ) {

	$checkout_id = 0;

	if ( ! empty( $content ) ) {

		$regex_pattern = get_shortcode_regex( array( 'cartflows_checkout' ) );

		preg_match( '/' . $regex_pattern . '/s', $content, $regex_matches );

		if ( ! empty( $regex_matches ) ) {

			if ( 'cartflows_checkout' == $regex_matches[2] ) {

				$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3] ) );
				$attribure_str = str_replace( '"', '', $attribure_str );

				$attributes = wp_parse_args( $attribure_str );

				if ( isset( $attributes['id'] ) ) {
					$checkout_id = $attributes['id'];
				}
			}
		}
	}

	return $checkout_id;
}

/**
 * Check if post type is upsell.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_upsell_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'upsell' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Returns upsell ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_upsell_id() {

	if ( _is_wcf_upsell_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if post is of type downsell.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _is_wcf_downsell_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'downsell' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Get downsell page ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_downsell_id() {

	if ( _is_wcf_downsell_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if page is of thank you type.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _is_wcf_thankyou_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'thankyou' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Get thank you page ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_thankyou_id() {

	if ( _is_wcf_thankyou_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}


/**
 * Check if post type is upsell.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_base_offer_type() {

	$result = false;

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		$step_type = get_post_meta( $post->ID, 'wcf-step-type', true );

		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {

			$result = true;
		}
	}

	return apply_filters( 'cartflows_is_offer_type', $result );
}

/**
 * Returns upsell ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_base_offer_id() {

	if ( _is_wcf_base_offer_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if page is of optin type.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _is_wcf_optin_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'optin' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Get optin page ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_optin_id() {

	if ( _is_wcf_optin_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Define a constant if it is not already defined.
 *
 * @since 3.0.0
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
function wcf_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}


if ( ! function_exists( 'wp_body_open' ) ) {
	/**
	 * Fire the wp_body_open action.
	 *
	 * Added for backwards compatibility to support WordPress versions prior to 5.2.0.
	 */
	function wp_body_open() {
		/**
		 * Triggered after the opening <body> tag.
		 */
		do_action( 'wp_body_open' );
	}
}

/**
 * Check if type is optin by id.
 *
 * @param int $post_id post id.
 *
 * @return int/bool
 * @since 1.0.0
 */
function _wcf_check_is_optin_by_id( $post_id ) {

	if ( 'optin' === get_post_meta( $post_id, 'wcf-step-type', true ) ) {

		return true;
	}

	return false;
}

/**
 * Supported Page Template
 *
 * @param string $page_template current page template.
 *
 * @return int/bool
 * @since 1.5.7
 */
function _wcf_supported_template( $page_template ) {

	if ( in_array( $page_template, array( 'cartflows-default', 'cartflows-canvas' ), true ) ) {

		return true;
	}

	return false;
}

/**
 * Display design Metabox Notice.
 *
 * @return string
 * @since 1.6.15
 */
function wcf_get_page_builder_notice() {

	$notice       = '';
	$page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

	$supported_page_builders = array( 'elementor', 'beaver-builder' );

	$page_builder_data = array(
		'elementor'      => array(
			'name' => 'Elementor',
			'doc'  => 'https://cartflows.com/docs/elementor-widgets-of-cartflows/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs',
		),
		'beaver-builder' => array(
			'name' => 'Beaver Builder',
			'doc'  => 'https://cartflows.com/docs/beaver-builder-modules-of-cartflows/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs',
		),
		'gutenberg'      => array(
			'name' => 'Gutenebrg',
			'doc'  => 'https://cartflows.com/docs/cartflows-gutenberg-blocks/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs',
		),
	);

	if ( isset( $page_builder_data[ $page_builder ] ) ) {

		$page_builder_name = $page_builder_data[ $page_builder ]['name'];
		$doc_link          = $page_builder_data[ $page_builder ]['doc'];

		$notice      = '<div class="wcf-metabox-notice-wrap"><p class="wcf-metabox-notice wcf-pb-modules-notice">';
			$notice .= '<span class="dashicons dashicons-info"></span> ';
			/* translators: %1$s page builder name "string" */
			$notice .= sprintf( __( 'We have introduced %1$1s widgets for CartFlows shortcodes. Now, you can add/change/update design settings directly from the page builder as well.', 'cartflows' ), $page_builder_name );
			$notice .= ' <a href="' . $doc_link . '" target="_blank">' . __( 'Learn More »', 'cartflows' ) . '</span></a>';
		$notice     .= '</p></div>';
	}

	return $notice;
}

/**
 * Get step type.
 *
 * @param int $post_id post id.
 *
 * @return bool|string
 * @since 1.0.0
 */
function wcf_get_step_type( $post_id ) {

	return get_post_meta( $post_id, 'wcf-step-type', true );
}

/**
 * Get get step object.
 *
 * @param int $step_id current step ID.
 * @since 1.5.9
 */
function wcf_get_step( $step_id ) {

	if ( ! isset( wcf()->wcf_step_objs[ $step_id ] ) ) {

		wcf()->wcf_step_objs[ $step_id ] = new Cartflows_Step_Factory( $step_id );
	}

	return wcf()->wcf_step_objs[ $step_id ];
}

/**
 * Check auto-prefill checkout fields feature is enabled or not.
 *
 * @return boolean
 */
function is_auto_prefill_checkout_fields_enabled() {
	return apply_filters( 'cartflows_auto_prefill_checkout_fields', true );
}

/**
 * Is starter plan.
 *
 * @return boolean
 */
function is_wcf_starter_plan() {
	return defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) && CARTFLOWS_PRO_PLUGIN_TYPE === 'starter';
}

/**
 * Is PRO plan.
 *
 * @return boolean
 */
function is_wcf_pro_plan() {
	return defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) && CARTFLOWS_PRO_PLUGIN_TYPE === 'pro';
}

/**
 * Show notice.
 *
 * @param string $title title.
 * @param string $message description.
 * @param string $classes classes.
 */
function wcf_feature_notice( $title, $message, $classes ) {

	$image_path = esc_url( CARTFLOWS_URL . 'assets/images/cartflows-logo-small.jpg' );

	$output          = '<div class="wcf-notice wcf-dismissible-notice notice notice-info is-dismissible ' . $classes . '">';
		$output     .= '<div class="wcf-notice-container">';
			$output .= '<img src=' . $image_path . ' class="wcf-notice-custom-logo" alt="CartFlows Icon" itemprop="logo">';
			$output .= '<div class="wcf-notice-message"><p class="wcf-notice-heading">' . $title . '</p><p class="wcf-notice-description">' . $message . '</p></div>';
		$output     .= '</div>';
	$output         .= '</div>';

	echo wp_kses_post( $output );
}
