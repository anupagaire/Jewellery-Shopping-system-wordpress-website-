<?php
/**
 * Custom modules
 *
 * @package BB Helper
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_BB_Helper' ) ) {
	/**
	 * This class initializes BB Ultimate Addon Helper
	 *
	 * @class Cartflows_BB_Helper
	 */
	class Cartflows_BB_Helper {

		/**
		 * Current step type var
		 *
		 * @since 1.6.15
		 * @var object $step_type
		 */
		public static $step_type;

		/**
		 * Get current post step type
		 *
		 * @since 1.6.15
		 * @var function cartflows_bb_step_type
		 */
		public static function cartflows_bb_step_type() {

			if ( ! isset( self::$step_type ) ) {
				self::$step_type = get_post_meta( get_the_id(), 'wcf-step-type', true );
			}

			return self::$step_type;
		}

		/**
		 * Initializes an array to replace recursive function
		 *
		 * @param string $color returns the bas values.
		 *
		 * @param array  $opacity returns the replacements values.
		 * @param array  $is_array returns the replacements values.
		 */
		public static function cartflows_bb_hex2rgba( $color, $opacity = false, $is_array = false ) {

			$default = $color;

			// Return default if no color provided.
			if ( empty( $color ) ) {
				return $default;
			}

			// Sanitize $color if "#" is provided.
			if ( '#' == $color[0] ) {
				$color = substr( $color, 1 );
			}

			// Check if color has 6 or 3 characters and get values.
			if ( 6 == strlen( $color ) ) {
					$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
			} elseif ( 3 == strlen( $color ) ) {
					$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
			} else {
					return $default;
			}

			// Convert hexadec to rgb.
			$rgb = array_map( 'hexdec', $hex );

			// Check if opacity is set(rgba or rgb).
			if ( false !== $opacity && '' !== $opacity ) {
				if ( abs( $opacity ) > 1 ) {

					$opacity = $opacity / 100;
				}
				$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
			} else {
				$output = 'rgb(' . implode( ',', $rgb ) . ')';
			}

			if ( $is_array ) {
				return $rgb;
			} else {

				return $output;
			}
		}

		/**
		 * Initializes an array to replace recursive function
		 *
		 * @param var   $settings returns the bas values.
		 *
		 * @param array $name returns the replacements values.
		 * @param array $opc returns the replacements values.
		 */
		public static function cartflows_bb_colorpicker( $settings, $name = '', $opc = false ) {

			$hex_color = '';
			$opacity   = '';
			$hex_color = $settings->$name;

			if ( '' != $hex_color && 'r' != $hex_color[0] && 'R' != $hex_color[0] ) {

				if ( true == $opc && isset( $settings->{ $name . '_opc' } ) ) {
					if ( '' !== $settings->{ $name . '_opc' } ) {
						$opacity = $settings->{ $name . '_opc' };
						$rgba    = self::cartflows_bb_hex2rgba( $hex_color, $opacity / 100 );
						return $rgba;
					}
				}

				if ( '#' != $hex_color[0] ) {

					return '#' . $hex_color;
				}
			}

			return $hex_color;

		}

		/**
		 * Provide option to parse a color code.
		 *
		 * @param var $code Returns a hex value for color from rgba or #hex color.
		 * @return string - hex value for the color
		 */
		public static function cartflows_bb_parse_color_to_hex( $code = '' ) {
			$color = '';
			$hex   = '';
			if ( '' != $code ) {
				if ( false !== strpos( $code, 'rgba' ) ) {
					$code  = ltrim( $code, 'rgba(' );
					$code  = rtrim( $code, ')' );
					$rgb   = explode( ',', $code );
					$hex  .= str_pad( dechex( $rgb[0] ), 2, '0', STR_PAD_LEFT );
					$hex  .= str_pad( dechex( $rgb[1] ), 2, '0', STR_PAD_LEFT );
					$hex  .= str_pad( dechex( $rgb[2] ), 2, '0', STR_PAD_LEFT );
					$color = $hex;
				} else {
					$color = ltrim( $code, '#' );
				}
			}
			return $color;
		}

		/**
		 * Check for the Beaver Builder's setting page.
		 *
		 * @since 1.6.x
		 * @var function wcf_is_bb_setting_page
		 */
		public static function wcf_is_bb_setting_page() {

			if ( is_admin() && isset( $_GET['page'] ) && 'fl-builder-settings' === sanitize_text_field( $_GET['page'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return true;
			}

			return false;
		}
	}
}
