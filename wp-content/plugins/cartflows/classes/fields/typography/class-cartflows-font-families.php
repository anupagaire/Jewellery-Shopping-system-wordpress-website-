<?php
/**
 * Helper class for font settings.
 *
 * @package     CartFlows
 * @author      CartFlows
 * @copyright   Copyright (c) 2018, CartFlows
 * @link        https://cartflows.com/
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font info class for System and Google fonts.
 */
if ( ! class_exists( 'CartFlows_Font_Families' ) ) :

	/**
	 * Font info class for System and Google fonts.
	 */
	final class CartFlows_Font_Families {

		/**
		 * System Fonts
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public static $system_fonts = array();

		/**
		 * Google Fonts
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public static $google_fonts = array();

		/**
		 * Get System Fonts
		 *
		 * @since 1.0.0
		 *
		 * @return Array All the system fonts in CartFlows
		 */
		public static function get_system_fonts() {
			if ( empty( self::$system_fonts ) ) {
				self::$system_fonts = array(
					'Helvetica' => array(
						'fallback' => 'Verdana, Arial, sans-serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Verdana'   => array(
						'fallback' => 'Helvetica, Arial, sans-serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Arial'     => array(
						'fallback' => 'Helvetica, Verdana, sans-serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Times'     => array(
						'fallback' => 'Georgia, serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Georgia'   => array(
						'fallback' => 'Times, serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Courier'   => array(
						'fallback' => 'monospace',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
				);
			}

			return apply_filters( 'cartflows_system_fonts', self::$system_fonts );
		}

		/**
		 * Custom Fonts
		 *
		 * @since 1.0.0
		 *
		 * @return Array All the custom fonts in CartFlows
		 */
		public static function get_custom_fonts() {
			$custom_fonts = array();

			return apply_filters( 'cartflows_custom_fonts', $custom_fonts );
		}

		/**
		 * Google Fonts used in CartFlows.
		 * Array is generated from the google-fonts.json file.
		 *
		 * @since 1.0.0
		 *
		 * @return Array Array of Google Fonts.
		 */
		public static function get_google_fonts() {

			if ( empty( self::$google_fonts ) ) {

				$google_fonts_file = CARTFLOWS_DIR . 'classes/fields/typography/google-fonts.php';

				if ( ! file_exists( $google_fonts_file ) ) {
					return array();
				}

				$google_fonts_contents = include $google_fonts_file;

				if ( is_array( $google_fonts_contents ) || is_object( $google_fonts_contents ) ) {
					self::$google_fonts = call_user_func_array( 'array_merge', $google_fonts_contents );
				}
			}

			return apply_filters( 'cartflows_google_fonts', self::$google_fonts );
		}

		/**
		 * Render Fonts
		 *
		 * @param array $post_id  post ID.
		 * @return void
		 */
		public static function render_fonts( $post_id ) {

			$is_exist = metadata_exists( 'post', $post_id, 'wcf-field-google-font-url' );

			// If not exist generate it.
			if ( false === $is_exist ) {
				$google_font_url = self::generate_google_url( $post_id );
				update_post_meta( $post_id, 'wcf-field-google-font-url', esc_url( $google_font_url ) );
			} else {
				$google_font_url = get_post_meta( $post_id, 'wcf-field-google-font-url', true );
			}

			if ( ! empty( $google_font_url ) ) {
				wp_enqueue_style( 'cartflows-google-fonts', esc_url( $google_font_url ), array(), CARTFLOWS_VER, 'all' );
			}
		}

		/**
		 * Get string between
		 *
		 * @param  string $string Input string.
		 * @param  string $start  First string.
		 * @param  string $end    Last string.
		 * @return string         string.
		 */
		public static function get_string_between( $string, $start, $end ) {
			$string = ' ' . $string;
			$ini    = strpos( $string, $start );
			if ( 0 == $ini ) {
				return '';
			}
			$ini += strlen( $start );
			$len  = strpos( $string, $end, $ini ) - $ini;
			return substr( $string, $ini, $len );
		}

		/**
		 * Google Font URL
		 * Combine multiple google font in one URL
		 *
		 * @link https://shellcreeper.com/?p=1476
		 * @param array $fonts      Google Fonts array.
		 */
		public static function google_fonts_url( $fonts ) {

			/* URL */
			$base_url   = '//fonts.googleapis.com/css';
			$font_args  = array();
			$family     = array();
			$google_url = '';

			/* Format Each Font Family in Array */
			foreach ( $fonts as $font_name => $font_weight ) {
				$font_name = str_replace( ' ', '+', $font_name );
				if ( ! empty( $font_weight ) ) {
					if ( is_array( $font_weight ) ) {
						$font_weight = implode( ',', $font_weight );
					}
					$font_family = explode( ',', $font_name );
					$font_family = str_replace( "'", '', wcf_get_prop( $font_family, 0 ) );
					$family[]    = trim( $font_family . ':' . urlencode( trim( $font_weight ) ) );
				} else {
					$family[] = trim( $font_name );
				}
			}

			/* Only return URL if font family defined. */
			if ( ! empty( $family ) ) {

				/* Make Font Family a String */
				$family = implode( '|', $family );

				/* Add font family in args */
				$font_args['family'] = $family;

				$google_url = add_query_arg( $font_args, $base_url );
			}

			return $google_url;
		}

		/**
		 * Generate Google Font URL from the post meta.
		 *
		 * @param  integer $post_id Post ID.
		 */
		public static function generate_google_url( $post_id ) {

			$fonts        = array();
			$system_fonts = self::get_system_fonts();

			$font_fields = array(

				// Checkout and optin fields.
				array(
					'font-family' => 'wcf-base-font-family',
				),
				array(
					'font-family' => 'wcf-input-font-family',
					'font-weight' => 'wcf-input-font-weight',
				),
				array(
					'font-family' => 'wcf-button-font-family',
					'font-weight' => 'wcf-button-font-weight',
				),

				// Checkout fields.
				array(
					'font-family' => 'wcf-heading-font-family',
					'font-weight' => 'wcf-heading-font-weight',
				),

				// Thank you fields.
				array(
					'font-family' => 'wcf-tq-heading-font-family',
					'font-weight' => 'wcf-tq-heading-font-wt',
				),
				array(
					'font-family' => 'wcf-tq-font-family',
				),

			);

			$font_fields = apply_filters( 'cartflows_font_family_fields', $font_fields, $post_id );

			foreach ( $font_fields as $index => $field ) {

				$font_family_value = get_post_meta( $post_id, $field['font-family'], true );

				if ( ! empty( $font_family_value ) && ! isset( $system_fonts[ $font_family_value ] ) ) {

					$font_weight_value = isset( $field['font-weight'] ) ? get_post_meta( $post_id, $field['font-weight'], true ) : '';

					$fonts[ self::get_string_between( $font_family_value, '\'', '\'' ) ] = $font_weight_value;
				}
			}

			return self::google_fonts_url( $fonts );
		}
	}

endif;
