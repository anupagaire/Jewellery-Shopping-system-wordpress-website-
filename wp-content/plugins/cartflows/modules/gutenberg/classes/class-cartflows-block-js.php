<?php
/**
 * Cartflows Block Js.
 *
 * @package Cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Block_JS' ) ) {

	/**
	 * Class Cartflows_Block_JS.
	 */
	class Cartflows_Block_JS {

		/**
		 * Adds Google fonts for Next Step Button.
		 *
		 * @since 1.6.15
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_next_step_button_gfont( $attr ) {

			$title_load_google_font = isset( $attr['titleLoadGoogleFonts'] ) ? $attr['titleLoadGoogleFonts'] : '';
			$title_font_family      = isset( $attr['titleFontFamily'] ) ? $attr['titleFontFamily'] : '';
			$title_font_weight      = isset( $attr['titleFontWeight'] ) ? $attr['titleFontWeight'] : '';
			$title_font_subset      = isset( $attr['titleFontSubset'] ) ? $attr['titleFontSubset'] : '';

			$sub_title_load_google_font = isset( $attr['subTitleLoadGoogleFonts'] ) ? $attr['subTitleLoadGoogleFonts'] : '';
			$sub_title_font_family      = isset( $attr['subTitleFontFamily'] ) ? $attr['subTitleFontFamily'] : '';
			$sub_title_font_weight      = isset( $attr['subTitleFontWeight'] ) ? $attr['subTitleFontWeight'] : '';
			$sub_title_font_subset      = isset( $attr['subTitleFontSubset'] ) ? $attr['subTitleFontSubset'] : '';

			Cartflows_Gb_Helper::blocks_google_font( $title_load_google_font, $title_font_family, $title_font_weight, $title_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $sub_title_load_google_font, $sub_title_font_family, $sub_title_font_weight, $sub_title_font_subset );
		}

		/**
		 * Adds Google fonts for Order Detail Form block.
		 *
		 * @since 1.6.15
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_order_detail_form_gfont( $attr ) {

			$heading_load_google_font = isset( $attr['headingLoadGoogleFonts'] ) ? $attr['headingLoadGoogleFonts'] : '';
			$heading_font_family      = isset( $attr['headingFontFamily'] ) ? $attr['headingFontFamily'] : '';
			$heading_font_weight      = isset( $attr['headingFontWeight'] ) ? $attr['headingFontWeight'] : '';
			$heading_font_subset      = isset( $attr['headingFontSubset'] ) ? $attr['headingFontSubset'] : '';

			$section_heading_load_google_font = isset( $attr['sectionHeadingLoadGoogleFonts'] ) ? $attr['sectionHeadingLoadGoogleFonts'] : '';
			$section_heading_font_family      = isset( $attr['sectionHeadingFontFamily'] ) ? $attr['sectionHeadingFontFamily'] : '';
			$section_heading_font_weight      = isset( $attr['sectionHeadingFontWeight'] ) ? $attr['sectionHeadingFontWeight'] : '';
			$section_heading_font_subset      = isset( $attr['sectionHeadingFontSubset'] ) ? $attr['sectionHeadingFontSubset'] : '';

			$section_content_load_google_font = isset( $attr['sectionContentLoadGoogleFonts'] ) ? $attr['sectionContentLoadGoogleFonts'] : '';
			$section_content_font_family      = isset( $attr['sectionContentFontFamily'] ) ? $attr['sectionContentFontFamily'] : '';
			$section_content_font_weight      = isset( $attr['sectionContentFontWeight'] ) ? $attr['sectionContentFontWeight'] : '';
			$section_content_font_subset      = isset( $attr['sectionContentFontSubset'] ) ? $attr['sectionContentFontSubset'] : '';

			$order_overview_load_google_font = isset( $attr['orderOverviewLoadGoogleFonts'] ) ? $attr['orderOverviewLoadGoogleFonts'] : '';
			$order_overview_font_family      = isset( $attr['orderOverviewFontFamily'] ) ? $attr['orderOverviewFontFamily'] : '';
			$order_overview_font_weight      = isset( $attr['orderOverviewFontWeight'] ) ? $attr['orderOverviewFontWeight'] : '';
			$order_overview_font_subset      = isset( $attr['orderOverviewFontSubset'] ) ? $attr['orderOverviewFontSubset'] : '';

			$download_heading_load_google_font = isset( $attr['downloadHeadingLoadGoogleFonts'] ) ? $attr['downloadHeadingLoadGoogleFonts'] : '';
			$download_heading_font_family      = isset( $attr['downloadHeadingFontFamily'] ) ? $attr['downloadHeadingFontFamily'] : '';
			$download_heading_font_weight      = isset( $attr['downloadHeadingFontWeight'] ) ? $attr['downloadHeadingFontWeight'] : '';
			$download_heading_font_subset      = isset( $attr['downloadHeadingFontSubset'] ) ? $attr['downloadHeadingFontSubset'] : '';

			$download_content_load_google_font = isset( $attr['downloadContentLoadGoogleFonts'] ) ? $attr['downloadContentLoadGoogleFonts'] : '';
			$download_content_font_family      = isset( $attr['downloadContentFontFamily'] ) ? $attr['downloadContentFontFamily'] : '';
			$download_content_font_weight      = isset( $attr['downloadContentFontWeight'] ) ? $attr['downloadContentFontWeight'] : '';
			$download_content_font_subset      = isset( $attr['downloadContentFontSubset'] ) ? $attr['downloadContentFontSubset'] : '';

			$order_detail_heading_load_google_font = isset( $attr['orderDetailHeadingLoadGoogleFonts'] ) ? $attr['orderDetailHeadingLoadGoogleFonts'] : '';
			$order_detail_heading_font_family      = isset( $attr['orderDetailHeadingFontFamily'] ) ? $attr['orderDetailHeadingFontFamily'] : '';
			$order_detail_heading_font_weight      = isset( $attr['orderDetailHeadingFontWeight'] ) ? $attr['orderDetailHeadingFontWeight'] : '';
			$order_detail_heading_font_subset      = isset( $attr['orderDetailHeadingFontSubset'] ) ? $attr['orderDetailHeadingFontSubset'] : '';

			$order_detail_content_load_google_font = isset( $attr['orderDetailContentLoadGoogleFonts'] ) ? $attr['orderDetailContentLoadGoogleFonts'] : '';
			$order_detail_content_font_family      = isset( $attr['orderDetailContentFontFamily'] ) ? $attr['orderDetailContentFontFamily'] : '';
			$order_detail_content_font_weight      = isset( $attr['orderDetailContentFontWeight'] ) ? $attr['orderDetailContentFontWeight'] : '';
			$order_detail_content_font_subset      = isset( $attr['orderDetailContentFontSubset'] ) ? $attr['orderDetailContentFontSubset'] : '';

			$customer_detail_heading_load_google_font = isset( $attr['customerDetailHeadingLoadGoogleFonts'] ) ? $attr['customerDetailHeadingLoadGoogleFonts'] : '';
			$customer_detail_heading_font_family      = isset( $attr['customerDetailHeadingFontFamily'] ) ? $attr['customerDetailHeadingFontFamily'] : '';
			$customer_detail_heading_font_weight      = isset( $attr['customerDetailHeadingFontWeight'] ) ? $attr['customerDetailHeadingFontWeight'] : '';
			$customer_detail_heading_font_subset      = isset( $attr['customerDetailHeadingFontSubset'] ) ? $attr['customerDetailHeadingFontSubset'] : '';

			$customer_detail_content_load_google_font = isset( $attr['customerDetailContentLoadGoogleFonts'] ) ? $attr['customerDetailContentLoadGoogleFonts'] : '';
			$customer_detail_content_font_family      = isset( $attr['customerDetailContentFontFamily'] ) ? $attr['customerDetailContentFontFamily'] : '';
			$customer_detail_content_font_weight      = isset( $attr['customerDetailContentFontWeight'] ) ? $attr['customerDetailContentFontWeight'] : '';
			$customer_detail_content_font_subset      = isset( $attr['customerDetailContentFontSubset'] ) ? $attr['customerDetailContentFontSubset'] : '';

			Cartflows_Gb_Helper::blocks_google_font( $heading_load_google_font, $heading_font_family, $heading_font_weight, $heading_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $section_heading_load_google_font, $section_heading_font_family, $section_heading_font_weight, $section_heading_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $section_content_load_google_font, $section_content_font_family, $section_content_font_weight, $section_content_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $order_overview_load_google_font, $order_overview_font_family, $order_overview_font_weight, $order_overview_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $download_heading_load_google_font, $download_heading_font_family, $download_heading_font_weight, $download_heading_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $download_content_load_google_font, $download_content_font_family, $download_content_font_weight, $download_content_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $order_detail_heading_load_google_font, $order_detail_heading_font_family, $order_detail_heading_font_weight, $order_detail_heading_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $order_detail_content_load_google_font, $order_detail_content_font_family, $order_detail_content_font_weight, $order_detail_content_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $customer_detail_heading_load_google_font, $customer_detail_heading_font_family, $customer_detail_heading_font_weight, $customer_detail_heading_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $customer_detail_content_load_google_font, $customer_detail_content_font_family, $customer_detail_content_font_weight, $customer_detail_content_font_subset );

		}

		/**
		 * Adds Google fonts for checkout form.
		 *
		 * @since 1.6.15
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_checkout_form_gfont( $attr ) {

			$head_load_google_font = isset( $attr['headLoadGoogleFonts'] ) ? $attr['headLoadGoogleFonts'] : '';
			$head_font_family      = isset( $attr['headFontFamily'] ) ? $attr['headFontFamily'] : '';
			$head_font_weight      = isset( $attr['headFontWeight'] ) ? $attr['headFontWeight'] : '';
			$head_font_subset      = isset( $attr['headFontSubset'] ) ? $attr['headFontSubset'] : '';

			$input_load_google_font = isset( $attr['inputLoadGoogleFonts'] ) ? $attr['inputLoadGoogleFonts'] : '';
			$input_font_family      = isset( $attr['inputFontFamily'] ) ? $attr['inputFontFamily'] : '';
			$input_font_weight      = isset( $attr['inputFontWeight'] ) ? $attr['inputFontWeight'] : '';
			$input_font_subset      = isset( $attr['inputFontSubset'] ) ? $attr['inputFontSubset'] : '';

			$button_load_google_font = isset( $attr['buttonLoadGoogleFonts'] ) ? $attr['buttonLoadGoogleFonts'] : '';
			$button_font_family      = isset( $attr['buttonFontFamily'] ) ? $attr['buttonFontFamily'] : '';
			$button_font_weight      = isset( $attr['buttonFontWeight'] ) ? $attr['buttonFontWeight'] : '';
			$button_font_subset      = isset( $attr['buttonFontSubset'] ) ? $attr['buttonFontSubset'] : '';

			$global_load_google_font = isset( $attr['globalLoadGoogleFonts'] ) ? $attr['globalLoadGoogleFonts'] : '';
			$global_font_family      = isset( $attr['globalFontFamily'] ) ? $attr['globalFontFamily'] : '';
			$global_font_weight      = isset( $attr['globalFontWeight'] ) ? $attr['globalFontWeight'] : '';
			$global_font_subset      = isset( $attr['globalFontSubset'] ) ? $attr['globalFontSubset'] : '';

			Cartflows_Gb_Helper::blocks_google_font( $global_load_google_font, $global_font_family, $global_font_weight, $global_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $head_load_google_font, $head_font_family, $head_font_weight, $head_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $button_load_google_font, $button_font_family, $button_font_weight, $button_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $input_load_google_font, $input_font_family, $input_font_weight, $input_font_subset );

		}

		/**
		 * Adds Google fonts for Optin Form block.
		 *
		 * @since 1.6.15
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_optin_form_gfont( $attr ) {

			$general_load_google_font = isset( $attr['generalLoadGoogleFonts'] ) ? $attr['generalLoadGoogleFonts'] : '';
			$general_font_family      = isset( $attr['generalFontFamily'] ) ? $attr['generalFontFamily'] : '';
			$general_font_weight      = isset( $attr['generalFontWeight'] ) ? $attr['generalFontWeight'] : '';
			$general_font_subset      = isset( $attr['generalFontSubset'] ) ? $attr['generalFontSubset'] : '';

			$submit_button_load_google_font = isset( $attr['submitButtonLoadGoogleFonts'] ) ? $attr['submitButtonLoadGoogleFonts'] : '';
			$submit_button_font_family      = isset( $attr['submitButtonFontFamily'] ) ? $attr['submitButtonFontFamily'] : '';
			$submit_button_font_weight      = isset( $attr['submitButtonFontWeight'] ) ? $attr['submitButtonFontWeight'] : '';
			$submit_button_font_subset      = isset( $attr['submitButtonFontSubset'] ) ? $attr['submitButtonFontSubset'] : '';

			Cartflows_Gb_Helper::blocks_google_font( $general_load_google_font, $general_font_family, $general_font_weight, $general_font_subset );
			Cartflows_Gb_Helper::blocks_google_font( $submit_button_load_google_font, $submit_button_font_family, $submit_button_font_weight, $submit_button_font_subset );

		}
	}
}
