<?php
/**
 * Cartflows Config.
 *
 * @package Cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Block_Config' ) ) {

	/**
	 * Class Cartflows_Block_Config.
	 */
	class Cartflows_Block_Config {

		/**
		 * Block Attributes
		 *
		 * @var block_attributes
		 */
		public static $block_attributes = null;

		/**
		 * Block Assets
		 *
		 * @var block_attributes
		 */
		public static $block_assets = null;

		/**
		 * Get Widget List.
		 *
		 * @since 1.6.15
		 *
		 * @return array The Widget List.
		 */
		public static function get_block_attributes() {

			$btn_border_attribute = self::generate_border_attribute( 'btn' );

			$optin_field_border_attribute  = self::generate_border_attribute( 'inputField' );
			$optin_button_border_attribute = self::generate_border_attribute( 'submitButton' );

			$checkout_field_border_attribute  = self::generate_border_attribute( 'field' );
			$checkout_button_border_attribute = self::generate_border_attribute( 'button' );

			if ( null === self::$block_attributes ) {
				self::$block_attributes = array(
					'wcfb/next-step-button'  => array(
						'slug'        => '',
						'title'       => __( 'Next Step Button', 'cartflows' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array_merge(
							$btn_border_attribute,
							array(
								'classMigrate'             => false,
								'borderStyle'              => 'none',
								'align'                    => 'center',
								'malign'                   => 'center',
								'talign'                   => 'center',
								'titletextTransform'       => 'none',
								'subtitletextTransform'    => 'none',
								'borderWidth'              => '',
								'borderColor'              => '',
								'borderHoverColor'         => '',
								'borderRadius'             => '',
								'buttonColor'              => '',
								'buttonHoverColor'         => '',
								'paddingTypeDesktop'       => 'px',
								'paddingTypeTablet'        => 'px',
								'paddingTypeMobile'        => 'px',
								'vPaddingDesktop'          => '5',
								'hPaddingDesktop'          => '30',
								'vPaddingTablet'           => '5',
								'hPaddingTablet'           => '30',
								'vPaddingMobile'           => '5',
								'hPaddingMobile'           => '30',
								'textAlignment'            => 'center',
								'textColor'                => '#ffffff',
								'textHoverColor'           => '#ffffff',
								'titleFontFamily'          => '',
								'titleFontSize'            => '20',
								'titleFontWeight'          => '500',
								'titleFontSizeType'        => 'px',
								'titleFontSizeTablet'      => '',
								'titleFontSizeMobile'      => '',
								'titleLineHeightType'      => 'em',
								'titleLineHeight'          => '',
								'titleLineHeightTablet'    => '',
								'titleLineHeightMobile'    => '',
								'subTitleFontFamily'       => '',
								'subTitleFontWeight'       => '500',
								'subTitleFontSize'         => '20',
								'subTitleFontSizeType'     => 'px',
								'subTitleFontSizeTablet'   => '',
								'subTitleFontSizeMobile'   => '',
								'subTitleLineHeightType'   => 'em',
								'subTitleLineHeight'       => '',
								'subTitleLineHeightTablet' => '',
								'subTitleLineHeightMobile' => '',
								'titleletterSpacing'       => '',
								'titleLetterSpacingTablet' => '',
								'titleLetterSpacingMobile' => '',
								'titleLetterSpacingType'   => 'px',
								'subtitleletterSpacing'    => '',
								'subtitleLetterSpacingTablet' => '',
								'subtitleLetterSpacingMobile' => '',
								'subtitleLetterSpacingType' => 'px',
								'titleBottomSpacing'       => '0',
								'iconSize'                 => '20',
								'iconSpacing'              => '10',
								'iconColor'                => '',
								'iconHoverColor'           => '',
								'iconPosition'             => 'before_title',
								'backgroundType'           => 'none',
								'backgroundImage'          => '',
								'backgroundPosition'       => 'center-center',
								'backgroundSize'           => 'cover',
								'backgroundRepeat'         => 'no-repeat',
								'backgroundAttachment'     => 'scroll',
								'backgroundColor'          => '#abb8c3',
								'gradientColor1'           => '#abb8c3',
								'gradientColor2'           => '#abb8c3',
								'gradientType'             => 'linear',
								'gradientLocation1'        => '0',
								'gradientLocation2'        => '100',
								'gradientAngle'            => '0',
								'gradientPosition'         => 'center center',
								'backgroundOpacity'        => 100,
								'backgroundImageColor'     => '#abb8c3',
								'gradientValue'            => '',
								// new attr.
								'paddingBtnTop'            => 5,
								'paddingBtnBottom'         => 5,
								'paddingBtnLeft'           => 30,
								'paddingBtnRight'          => 30,
								'paddingBtnTopTablet'      => '',
								'paddingBtnRightTablet'    => '',
								'paddingBtnBottomTablet'   => '',
								'paddingBtnLeftTablet'     => '',
								'paddingBtnTopMobile'      => '',
								'paddingBtnRightMobile'    => '',
								'paddingBtnBottomMobile'   => '',
								'paddingBtnLeftMobile'     => '',
								'titleFontFamily'          => 'normal',
								'subTitleFontFamily'       => 'normal',
							)
						),
					),

					'wcfb/order-detail-form' => array(
						'slug'        => '',
						'title'       => __( 'Order Details Form', 'cartflows' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'                 => false,
							'align'                        => 'center',
							// Genaral.
							'orderOverview'                => true,
							'orderDetails'                 => true,
							'billingAddress'               => true,
							'shippingAddress'              => true,
							// Spacing.
							'headingBottomSpacing'         => '',
							'sectionSpacing'               => '',
							// Heading.
							'thanyouText'                  => 'center',
							'headingAlignment'             => 'center',
							'headingColor'                 => '',
							'headingFontFamily'            => '',
							'headingFontWeight'            => '',
							'headingFontSize'              => '',
							'headingFontSizeType'          => 'px',
							'headingFontSizeTablet'        => '',
							'headingFontSizeMobile'        => '',
							'headingLineHeightType'        => 'em',
							'headingLineHeight'            => '',
							'headingLineHeightTablet'      => '',
							'headingLineHeightMobile'      => '',
							// Sections.
							'sectionHeadingColor'          => '',
							'sectionHeadingFontFamily'     => '',
							'sectionHeadingFontWeight'     => '',
							'sectionHeadingFontSize'       => '',
							'sectionHeadingFontSizeType'   => 'px',
							'sectionHeadingFontSizeTablet' => '',
							'sectionHeadingFontSizeMobile' => '',
							'sectionHeadingLineHeightType' => '',
							'sectionHeadingLineHeight'     => 'em',
							'sectionHeadingLineHeightTablet' => '',
							'sectionHeadingLineHeightMobile' => '',
							'sectionContentColor'          => '',
							'sectionContentFontFamily'     => '',
							'sectionContentFontWeight'     => '',
							'sectionContentFontSize'       => '',
							'sectionContentFontSizeType'   => 'px',
							'sectionContentFontSizeTablet' => '',
							'sectionContentFontSizeMobile' => '',
							'sectionContentLineHeightType' => 'em',
							'sectionContentLineHeight'     => '',
							'sectionContentLineHeightTablet' => '',
							'sectionContentLineHeightMobile' => '',
							'sectionBackgroundColor'       => '',
							// Order Overview.
							'orderOverviewTextColor'       => '',
							'orderOverviewBackgroundColor' => '',
							'orderOverviewFontFamily'      => '',
							'orderOverviewFontWeight'      => '',
							'orderOverviewFontSize'        => '',
							'orderOverviewFontSizeType'    => 'px',
							'orderOverviewFontSizeTablet'  => '',
							'orderOverviewFontSizeMobile'  => '',
							'orderOverviewLineHeightType'  => 'em',
							'orderOverviewLineHeight'      => '',
							'orderOverviewLineHeightTablet' => '',
							'orderOverviewLineHeightMobile' => '',
							// Downloads.
							'downloadHeadingColor'         => '',
							'downloadHeadingFontFamily'    => '',
							'downloadHeadingFontWeight'    => '',
							'downloadHeadingFontSize'      => '',
							'downloadHeadingFontSizeType'  => 'px',
							'downloadHeadingFontSizeTablet' => '',
							'downloadHeadingFontSizeMobile' => '',
							'downloadHeadingLineHeightType' => 'em',
							'downloadHeadingLineHeight'    => '',
							'downloadHeadingLineHeightTablet' => '',
							'downloadHeadingLineHeightMobile' => '',
							'downloadContentColor'         => '',
							'downloadContentFontFamily'    => '',
							'downloadContentFontWeight'    => '',
							'downloadContentFontSize'      => '',
							'downloadContentFontSizeType'  => 'px',
							'downloadContentFontSizeTablet' => '',
							'downloadContentFontSizeMobile' => '',
							'downloadContentLineHeightType' => 'em',
							'downloadContentLineHeight'    => '',
							'downloadContentLineHeightTablet' => '',
							'downloadContentLineHeightMobile' => '',
							'downloadBackgroundColor'      => '',
							// Order Details.
							'orderDetailHeadingColor'      => '',
							'orderDetailHeadingFontFamily' => '',
							'orderDetailHeadingFontWeight' => '',
							'orderDetailHeadingFontSize'   => '',
							'orderDetailHeadingFontSizeType' => 'px',
							'orderDetailHeadingFontSizeTablet' => '',
							'orderDetailHeadingFontSizeMobile' => '',
							'orderDetailHeadingLineHeightType' => 'em',
							'orderDetailHeadingLineHeight' => '',
							'orderDetailHeadingLineHeightTablet' => '',
							'orderDetailHeadingLineHeightMobile' => '',
							'orderDetailContentColor'      => '',
							'orderDetailContentFontFamily' => '',
							'orderDetailContentFontWeight' => '',
							'orderDetailContentFontSize'   => '',
							'orderDetailContentFontSizeType' => 'px',
							'orderDetailContentFontSizeTablet' => '',
							'orderDetailContentFontSizeMobile' => '',
							'orderDetailContentLineHeightType' => 'em',
							'orderDetailContentLineHeight' => '',
							'orderDetailContentLineHeightTablet' => '',
							'orderDetailContentLineHeightMobile' => '',
							'orderDetailBackgroundColor'   => '',
							// Customer Details.
							'customerDetailHeadingColor'   => '',
							'customerDetailHeadingFontFamily' => '',
							'customerDetailHeadingFontWeight' => '',
							'customerDetailHeadingFontSize' => '',
							'customerDetailHeadingFontSizeType' => 'px',
							'customerDetailHeadingFontSizeTablet' => '',
							'customerDetailHeadingFontSizeMobile' => '',
							'customerDetailHeadingLineHeightType' => 'em',
							'customerDetailHeadingLineHeight' => '',
							'customerDetailHeadingLineHeightTablet' => '',
							'customerDetailHeadingLineHeightMobile' => '',
							'customerDetailContentColor'   => '',
							'customerDetailContentFontFamily' => '',
							'customerDetailContentFontWeight' => '',
							'customerDetailContentFontSize' => '',
							'customerDetailContentFontSizeType' => 'px',
							'customerDetailContentFontSizeTablet' => '',
							'customerDetailContentFontSizeMobile' => '',
							'customerDetailContentLineHeightType' => 'em',
							'customerDetailContentLineHeight' => '',
							'customerDetailContentLineHeightTablet' => '',
							'customerDetailContentLineHeightMobile' => '',
							'customerDetailBackgroundColor' => '',
							'backgroundType'               => 'none',
							'backgroundImage'              => '',
							'backgroundPosition'           => 'center-center',
							'backgroundSize'               => 'cover',
							'backgroundRepeat'             => 'no-repeat',
							'backgroundAttachment'         => 'scroll',
							'backgroundColor'              => '',
							'backgroundOpacity'            => 100,
							'backgroundImageColor'         => '#abb8c3',
							'odbackgroundType'             => 'none',
							'odbackgroundImage'            => '',
							'odbackgroundPosition'         => 'center-center',
							'odbackgroundSize'             => 'cover',
							'odbackgroundRepeat'           => 'no-repeat',
							'odbackgroundAttachment'       => 'scroll',
							'odbackgroundColor'            => '',
							'odbackgroundOpacity'          => 100,
							'odbackgroundImageColor'       => '#abb8c3',
							'dbackgroundType'              => 'none',
							'dbackgroundImage'             => '',
							'dbackgroundPosition'          => 'center-center',
							'dbackgroundSize'              => 'cover',
							'dbackgroundRepeat'            => 'no-repeat',
							'dbackgroundAttachment'        => 'scroll',
							'dbackgroundColor'             => '',
							'dbackgroundOpacity'           => 100,
							'dbackgroundImageColor'        => '#abb8c3',
							'odetailbackgroundType'        => 'none',
							'odetailbackgroundImage'       => '',
							'odetailbackgroundPosition'    => 'center-center',
							'odetailbackgroundSize'        => 'cover',
							'odetailbackgroundRepeat'      => 'no-repeat',
							'odetailbackgroundAttachment'  => 'scroll',
							'odetailbackgroundColor'       => '',
							'odetailbackgroundOpacity'     => 100,
							'odetailbackgroundImageColor'  => '#abb8c3',
							'cdetailbackgroundType'        => 'none',
							'cdetailbackgroundImage'       => '',
							'cdetailbackgroundPosition'    => 'center-center',
							'cdetailbackgroundSize'        => 'cover',
							'cdetailbackgroundRepeat'      => 'no-repeat',
							'cdetailbackgroundAttachment'  => 'scroll',
							'cdetailbackgroundColor'       => '',
							'cdetailsbackgroundOpacity'    => 100,
							'cdetailsbackgroundImageColor' => '#abb8c3',

							// New attr.
							'gradientColor1'               => '#abb8c3',
							'gradientColor2'               => '#abb8c3',
							'gradientType'                 => 'linear',
							'gradientLocation1'            => 0,
							'gradientLocation2'            => 100,
							'gradientAngle'                => 0,
							'gradientPosition'             => 'center center',
							'gradientValue'                => '',

							'odgradientValue'              => '',
							// Download.
							'dgradientValue'               => '',
							// order details.
							'odetailgradientValue'         => '',
							// Customer details.
							'cdetailgradientValue'         => '',

							'orderOverviewFontStyle'       => '',
							'orderDetailHeadingFontStyle'  => '',
							'downloadHeadingFontStyle'     => '',
							'sectionHeadingFontStyle'      => '',
							'customerDetailHeadingFontStyle' => '',
							'headingFontStyle'             => '',
							'orderDetailContentFontStyle'  => '',
							'sectionContentFontStyle'      => '',
							'downloadContentFontStyle'     => '',
							'customerDetailContentFontStyle' => '',
						),
					),

					'wcfb/checkout-form'     => array(
						'slug'        => '',
						'title'       => __( 'Checkout Form', 'cartflows' ),
						'description' => '',
						'default'     => true,
						'is_active'   => class_exists( 'Cartflows_Checkout_Markup' ),
						'attributes'  => array_merge(
							$checkout_field_border_attribute,
							$checkout_button_border_attribute,
							array(
								'block_id'                 => '',
								'boxShadowColor'           => '',
								'boxShadowHOffset'         => 0,
								'boxShadowVOffset'         => 0,
								'boxShadowBlur'            => 0,
								'boxShadowSpread'          => 0,
								'boxShadowPosition'        => 'outset',
								'headBgColor'              => '',
								'fieldHrPadding'           => '',
								'fieldVrPadding'           => '',
								'fieldBgColor'             => '',
								'fieldLabelColor'          => '',
								'fieldInputColor'          => '',
								'fieldBorderStyle'         => 'solid',
								'fieldBorderWidth'         => '',
								'fieldBorderRadius'        => '',
								'fieldBorderColor'         => '',
								'fieldBorderFocusColor'    => '',
								'buttonAlignment'          => 'left',
								'buttonVrPadding'          => '',
								'buttonHrPadding'          => '',
								'buttonTextColor'          => '',
								'buttonBgColor'            => '',
								'buttonTextHoverColor'     => '',
								'buttonBgHoverColor'       => '',
								'buttonBorderStyle'        => 'inherit',
								'buttonBorderWidth'        => '',
								'buttonBorderRadius'       => '',
								'buttonBorderColor'        => '',
								'buttonBorderHoverColor'   => '',
								'fieldSpacing'             => '',
								'fieldLabelSpacing'        => '',
								'inputFontSize'            => '',
								'inputFontSizeType'        => 'px',
								'inputFontSizeTablet'      => '',
								'inputFontSizeMobile'      => '',
								'inputFontFamily'          => 'Default',
								'inputFontWeight'          => '',
								'inputFontSubset'          => '',
								'inputLineHeightType'      => 'px',
								'inputLineHeight'          => '',
								'inputLineHeightTablet'    => '',
								'inputLineHeightMobile'    => '',
								'inputLoadGoogleFonts'     => false,
								'submitButtonText'         => '',
								'buttonFontSize'           => '',
								'buttonFontSizeType'       => 'px',
								'buttonFontSizeTablet'     => '',
								'buttonFontSizeMobile'     => '',
								'buttonFontFamily'         => 'Default',
								'buttonFontWeight'         => '',
								'buttonFontSubset'         => '',
								'buttonLineHeightType'     => 'px',
								'buttonLineHeight'         => '',
								'buttonLineHeightTablet'   => '',
								'buttonLineHeightMobile'   => '',
								'buttonLoadGoogleFonts'    => false,
								'errorMsgColor'            => '',
								'errorMsgBgColor'          => '',
								'errorMsgBorderColor'      => '',
								'msgBorderSize'            => '',
								'msgBorderRadius'          => '',
								'msgVrPadding'             => 10,
								'msgHrPadding'             => 10,
								'msgBorderRadiusType'      => 'px',
								'fieldBorderRadiusType'    => 'px',
								'buttonBorderRadiusType'   => 'px',
								'paymentdescriptionColor'  => '',
								'paymenttitleColor'        => '',
								'sectionbgColor'           => '',
								'informationbgColor'       => '',
								'sectionhrPadding'         => '',
								'sectionvrPadding'         => '',
								'sectionhrMargin'          => '',
								'sectionvrMargin'          => '',
								'sectionBorderRadius'      => '',
								'headFontSize'             => '',
								'headFontSizeType'         => 'px',
								'headFontSizeTablet'       => '',
								'headFontSizeMobile'       => '',
								'headFontFamily'           => 'Default',
								'headFontWeight'           => '',
								'headFontSubset'           => '',
								'headLineHeightType'       => 'px',
								'headLineHeight'           => '',
								'headLineHeightTablet'     => '',
								'headLineHeightMobile'     => '',
								'headLoadGoogleFonts'      => '',
								'globaltextColor'          => '',
								'globalbgColor'            => '',
								'globalFontSize'           => '',
								'globalFontSizeType'       => 'px',
								'globalFontSizeTablet'     => '',
								'globalFontSizeMobile'     => '',
								'globalFontFamily'         => 'Default',
								'globalFontWeight'         => '',
								'globalFontSubset'         => '',
								'globalLineHeightType'     => 'px',
								'globalLineHeight'         => '',
								'globalLineHeightTablet'   => '',
								'globalLineHeightMobile'   => '',
								'globalLoadGoogleFonts'    => false,
								'backgroundType'           => 'color',
								'backgroundImage'          => '',
								'backgroundPosition'       => 'center-center',
								'backgroundSize'           => 'cover',
								'backgroundRepeat'         => 'no-repeat',
								'backgroundAttachment'     => 'scroll',
								'backgroundColor'          => '',
								'backgroundHoverColor'     => '',
								'gradientColor1'           => '#abb8c3',
								'gradientColor2'           => '#abb8c3',
								'gradientType'             => 'linear',
								'gradientLocation1'        => '0',
								'gradientLocation2'        => '100',
								'gradientAngle'            => '0',
								'gradientPosition'         => 'center center',
								'backgroundOpacity'        => 100,
								'backgroundImageColor'     => '#abb8c3',
								'gradientValue'            => '',
								'errorLabelColor'          => '',
								'errorFieldBorderColor'    => '#e2401c',
								'orderReviewColumnColor'   => '#ffffff',
								'orderReviewColumnTextColor' => '#555',

								// New attrs.
								'paymentSectionpaddingTop' => '',
								'paymentSectionpaddingBottom' => '',
								'paymentSectionpaddingLeft' => '',
								'paymentSectionpaddingRight' => '',
								'paymentSectionpaddingTopTablet' => '',
								'paymentSectionpaddingRightTablet' => '',
								'paymentSectionpaddingBottomTablet' => '',
								'paymentSectionpaddingLeftTablet' => '',
								'paymentSectionpaddingTopMobile' => '',
								'paymentSectionpaddingRightMobile' => '',
								'paymentSectionpaddingBottomMobile' => '',
								'paymentSectionpaddingLeftMobile' => '',

								'paymentSectionpaddingTypeDesktop' => 'px',
								'paymentSectionpaddingTypeTablet' => 'px',
								'paymentSectionpaddingTypeMobile' => 'px',

								'paymentSectionMarginTop'  => '',
								'paymentSectionMarginBottom' => '',
								'paymentSectionMarginLeft' => '',
								'paymentSectionMarginRight' => '',
								'paymentSectionMarginTopTablet' => '',
								'paymentSectionMarginRightTablet' => '',
								'paymentSectionMarginBottomTablet' => '',
								'paymentSectionMarginLeftTablet' => '',
								'paymentSectionMarginTopMobile' => '',
								'paymentSectionMarginRightMobile' => '',
								'paymentSectionMarginBottomMobile' => '',
								'paymentSectionMarginLeftMobile' => '',

								'paymentSectionMarginTypeDesktop' => 'px',
								'paymentSectionMarginTypeTablet' => 'px',
								'paymentSectionMarginTypeMobile' => 'px',
								'spacingLink'              => '',

								'fieldBorderHoverColor'    => '',

								'buttonFontStyle'          => '',
								'inputFontStyle'           => '',
								'globalFontStyle'          => '',
								'headFontStyle'            => '',

								'buttonTransform'          => '',
								'buttonLetterSpacing'      => '',
								'buttonLetterSpacingTablet' => '',
								'buttonLetterSpacingMobile' => '',
								'buttonLetterSpacingType'  => 'px',

								'inputTransform'           => '',
								'inputLetterSpacing'       => '',
								'inputLetterSpacingTablet' => '',
								'inputLetterSpacingMobile' => '',
								'inputLetterSpacingType'   => 'px',

								'headTransform'            => '',
								'headLetterSpacing'        => '',
								'headLetterSpacingTablet'  => '',
								'headLetterSpacingMobile'  => '',
								'headLetterSpacingType'    => 'px',

								'globalTransform'          => 'none',
								'globalLetterSpacing'      => '',
								'globalLetterSpacingTablet' => '',
								'globalLetterSpacingMobile' => '',
								'globalLetterSpacingType'  => 'px',
							)
						),
					),

					'wcfb/optin-form'        => array(
						'slug'        => '',
						'title'       => __( 'Optin Form', 'cartflows' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array_merge(
							$optin_field_border_attribute,
							$optin_button_border_attribute,
							array(
								'block_id'                 => '',
								'classMigrate'             => false,
								// General.
								'generalPrimaryColor'      => '',
								'generalFontFamily'        => '',
								'generalFontWeight'        => '',
								'generalFontSize'          => '',
								'generalFontSizeType'      => 'px',
								'generalFontSizeTablet'    => '',
								'generalFontSizeMobile'    => '',
								'generalLineHeightType'    => 'em',
								'generalLineHeight'        => '',
								'generalLineHeightTablet'  => '',
								'generalLineHeightMobile'  => '',
								'generalTransform'         => 'none',
								'generalLetterSpacing'     => '',
								'generalLetterSpacingTablet' => '',
								'generalLetterSpacingMobile' => '',
								'generalLetterSpacingType' => 'px',
								// Input Fields.
								'inputFieldFontFamily'     => '',
								'inputFieldFontWeight'     => '',
								'inputFieldFontSize'       => '',
								'inputFieldFontSizeType'   => 'px',
								'inputFieldFontSizeTablet' => '',
								'inputFieldFontSizeMobile' => '',
								'inputFieldLineHeightType' => 'em',
								'inputFieldLineHeight'     => '',
								'inputFieldLineHeightTablet' => '',
								'inputFieldLineHeightMobile' => '',
								'inputFieldLabelColor'     => '',
								'inputFieldBackgroundColor' => '',
								'inputFieldTextPlaceholderColor' => '',
								'inputFieldBorderStyle'    => 'solid',
								'inputFieldBorderWidth'    => '',
								'inputFieldBorderRadius'   => '',
								'inputFieldBorderColor'    => '',
								'inputFieldBorderHoverColor' => '',
								'inputFieldTransform'      => '',
								'inputFieldLetterSpacing'  => '',
								'inputFieldLetterSpacingTablet' => '',
								'inputFieldLetterSpacingMobile' => '',
								'inputFieldLetterSpacingType' => 'px',
								// Submit Button.
								'submitButtonFontFamily'   => '',
								'submitButtonFontWeight'   => '',
								'submitButtonFontSize'     => '',
								'submitButtonFontSizeType' => 'px',
								'submitButtonFontSizeTablet' => '',
								'submitButtonFontSizeMobile' => '',
								'submitButtonLineHeightType' => 'em',
								'submitButtonLineHeight'   => '',
								'submitButtonLineHeightTablet' => '',
								'submitButtonLineHeightMobile' => '',
								'submitButtonTextColor'    => '',
								'submitButtonBackgroundColor' => '',
								'submitButtonTextHoverColor' => '',
								'submitButtonBackgroundHoverColor' => '',
								'submitButtonBorderStyle'  => '',
								'submitButtonBorderWidth'  => '',
								'submitButtonBorderRadius' => '',
								'submitButtonBorderColor'  => '',
								'submitButtonBorderHoverColor' => '',
								'submitButtonTransform'    => '',
								'submitButtonLetterSpacing' => '',
								'submitButtonLetterSpacingTablet' => '',
								'submitButtonLetterSpacingMobile' => '',
								'submitButtonLetterSpacingType' => 'px',
								'boxShadowColor'           => '',
								'boxShadowHOffset'         => 0,
								'boxShadowVOffset'         => 0,
								'boxShadowBlur'            => 0,
								'boxShadowSpread'          => 0,
								'boxShadowPosition'        => 'outset',
								'generalFontStyle'         => '',
								'submitButtonFontStyle'    => '',
								'inputFieldFontStyle'      => '',
							)
						),

					),

				);
			}
			return apply_filters( 'cartflows_gutenberg_blocks_attributes', self::$block_attributes );
		}

				/**
				 * Get Block Assets.
				 *
				 * @since 1.6.15
				 *
				 * @return array The Asset List.
				 */
		public static function get_block_assets() {

			if ( null === self::$block_assets ) {
				self::$block_assets = array();
			}
			return self::$block_assets;
		}

		/**
		 * Border attribute generation Function.
		 *
		 * @since 2.0.0-beta.3
		 * @param  array $prefix   Attribute Prefix.
		 * @param array $default_args  default attributes args.
		 * @return array
		 */
		public static function generate_border_attribute( $prefix, $default_args = array() ) {
			$defaults = wp_parse_args(
				$default_args,
				array(
					// Width.
					'borderTopWidth'                => '',
					'borderRightWidth'              => '',
					'borderBottomWidth'             => '',
					'borderLeftWidth'               => '',
					'borderTopWidthTablet'          => '',
					'borderRightWidthTablet'        => '',
					'borderBottomWidthTablet'       => '',
					'borderLeftWidthTablet'         => '',
					'borderTopWidthMobile'          => '',
					'borderRightWidthMobile'        => '',
					'borderBottomWidthMobile'       => '',
					'borderLeftWidthMobile'         => '',
					// Radius.
					'borderTopLeftRadius'           => '',
					'borderTopRightRadius'          => '',
					'borderBottomRightRadius'       => '',
					'borderBottomLeftRadius'        => '',
					'borderTopLeftRadiusTablet'     => '',
					'borderTopRightRadiusTablet'    => '',
					'borderBottomRightRadiusTablet' => '',
					'borderBottomLeftRadiusTablet'  => '',
					'borderTopLeftRadiusMobile'     => '',
					'borderTopRightRadiusMobile'    => '',
					'borderBottomRightRadiusMobile' => '',
					'borderBottomLeftRadiusMobile'  => '',
					// unit.
					'borderRadiusUnit'              => 'px',
					'borderRadiusUnitTablet'        => 'px',
					'borderRadiusUnitMobile'        => 'px',
					// common.
					'borderStyle'                   => 'none',
					'borderColor'                   => '',
					'borderHColor'                  => '',
				)
			);

			$border_attr = array();

			$device = array( '', 'Tablet', 'Mobile' );

			foreach ( $device as $slug => $data ) {

				$border_attr[ "{$prefix}BorderTopWidth{$data}" ]          = $defaults[ "borderTopWidth{$data}" ];
				$border_attr[ "{$prefix}BorderLeftWidth{$data}" ]         = $defaults[ "borderLeftWidth{$data}" ];
				$border_attr[ "{$prefix}BorderRightWidth{$data}" ]        = $defaults[ "borderRightWidth{$data}" ];
				$border_attr[ "{$prefix}BorderBottomWidth{$data}" ]       = $defaults[ "borderBottomWidth{$data}" ];
				$border_attr[ "{$prefix}BorderTopLeftRadius{$data}" ]     = $defaults[ "borderTopLeftRadius{$data}" ];
				$border_attr[ "{$prefix}BorderTopRightRadius{$data}" ]    = $defaults[ "borderTopRightRadius{$data}" ];
				$border_attr[ "{$prefix}BorderBottomLeftRadius{$data}" ]  = $defaults[ "borderBottomLeftRadius{$data}" ];
				$border_attr[ "{$prefix}BorderBottomRightRadius{$data}" ] = $defaults[ "borderBottomLeftRadius{$data}" ];
				$border_attr[ "{$prefix}BorderRadiusUnit{$data}" ]        = $defaults[ "borderRadiusUnit{$data}" ];
			}

			$border_attr[ "{$prefix}BorderStyle" ]  = $defaults['borderStyle'];
			$border_attr[ "{$prefix}BorderColor" ]  = $defaults['borderColor'];
			$border_attr[ "{$prefix}BorderHColor" ] = $defaults['borderHColor'];
			return $border_attr;
		}
	}
}
