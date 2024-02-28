<?php
/**
 * WCFB - Order Detail Form.
 *
 * @package UAGB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCFB_Order_Detail_Form' ) ) {

	/**
	 * Class WCFB_Order_Detail_Form.
	 */
	class WCFB_Order_Detail_Form {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;


		/**
		 * Settings
		 *
		 * @since 1.6.15
		 * @var object $settings
		 */
		public static $settings;

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

			register_block_type(
				'wcfb/order-detail-form',
				array(
					'attributes'      => array(
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
						// Genaral.
						'orderOverview'                    => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'orderDetails'                     => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'billingAddress'                   => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'shippingAddress'                  => array(
							'type'    => 'boolean',
							'default' => true,
						),
						// Spacing.
						'headingBottomSpacing'             => array(
							'type' => 'number',
						),
						'sectionSpacing'                   => array(
							'type' => 'number',
						),
						// Heading.
						'thanyouText'                      => array(
							'type'    => 'string',
							'default' => '',
						),
						'headingAlignment'                 => array(
							'type'    => 'string',
							'default' => 'center',
						),
						'headingColor'                     => array(
							'type'    => 'string',
							'default' => '',
						),
						// heading font family.
						'headingLoadGoogleFonts'           => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'headingFontFamily'                => array(
							'type' => 'string',
						),
						'headingFontWeight'                => array(
							'type' => 'string',
						),
						'headingFontSubset'                => array(
							'type' => 'string',
						),
						// heading font size.
						'headingFontSize'                  => array(
							'type' => 'number',
						),
						'headingFontSizeType'              => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'headingFontSizeTablet'            => array(
							'type' => 'number',
						),
						'headingFontSizeMobile'            => array(
							'type' => 'number',
						),
						// heading line height.
						'headingLineHeightType'            => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'headingLineHeight'                => array(
							'type' => 'number',
						),
						'headingLineHeightTablet'          => array(
							'type' => 'number',
						),
						'headingLineHeightMobile'          => array(
							'type' => 'number',
						),
						// Sections.
						'sectionHeadingColor'              => array(
							'type' => 'string',
						),
						// section heading font family.
						'sectionHeadingLoadGoogleFonts'    => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'sectionHeadingFontFamily'         => array(
							'type' => 'string',
						),
						'sectionHeadingFontWeight'         => array(
							'type' => 'string',
						),
						'sectionHeadingFontSubset'         => array(
							'type' => 'string',
						),
						// section heading font size.
						'sectionHeadingFontSize'           => array(
							'type' => 'number',
						),
						'sectionHeadingFontSizeType'       => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'sectionHeadingFontSizeTablet'     => array(
							'type' => 'number',
						),
						'sectionHeadingFontSizeMobile'     => array(
							'type' => 'number',
						),
						// section heading line height.
						'sectionHeadingLineHeightType'     => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'sectionHeadingLineHeight'         => array(
							'type' => 'number',
						),
						'sectionHeadingLineHeightTablet'   => array(
							'type' => 'number',
						),
						'sectionHeadingLineHeightMobile'   => array(
							'type' => 'number',
						),
						'sectionContentColor'              => array(
							'type' => 'string',
						),
						// section content font family.
						'sectionContentLoadGoogleFonts'    => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'sectionContentFontFamily'         => array(
							'type' => 'string',
						),
						'sectionContentFontWeight'         => array(
							'type' => 'string',
						),
						'sectionContentFontSubset'         => array(
							'type' => 'string',
						),
						// section content font size.
						'sectionContentFontSize'           => array(
							'type' => 'number',
						),
						'sectionContentFontSizeType'       => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'sectionContentFontSizeTablet'     => array(
							'type' => 'number',
						),
						'sectionContentFontSizeMobile'     => array(
							'type' => 'number',
						),
						// section content line height.
						'sectionContentLineHeightType'     => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'sectionContentLineHeight'         => array(
							'type' => 'number',
						),
						'sectionContentLineHeightTablet'   => array(
							'type' => 'number',
						),
						'sectionContentLineHeightMobile'   => array(
							'type' => 'number',
						),
						'sectionBackgroundColor'           => array(
							'type' => 'string',
						),
						// Order Overview.
						'orderOverviewTextColor'           => array(
							'type' => 'string',
						),
						'orderOverviewBackgroundColor'     => array(
							'type' => 'string',
						),
						// order overview font family.
						'orderOverviewLoadGoogleFonts'     => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'orderOverviewFontFamily'          => array(
							'type' => 'string',
						),
						'orderOverviewFontWeight'          => array(
							'type' => 'string',
						),
						'orderOverviewFontSubset'          => array(
							'type' => 'string',
						),
						// order overview font size.
						'orderOverviewFontSize'            => array(
							'type' => 'number',
						),
						'orderOverviewFontSizeType'        => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'orderOverviewFontSizeTablet'      => array(
							'type' => 'number',
						),
						'orderOverviewFontSizeMobile'      => array(
							'type' => 'number',
						),
						// order overview line height.
						'orderOverviewLineHeightType'      => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'orderOverviewLineHeight'          => array(
							'type' => 'number',
						),
						'orderOverviewLineHeightTablet'    => array(
							'type' => 'number',
						),
						'orderOverviewLineHeightMobile'    => array(
							'type' => 'number',
						),
						// Downloads.
						'downloadHeadingColor'             => array(
							'type' => 'string',
						),
						// download heading font family.
						'downloadHeadingLoadGoogleFonts'   => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'downloadHeadingFontFamily'        => array(
							'type' => 'string',
						),
						'downloadHeadingFontWeight'        => array(
							'type' => 'string',
						),
						'downloadHeadingFontSubset'        => array(
							'type' => 'string',
						),
						// download heading font size.
						'downloadHeadingFontSize'          => array(
							'type' => 'number',
						),
						'downloadHeadingFontSizeType'      => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'downloadHeadingFontSizeTablet'    => array(
							'type' => 'number',
						),
						'downloadHeadingFontSizeMobile'    => array(
							'type' => 'number',
						),
						// download heading line height.
						'downloadHeadingLineHeightType'    => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'downloadHeadingLineHeight'        => array(
							'type' => 'number',
						),
						'downloadHeadingLineHeightTablet'  => array(
							'type' => 'number',
						),
						'downloadHeadingLineHeightMobile'  => array(
							'type' => 'number',
						),
						'downloadContentColor'             => array(
							'type' => 'string',
						),
						// download content font family.
						'downloadContentLoadGoogleFonts'   => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'downloadContentFontFamily'        => array(
							'type' => 'string',
						),
						'downloadContentFontWeight'        => array(
							'type' => 'string',
						),
						'downloadContentFontSubset'        => array(
							'type' => 'string',
						),
						// download content font size.
						'downloadContentFontSize'          => array(
							'type' => 'number',
						),
						'downloadContentFontSizeType'      => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'downloadContentFontSizeTablet'    => array(
							'type' => 'number',
						),
						'downloadContentFontSizeMobile'    => array(
							'type' => 'number',
						),
						// download content line height.
						'downloadContentLineHeightType'    => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'downloadContentLineHeight'        => array(
							'type' => 'number',
						),
						'downloadContentLineHeightTablet'  => array(
							'type' => 'number',
						),
						'downloadContentLineHeightMobile'  => array(
							'type' => 'number',
						),
						'downloadBackgroundColor'          => array(
							'type' => 'string',
						),
						// Order Details.
						'orderDetailHeadingColor'          => array(
							'type' => 'string',
						),
						// order details heading font family.
						'orderDetailHeadingLoadGoogleFonts' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'orderDetailHeadingFontFamily'     => array(
							'type' => 'string',
						),
						'orderDetailHeadingFontWeight'     => array(
							'type' => 'string',
						),
						'orderDetailHeadingFontSubset'     => array(
							'type' => 'string',
						),
						// order details heading font size.
						'orderDetailHeadingFontSize'       => array(
							'type' => 'number',
						),
						'orderDetailHeadingFontSizeType'   => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'orderDetailHeadingFontSizeTablet' => array(
							'type' => 'number',
						),
						'orderDetailHeadingFontSizeMobile' => array(
							'type' => 'number',
						),
						// order details heading line height.
						'orderDetailHeadingLineHeightType' => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'orderDetailHeadingLineHeight'     => array(
							'type' => 'number',
						),
						'orderDetailHeadingLineHeightTablet' => array(
							'type' => 'number',
						),
						'orderDetailHeadingLineHeightMobile' => array(
							'type' => 'number',
						),
						'orderDetailContentColor'          => array(
							'type' => 'string',
						),
						// order details content font family.
						'orderDetailContentLoadGoogleFonts' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'orderDetailContentFontFamily'     => array(
							'type' => 'string',
						),
						'orderDetailContentFontWeight'     => array(
							'type' => 'string',
						),
						'orderDetailContentFontSubset'     => array(
							'type' => 'string',
						),
						// order details content font size.
						'orderDetailContentFontSize'       => array(
							'type' => 'number',
						),
						'orderDetailContentFontSizeType'   => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'orderDetailContentFontSizeTablet' => array(
							'type' => 'number',
						),
						'orderDetailContentFontSizeMobile' => array(
							'type' => 'number',
						),
						// order details content line height.
						'orderDetailContentLineHeightType' => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'orderDetailContentLineHeight'     => array(
							'type' => 'number',
						),
						'orderDetailContentLineHeightTablet' => array(
							'type' => 'number',
						),
						'orderDetailContentLineHeightMobile' => array(
							'type' => 'number',
						),
						'orderDetailBackgroundColor'       => array(
							'type' => 'string',
						),
						// Customer Details.
						'customerDetailHeadingColor'       => array(
							'type' => 'string',
						),
						// customer details heading font family.
						'customerDetailHeadingLoadGoogleFonts' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'customerDetailHeadingFontFamily'  => array(
							'type' => 'string',
						),
						'customerDetailHeadingFontWeight'  => array(
							'type' => 'string',
						),
						'customerDetailHeadingFontSubset'  => array(
							'type' => 'string',
						),
						// customer details heading font size.
						'customerDetailHeadingFontSize'    => array(
							'type' => 'number',
						),
						'customerDetailHeadingFontSizeType' => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'customerDetailHeadingFontSizeTablet' => array(
							'type' => 'number',
						),
						'customerDetailHeadingFontSizeMobile' => array(
							'type' => 'number',
						),
						// customer details heading line height.
						'customerDetailHeadingLineHeightType' => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'customerDetailHeadingLineHeight'  => array(
							'type' => 'number',
						),
						'customerDetailHeadingLineHeightTablet' => array(
							'type' => 'number',
						),
						'customerDetailHeadingLineHeightMobile' => array(
							'type' => 'number',
						),
						'customerDetailContentColor'       => array(
							'type' => 'string',
						),
						// customer details content font family.
						'customerDetailContentLoadGoogleFonts' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'customerDetailContentFontFamily'  => array(
							'type' => 'string',
						),
						'customerDetailContentFontWeight'  => array(
							'type' => 'string',
						),
						'customerDetailContentFontSubset'  => array(
							'type' => 'string',
						),
						// customer details content font size.
						'customerDetailContentFontSize'    => array(
							'type' => 'number',
						),
						'customerDetailContentFontSizeType' => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'customerDetailContentFontSizeTablet' => array(
							'type' => 'number',
						),
						'customerDetailContentFontSizeMobile' => array(
							'type' => 'number',
						),
						// customer details content line height.
						'customerDetailContentLineHeightType' => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'customerDetailContentLineHeight'  => array(
							'type' => 'number',
						),
						'customerDetailContentLineHeightTablet' => array(
							'type' => 'number',
						),
						'customerDetailContentLineHeightMobile' => array(
							'type' => 'number',
						),
						'customerDetailBackgroundColor'    => array(
							'type' => 'string',
						),
						'backgroundType'                   => array(
							'type'    => 'string',
							'default' => 'color',
						),
						'backgroundImage'                  => array(
							'type' => 'object',
						),
						'backgroundPosition'               => array(
							'type'    => 'string',
							'default' => 'center-center',
						),
						'backgroundSize'                   => array(
							'type'    => 'string',
							'default' => 'cover',
						),
						'backgroundRepeat'                 => array(
							'type'    => 'string',
							'default' => 'no-repeat',
						),
						'backgroundAttachment'             => array(
							'type'    => 'string',
							'default' => 'scroll',
						),
						'backgroundOpacity'                => array(
							'type' => 'number',
						),
						'backgroundImageColor'             => array(
							'type'    => 'string',
							'default' => '',
						),
						'backgroundColor'                  => array(
							'type'    => 'string',
							'default' => '',
						),
						'odbackgroundType'                 => array(
							'type'    => 'string',
							'default' => 'color',
						),
						'odbackgroundImage'                => array(
							'type' => 'object',
						),
						'odbackgroundPosition'             => array(
							'type'    => 'string',
							'default' => 'center-center',
						),
						'odbackgroundSize'                 => array(
							'type'    => 'string',
							'default' => 'cover',
						),
						'odbackgroundRepeat'               => array(
							'type'    => 'string',
							'default' => 'no-repeat',
						),
						'odbackgroundAttachment'           => array(
							'type'    => 'string',
							'default' => 'scroll',
						),
						'odbackgroundOpacity'              => array(
							'type' => 'number',
						),
						'odbackgroundImageColor'           => array(
							'type'    => 'string',
							'default' => '',
						),
						'odbackgroundColor'                => array(
							'type'    => 'string',
							'default' => '',
						),
						'dbackgroundType'                  => array(
							'type'    => 'string',
							'default' => 'color',
						),
						'dbackgroundImage'                 => array(
							'type' => 'object',
						),
						'dbackgroundPosition'              => array(
							'type'    => 'string',
							'default' => 'center-center',
						),
						'dbackgroundSize'                  => array(
							'type'    => 'string',
							'default' => 'cover',
						),
						'dbackgroundRepeat'                => array(
							'type'    => 'string',
							'default' => 'no-repeat',
						),
						'dbackgroundAttachment'            => array(
							'type'    => 'string',
							'default' => 'scroll',
						),
						'dbackgroundOpacity'               => array(
							'type' => 'number',
						),
						'dbackgroundImageColor'            => array(
							'type'    => 'string',
							'default' => '',
						),
						'dbackgroundColor'                 => array(
							'type'    => 'string',
							'default' => '',
						),
						'odetailbackgroundType'            => array(
							'type'    => 'string',
							'default' => 'color',
						),
						'odetailbackgroundImage'           => array(
							'type' => 'object',
						),
						'odetailbackgroundPosition'        => array(
							'type'    => 'string',
							'default' => 'center-center',
						),
						'odetailbackgroundSize'            => array(
							'type'    => 'string',
							'default' => 'cover',
						),
						'odetailbackgroundRepeat'          => array(
							'type'    => 'string',
							'default' => 'no-repeat',
						),
						'odetailbackgroundAttachment'      => array(
							'type'    => 'string',
							'default' => 'scroll',
						),
						'odetailbackgroundOpacity'         => array(
							'type' => 'number',
						),
						'odetailbackgroundImageColor'      => array(
							'type'    => 'string',
							'default' => '',
						),
						'odetailbackgroundColor'           => array(
							'type'    => 'string',
							'default' => '',
						),
						'cdetailbackgroundType'            => array(
							'type'    => 'string',
							'default' => 'color',
						),
						'cdetailbackgroundImage'           => array(
							'type' => 'object',
						),
						'cdetailbackgroundPosition'        => array(
							'type'    => 'string',
							'default' => 'center-center',
						),
						'cdetailbackgroundSize'            => array(
							'type'    => 'string',
							'default' => 'cover',
						),
						'cdetailbackgroundRepeat'          => array(
							'type'    => 'string',
							'default' => 'no-repeat',
						),
						'cdetailbackgroundAttachment'      => array(
							'type'    => 'string',
							'default' => 'scroll',
						),
						'cdetailbackgroundOpacity'         => array(
							'type' => 'number',
						),
						'cdetailbackgroundImageColor'      => array(
							'type'    => 'string',
							'default' => '',
						),
						'cdetailbackgroundColor'           => array(
							'type'    => 'string',
							'default' => '',
						),
						'deviceType'       => array(
							'type'    => 'string',
							'default' => 'Desktop',
						),

						// Global
						'gradientColor1'          => array(
							'type'    => 'string',
							'default' => '#abb8c3',
						),
						'gradientColor2'          => array(
							'type'    => 'string',
							'default' => '#abb8c3',
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


						// Order Overview
						'odgradientValue'           => array(
							'type'    => 'string',
							'default' => '',
						),

						// Download
						'dgradientValue'           => array(
							'type'    => 'string',
							'default' => '',
						),

						// order details
						'odetailgradientValue'           => array(
							'type'    => 'string',
							'default' => '',
						),

						// Customer details
						'cdetailgradientValue'           => array(
							'type'    => 'string',
							'default' => '',
						),

						'orderOverviewFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'orderOverviewFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'orderDetailHeadingFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'downloadHeadingFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'sectionHeadingFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'customerDetailHeadingFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'headingFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'orderDetailContentFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'sectionContentFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'downloadContentFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),
						'customerDetailContentFontStyle'=> array(
							'type'=> 'string',
							'default'=> '',
						),

					),
					'render_callback' => array( $this, 'render_html' ),
				)
			);

		}


		/**
		 * Render Order Detail Form HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.15
		 */
		public function render_html( $attributes ) {

			$advanced_classes = Cartflows_Gb_Helper::get_instance()->generate_advanced_setting_classes( $attributes );
			$zindex_wrap = $advanced_classes[ 'zindex_wrap'];

			$main_classes = array(
				'cf-block-' . $attributes['block_id'],
				$advanced_classes[ 'desktop_class'],
				$advanced_classes[ 'tab_class'],
				$advanced_classes[ 'mob_class'],
				$advanced_classes[ 'zindex_extention_enabled'] ? 'uag-blocks-common-selector' : '',
			);

			if ( isset( $attributes['className'] ) ) {
				$main_classes[] = $attributes['className'];
			}

			$classes = array(
				'wpcf__order-detail-form',
			);

			$this->dynamic_option_filters( $attributes );

			ob_start();

			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>" style="<?php echo esc_html( implode( '', $zindex_wrap ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php echo do_shortcode( '[cartflows_order_details]' ); ?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}

		/**
		 * Dynamic options of elementor and add filters.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.15
		 */
		public function dynamic_option_filters( $attributes ) {

			self::$settings = $attributes;

			if ( ! empty( self::$settings['thanyouText'] ) ) {

				add_filter(
					'cartflows_thankyou_meta_wcf-tq-text',
					function( $text ) {

						$text = self::$settings['thanyouText'];

						return $text;
					},
					10,
					1
				);
			}

		}

	}

	/**
	 *  Prepare if class 'WCFB_Order_Detail_Form' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	WCFB_Order_Detail_Form::get_instance();
}
