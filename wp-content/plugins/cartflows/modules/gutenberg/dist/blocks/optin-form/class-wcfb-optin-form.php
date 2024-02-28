<?php
/**
 * WCFB - Optin Detail Form.
 *
 * @package UAGB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCFB_Optin_Form' ) ) {

	/**
	 * Class WCFB_Optin_Form.
	 */
	class WCFB_Optin_Form {

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

			// Activation hook.
			add_action( 'init', array( $this, 'register_blocks' ) );
		}

		/**
		 * Registers the `core/latest-posts` block on server.
		 *
		 * @since 1.6.15
		 */
		public function register_blocks() {

			// Check if the register function exists.
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			$attr = array(
				'block_id'                         => array(
					'type' => 'string',
				),
				'classMigrate'                     => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'className'                        => array(
					'type' => 'string',
				),
				// General.
				'generalPrimaryColor'              => array(
					'type'    => 'string',
					'default' => '',
				),
				// general font family.
				'generalLoadGoogleFonts'           => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'generalFontFamily'                => array(
					'type' => 'string',
				),
				'generalFontWeight'                => array(
					'type' => 'string',
				),
				'generalFontSubset'                => array(
					'type' => 'string',
				),
				// general font size.
				'generalFontSize'                  => array(
					'type' => 'number',
				),
				'generalFontSizeType'              => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'generalFontSizeTablet'            => array(
					'type' => 'number',
				),
				'generalFontSizeMobile'            => array(
					'type' => 'number',
				),
				// general line height.
				'generalLineHeightType'            => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'generalLineHeight'                => array(
					'type' => 'number',
				),
				'generalLineHeightTablet'          => array(
					'type' => 'number',
				),
				'generalLineHeightMobile'          => array(
					'type' => 'number',
				),
				'generalTransform' => array(
					'type' => 'string',
					'default' => 'none',
				),
				'generalLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'generalLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'generalLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'generalLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
				// Input Fields.
				// input field font family.
				'inputFieldLoadGoogleFonts'        => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'inputFieldFontFamily'             => array(
					'type' => 'string',
				),
				'inputFieldFontWeight'             => array(
					'type' => 'string',
				),
				'inputFieldFontSubset'             => array(
					'type' => 'string',
				),
				// input field font size.
				'inputFieldFontSize'               => array(
					'type' => 'number',
				),
				'inputFieldFontSizeType'           => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'inputFieldFontSizeTablet'         => array(
					'type' => 'number',
				),
				'inputFieldFontSizeMobile'         => array(
					'type' => 'number',
				),
				// input field line height.
				'inputFieldLineHeightType'         => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'inputFieldLineHeight'             => array(
					'type' => 'number',
				),
				'inputFieldLineHeightTablet'       => array(
					'type' => 'number',
				),
				'inputFieldLineHeightMobile'       => array(
					'type' => 'number',
				),
				'inputFieldLabelColor'             => array(
					'type'    => 'string',
					'default' => '',
				),
				'inputFieldBackgroundColor'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'inputFieldTextPlaceholderColor'   => array(
					'type'    => 'string',
					'default' => '',
				),
				// border.
				'inputFieldBorderStyle'            => array(
					'type'    => 'string',
					'default' => 'solid',
				),
				'inputFieldBorderWidth'            => array(
					'type' => 'number',
					'default' => '',
				),
				'inputFieldBorderRadius'           => array(
					'type' => 'number',
					'default' => '',
				),
				'inputFieldBorderColor'            => array(
					'type'    => 'string',
					'default' => '',
				),
				// 'inputFieldBorderHoverColor'            => array(
				// 	'type'    => 'string',
				// 	'default' => '',
				// ),
				'inputFieldBorderHColor'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'inputFieldTransform' => array(
					'type' => 'string',
					'default' => '',
				),
				'inputFieldLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'inputFieldLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'inputFieldLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'inputFieldLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
				// Submit Button.
				// submit button font family.

				'submitButtonLoadGoogleFonts'      => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'submitButtonFontFamily'           => array(
					'type' => 'string',
				),
				'submitButtonFontWeight'           => array(
					'type' => 'string',
				),
				'submitButtonFontSubset'           => array(
					'type' => 'string',
				),
				// submit button font size.
				'submitButtonFontSize'             => array(
					'type' => 'number',
				),
				'submitButtonFontSizeType'         => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'submitButtonFontSizeTablet'       => array(
					'type' => 'number',
				),
				'submitButtonFontSizeMobile'       => array(
					'type' => 'number',
				),
				// submit button line height.
				'submitButtonLineHeightType'       => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'submitButtonLineHeight'           => array(
					'type' => 'number',
				),
				'submitButtonLineHeightTablet'     => array(
					'type' => 'number',
				),
				'submitButtonLineHeightMobile'     => array(
					'type' => 'number',
				),
				'submitButtonTextColor'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'submitButtonBackgroundColor'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'submitButtonTextHoverColor'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'submitButtonBackgroundHoverColor' => array(
					'type'    => 'string',
					'default' => '',
				),
				// border.
				'submitButtonBorderStyle'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'submitButtonBorderWidth'          => array(
					'type' => 'number',
					'default' => '',
				),
				'submitButtonBorderRadius'         => array(
					'type' => 'number',
					'default' => '',
				),
				'submitButtonBorderColor'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'submitButtonTransform' => array(
					'type' => 'string',
					'default' => '',
				),
				'submitButtonLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'submitButtonLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'submitButtonLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'submitButtonLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
				// 'submitButtonBorderHoverColor'     => array(
				// 	'type'    => 'string',
				// 	'default' => '',
				// ),
				'submitButtonBorderHColor'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'boxShadowColor'                   => array(
					'type'    => 'string',
					'default' => '',
				),
				'boxShadowHOffset'                 => array(
					'type' => 'number',
				),
				'boxShadowVOffset'                 => array(
					'type' => 'number',
				),
				'boxShadowBlur'                    => array(
					'type' => 'number',
				),
				'boxShadowSpread'                  => array(
					'type' => 'number',
				),
				'boxShadowPosition'                => array(
					'type'    => 'string',
					'default' => 'outset',
				),
				'input_skins' => array(
					'type'    => 'string',
					'default' => '',
				),
				'deviceType'       => array(
					'type'    => 'string',
					'default' => 'Desktop',
				),
				'generalFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
				'submitButtonFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
				'inputFieldFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
			);

			$field_border_attr = Cartflows_Gb_Helper::get_instance()->generate_php_border_attribute( 'inputField' );
			$btn_border_attr = Cartflows_Gb_Helper::get_instance()->generate_php_border_attribute( 'submitButton' );

			$attr = array_merge( $field_border_attr, $btn_border_attr, $attr );
			$attributes = apply_filters( 'cartflows_gutenberg_optin_attributes_filters', $attr );

			register_block_type(
				'wcfb/optin-form',
				array(
					'attributes'      => $attributes,
					'render_callback' => array( $this, 'render_html' ),
				)
			);

		}



		/**
		 * Settings
		 *
		 * @since 1.6.15
		 * @var object $settings
		 */
		public static $settings;


		/**
		 * Render Optin Detail Form HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.15
		 */
		public function render_html( $attributes ) {

			self::$settings = $attributes;

			$advanced_classes = Cartflows_Gb_Helper::get_instance()->generate_advanced_setting_classes( $attributes );
			$zindex_wrap = $advanced_classes[ 'zindex_wrap'];

			$main_classes = array(
				$advanced_classes[ 'desktop_class'],
				$advanced_classes[ 'tab_class'],
				$advanced_classes[ 'mob_class'],
				$advanced_classes[ 'zindex_extention_enabled'] ? 'uag-blocks-common-selector' : '',
			);

			if ( isset( $attributes['block_id'] ) ) {
				$main_classes[] = 'cf-block-' . $attributes['block_id'];
			}

			if ( isset( $attributes['className'] ) ) {
				$main_classes[] = $attributes['className'];
			}

			$classes = array(
				'wpcf__optin-form',
			);

			$optin_fields = array(

				// Input Fields.
				array(
					'filter_slug'  => 'wcf-input-fields-skins',
					'setting_name' => 'input_skins',
				),
			);

			if ( isset( $optin_fields ) && is_array( $optin_fields ) ) {

				foreach ( $optin_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					add_filter(
						'cartflows_optin_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_name, $attributes ) {

							$value = $attributes[ $setting_name ];

							return $value;
						},
						10,
						1
					);
				}
			}

			do_action( 'cartflows_gutenberg_optin_options_filters', $attributes );

			ob_start();

			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>" style="<?php echo esc_html( implode( '', $zindex_wrap ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php
						echo do_shortcode( '[cartflows_optin]' );
						?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}


	}

	/**
	 *  Prepare if class 'WCFB_Optin_Form' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	WCFB_Optin_Form::get_instance();
}
