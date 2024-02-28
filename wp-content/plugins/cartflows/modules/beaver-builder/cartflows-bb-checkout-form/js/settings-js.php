<?php
/**
 * Checkout Form Script for Beaver Builder.
 *
 * @package cartflows
 */

//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
?>
(function($) {

	var $wrapper = $('.fl-node-<?php echo $cf_module->node; ?> .cartflows-bb__checkout-form' );

	var $offer_wrap = $('body').find( '#wcf-pre-checkout-offer-modal' );

	var settings_data = $wrapper.data( 'settings-data' );

	var enable_product_options = settings_data.enable_product_options;

	var form = $('.fl-builder-settings');

	if( 'yes' === enable_product_options ) {

		form.find( "#fl-field-product_options_position .fl-field-label" ).show();
		form.find( "#fl-field-product_options_position select" ).show();
		form.find( "#fl-field-product_options_position .fl-field-description" ).hide();

		form.find( "#fl-field-product_options_skin" ).show();
		form.find( "#fl-field-product_options_images" ).show();
		form.find( "#fl-field-product_option_section_title_text" ).show();

		form.find('#fl-builder-settings-section-product_style').show();

	} else {

		form.find( "#fl-field-product_options_position .fl-field-label" ).hide();
		form.find( "#fl-field-product_options_position select" ).hide();
		form.find( "#fl-field-product_options_position .fl-field-description" ).show();

		form.find( "#fl-field-product_options_skin" ).hide();
		form.find( "#fl-field-product_options_images" ).hide();
		form.find( "#fl-field-product_option_section_title_text" ).hide();

		form.find('#fl-builder-settings-section-product_style').hide();
	}
})(jQuery);
