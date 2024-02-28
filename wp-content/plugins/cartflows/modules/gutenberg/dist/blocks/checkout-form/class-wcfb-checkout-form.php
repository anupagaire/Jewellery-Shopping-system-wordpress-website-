<?php
/**
 * WCFB - Checkout Form Styler.
 *
 * @package WCFB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCFB_Checkout_Form' ) ) {

	/**
	 * Class WCFB_Checkout_Form.
	 */
	class WCFB_Checkout_Form {

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
		 * @since 0.0.1
		 */
		public function register_blocks() {

			// Check if the register function exists.
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			$attr = array(
				'block_id'                => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'               => array(
					'type' => 'string',
				),
				'boxShadowColor'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'boxShadowHOffset'        => array(
					'type'    => 'number',
					'default' => 0,
				),
				'boxShadowVOffset'        => array(
					'type'    => 'number',
					'default' => 0,
				),
				'boxShadowBlur'           => array(
					'type'    => 'number',
					'default' => 0,
				),
				'boxShadowSpread'         => array(
					'type'    => 'number',
					'default' => 0,
				),
				'boxShadowPosition'       => array(
					'type'    => 'string',
					'default' => 'outset',
				),
				'isHtml'                  => array(
					'type' => 'boolean',
				),
				'showprecheckoutoffer'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'formJson'                => array(
					'type'    => 'object',
					'default' => null,
				),
				'fieldVrPadding'          => array(
					'type'    => 'number',
					'default' => 10,
				),
				'fieldHrPadding'          => array(
					'type'    => 'number',
					'default' => 10,
				),
				'headBgColor'             => array(
					'type'    => 'string',
					'default' => '',
				),
				'fieldBgColor'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'fieldLabelColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'fieldInputColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'fieldBorderStyle'        => array(
					'type'    => 'string',
					'default' => 'solid',
				),
				'fieldBorderWidth'        => array(
					'type'    => 'number',
					'default' => '',
				),
				'fieldBorderRadius'       => array(
					'type'    => 'number',
					'default' => '',
				),
				'fieldBorderColor'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'fieldBorderHoverColor'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonAlignment'         => array(
					'type'    => 'string',
					'default' => 'left',
				),
				'buttonVrPadding'         => array(
					'type'    => 'number',
					'default' => 10,
				),
				'buttonHrPadding'         => array(
					'type'    => 'number',
					'default' => 25,
				),
				'buttonBorderStyle'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonBorderWidth'       => array(
					'type'    => 'number',
					'default' => '',
				),
				'buttonBorderRadius'      => array(
					'type'    => 'number',
					'default' => '',
				),
				'buttonBorderColor'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonTextColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonBgColor'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonBorderHoverColor'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonTextHoverColor'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonBgHoverColor'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'fieldSpacing'            => array(
					'type'    => 'number',
					'default' => '',
				),
				'fieldLabelSpacing'       => array(
					'type'    => 'number',
					'default' => '',
				),
				'inputFontSize'           => array(
					'type'    => 'number',
					'default' => '',
				),
				'inputFontSizeType'       => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'inputFontSizeTablet'     => array(
					'type' => 'number',
				),
				'inputFontSizeMobile'     => array(
					'type' => 'number',
				),
				'inputFontFamily'         => array(
					'type'    => 'string',
					'default' => 'Default',
				),
				'inputFontWeight'         => array(
					'type' => 'string',
				),
				'inputFontSubset'         => array(
					'type' => 'string',
				),
				'inputLineHeightType'     => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'inputLineHeight'         => array(
					'type' => 'number',
				),
				'inputLineHeightTablet'   => array(
					'type' => 'number',
				),
				'inputLineHeightMobile'   => array(
					'type' => 'number',
				),
				'inputLoadGoogleFonts'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'buttonFontSize'          => array(
					'type'    => 'number',
					'default' => '',
				),
				'buttonFontSizeType'      => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'buttonFontSizeTablet'    => array(
					'type' => 'number',
				),
				'buttonFontSizeMobile'    => array(
					'type' => 'number',
				),
				'buttonFontFamily'        => array(
					'type'    => 'string',
					'default' => 'Default',
				),
				'buttonFontWeight'        => array(
					'type' => 'string',
				),
				'buttonFontSubset'        => array(
					'type' => 'string',
				),
				'buttonLineHeightType'    => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'buttonLineHeight'        => array(
					'type' => 'number',
				),
				'buttonLineHeightTablet'  => array(
					'type' => 'number',
				),
				'buttonLineHeightMobile'  => array(
					'type' => 'number',
				),
				'buttonLoadGoogleFonts'   => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'errorMsgColor'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'errorMsgBgColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'errorMsgBorderColor'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'msgBorderSize'           => array(
					'type'    => 'number',
					'default' => '',
				),
				'msgBorderRadius'         => array(
					'type'    => 'number',
					'default' => '',
				),
				'msgVrPadding'            => array(
					'type'    => 'number',
					'default' => '',
				),
				'msgHrPadding'            => array(
					'type'    => 'number',
					'default' => '',
				),
				'msgBorderRadiusType'     => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'fieldBorderRadiusType'   => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'buttonBorderRadiusType'  => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'paymentdescriptionColor' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymenttitleColor'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'sectionbgColor'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'informationbgColor'      => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'sectionBorderRadius'     => array(
					'type'    => 'number',
					'default' => '',
				),
				'sectionhrPadding'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'sectionvrPadding'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'sectionhrMargin'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'sectionvrMargin'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'headFontSize'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'headFontSizeType'        => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'headFontSizeTablet'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'headFontSizeMobile'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'headFontFamily'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'headFontWeight'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'headFontSubset'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'headLineHeightType'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'headLineHeight'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'headLineHeightTablet'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'headLineHeightMobile'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'headLoadGoogleFonts'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'globaltextColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'globalbgColor'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'globalFontSize'          => array(
					'type'    => 'number',
					'default' => '',
				),
				'globalFontSizeType'      => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'globalFontSizeTablet'    => array(
					'type' => 'number',
				),
				'globalFontSizeMobile'    => array(
					'type' => 'number',
				),
				'globalFontFamily'        => array(
					'type'    => 'string',
					'default' => 'Default',
				),
				'globalFontWeight'        => array(
					'type' => 'string',
				),
				'globalFontSubset'        => array(
					'type' => 'string',
				),
				'globalLineHeightType'    => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'globalLineHeight'        => array(
					'type' => 'number',
				),
				'globalLineHeightTablet'  => array(
					'type' => 'number',
				),
				'globalLineHeightMobile'  => array(
					'type' => 'number',
				),
				'globalLoadGoogleFonts'   => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'backgroundType'          => array(
					'type'    => 'string',
					'default' => 'color',
				),
				'backgroundImage'         => array(
					'type' => 'object',
				),
				'backgroundPosition'      => array(
					'type'    => 'string',
					'default' => 'center-center',
				),
				'backgroundSize'          => array(
					'type'    => 'string',
					'default' => 'cover',
				),
				'backgroundRepeat'        => array(
					'type'    => 'string',
					'default' => 'no-repeat',
				),
				'backgroundAttachment'    => array(
					'type'    => 'string',
					'default' => 'scroll',
				),
				'backgroundOpacity'       => array(
					'type' => 'number',
				),
				'backgroundImageColor'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'backgroundColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'backgroundHoverColor'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'gradientColor1'          => array(
					'type'    => 'string',
					'default' => '#f16334',
				),
				'gradientColor2'          => array(
					'type'    => 'string',
					'default' => '#f16334',
				),
				'gradientType'            => array(
					'type'    => 'string',
					'default' => 'linear',
				),
				'gradientLocation1'       => array(
					'type'    => 'number',
					'default' => 0,
				),
				'gradientLocation2'       => array(
					'type'    => 'number',
					'default' => 100,
				),
				'gradientAngle'           => array(
					'type'    => 'number',
					'default' => 0,
				),
				'gradientPosition'        => array(
					'type'    => 'string',
					'default' => 'center center',
				),
				'gradientValue'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'errorLabelColor'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'orderReviewColumnColor'         => array(
					'type'    => 'string',
					'default' => '#ffffff',
				),
				'orderReviewColumnTextColor'         => array(
					'type'    => 'string',
					'default' => '#555555',
				),
				'errorFieldBorderColor'   => array(
					'type'    => 'string',
					'default' => '',
				),

				'inputSkins'                      => array(
					'type'    => 'string',
					'default' => 'modern-label',
				),
				'layout'                          => array(
					'type'    => 'string',
					'default' => 'modern-checkout',
				),
				'deviceType'       => array(
					'type'    => 'string',
					'default' => 'Desktop',
				),

				//New attrs.
				'paymentSectionpaddingTop' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingBottom' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingLeft' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingRight' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingTopTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingRightTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingBottomTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingLeftTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingTopMobile' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingRightMobile' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingBottomMobile' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionpaddingLeftMobile' => array(
					'type'    => 'string',
					'default' => '',
				),

				'paymentSectionpaddingType' => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'paymentSectionpaddingTypeTablet' => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'paymentSectionpaddingTypeMobile' => array(
					'type'    => 'string',
					'default' => 'px',
				),

				'paymentSectionMarginTop' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginBottom' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginLeft' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginRight' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginTopTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginRightTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginBottomTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginLeftTablet' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginTopMobile' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginRightMobile' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginBottomMobile' => array(
					'type'    => 'string',
					'default' => '',
				),
				'paymentSectionMarginLeftMobile' => array(
					'type'    => 'string',
					'default' => '',
				),

				'paymentSectionMarginType' => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'paymentSectionMarginTypeTablet' => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'paymentSectionMarginTypeMobile' => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'spacingLink' =>array(
					'type'    => 'string',
					'default' => '',
				),

				'paymentSectionpaddingTypeDesktop'=>array(
					'type'    => 'string',
					'default' => 'px',
				),

				'paymentSectionMarginTypeDesktop'=>array(
					'type'    => 'string',
					'default' => 'px',
				),

				'buttonFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
				'inputFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
				'globalFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
				'headFontStyle'=> array(
					'type'=> 'string',
					'default'=> '',
				),
				'buttonTransform' => array(
					'type' => 'string',
					'default' => '',
				),
				'buttonLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'buttonLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'buttonLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'buttonLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
				'inputTransform' => array(
					'type' => 'string',
					'default' => '',
				),
				'inputLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'inputLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'inputLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'inputLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
				'headTransform' => array(
					'type' => 'string',
					'default' => '',
				),
				'headLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'headLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'headLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'headLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
				'globalTransform' => array(
					'type' => 'string',
					'default' => 'none',
				),
				'globalLetterSpacing' => array(
					'type' => 'number',
					'default' => '',
				),
				'globalLetterSpacingTablet' => array(
					'type' => 'number',
					'default' => '',
				),
				'globalLetterSpacingMobile' => array(
					'type' => 'number',
					'default' => '',
				),
				'globalLetterSpacingType' => array(
					'type' => 'string',
					'default' => 'px',
				),
			);

			$field_border_attr = Cartflows_Gb_Helper::get_instance()->generate_php_border_attribute( 'field' );
			$btn_border_attr = Cartflows_Gb_Helper::get_instance()->generate_php_border_attribute( 'button' );

			$attr = array_merge( $field_border_attr, $btn_border_attr, $attr );

			$attributes = apply_filters( 'cartflows_gutenberg_cf_attributes_filters', $attr );

			register_block_type(
				'wcfb/checkout-form',
				array(
					'attributes'      => $attributes,
					'render_callback' => array( $this, 'render_html' ),
				)
			);
		}

		/**
		 * Render CF HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.15
		 */
		public function render_html( $attributes ) {

			$advanced_classes = Cartflows_Gb_Helper::get_instance()->generate_advanced_setting_classes( $attributes );

			$zindex_wrap = $advanced_classes[ 'zindex_wrap'];

			$main_classes = array(
				'wcf-gb-checkout-form cartflows-gutenberg__checkout-form',
				'cf-block-' . $attributes['block_id'],
				$advanced_classes[ 'desktop_class'],
				$advanced_classes[ 'tab_class'],
				$advanced_classes[ 'mob_class'],
				$advanced_classes[ 'zindex_extention_enabled'] ? 'uag-blocks-common-selector' : '',
			);

			if ( isset( $attributes['className'] ) ) {
				$main_classes[] = $attributes['className'];
			}

			$checkout_fields = array(
				// Input Fields.
				array(
					'filter_slug'  => 'wcf-fields-skins',
					'setting_name' => 'inputSkins',
				),
				array(
					'filter_slug'  => 'wcf-checkout-layout',
					'setting_name' => 'layout',
				),
			);

			if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

				foreach ( $checkout_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					if ( '' !== $attributes[ $setting_name ] ) {

						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name, $attributes ) {

								$value = $attributes[ $setting_name ];

								return $value;
							},
							10,
							1
						);
					}
				}
			}


			do_action( 'cartflows_gutenberg_before_checkout_shortcode', 0 );

			do_action( 'cartflows_gutenberg_checkout_options_filters', $attributes );

			do_action( 'cartflows_gutenberg_before_checkout_shortcode' );

			ob_start();
			?>
			<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>" style="<?php echo esc_html( implode( '', $zindex_wrap ) ); ?>">
				<?php echo do_shortcode( '[cartflows_checkout]' ); ?>
			</div>
			<?php
			return ob_get_clean();
		}
	}

	/**
	 *  Prepare if class 'WCFB_Checkout_Form' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	WCFB_Checkout_Form::get_instance();
}
