<?php
/**
 * Dynamic checkout divi css
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$output .= "
	.et_pb_module #wcf-embed-checkout-form .woocommerce .woocommerce-checkout .product-name .remove:hover{
		color:$primary_color !important;
		border:1px solid $primary_color !important;
	}
	.et_pb_module #wcf-embed-checkout-form .wcf-checkout-header-image img{
		width: {$header_logo_width}px;
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment input[type=checkbox]:checked:before, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:checked:before{
	    color: {$primary_color} !important;
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment input[type=radio]:checked:before{
		background-color: {$primary_color};
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment input[type=checkbox]:focus, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:focus,
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment input[type=radio]:checked:focus,
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment input[type=radio]:not(:checked):focus{
		border-color: {$primary_color} !important;
		box-shadow: 0 0 2px rgba( " . $r . ',' . $g . ',' . $b . ", .8) !important;
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout label{
		color: {$field_label_color};
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box{
		background-color: {$hl_bg_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.et_pb_module #wcf-embed-checkout-form #add_payment_method #payment div.payment_box::before, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce-cart #payment div.payment_box::before, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box::before
	{
	    border-bottom-color: {$hl_bg_color};
	    border-right-color: transparent;
	    border-left-color: transparent;
	    border-top-color: transparent;
	    position: absolute;
	}

	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment [type='radio']:checked + label,
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment [type='radio']:not(:checked) + label{
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	
	.et_pb_module #wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type='text'],
	.et_pb_module #wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
	.et_pb_module #wcf-embed-checkout-form .woocommerce form .form-row textarea,
	.et_pb_module #wcf-embed-checkout-form .select2-container--default .select2-selection--single {
		color: {$field_color} !important;
		background-color: {$field_bg_color} !important;
		border-color: {$field_border_color} !important;
		padding-top: {$field_tb_padding}px;
		padding-bottom: {$field_tb_padding}px;
		padding-left: {$field_lr_padding}px;
		padding-right: {$field_lr_padding}px;
		min-height: {$field_input_size} !important;
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-field-style-one .woocommerce .form-row input[type='text'], 
	.et_pb_module #wcf-embed-checkout-form.wcf-field-style-one .woocommerce .form-row input[type='email'], 
	.et_pb_module #wcf-embed-checkout-form.wcf-field-style-one .woocommerce .form-row input[type='password'], 
	.et_pb_module #wcf-embed-checkout-form.wcf-field-style-one .woocommerce .form-row input[type='tel'], 
	.et_pb_module #wcf-embed-checkout-form.wcf-field-style-one .select2-container--default .select2-selection--single{
		padding:{$field_tb_padding}px {$field_lr_padding}px !important;
		
	}

	.et_pb_module #wcf-embed-checkout-form .woocommerce .col2-set .col-1, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce .col2-set .col-2,
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout .shop_table,
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout #order_review_heading,
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout #payment,
	.et_pb_module #wcf-embed-checkout-form .woocommerce form.checkout_coupon
	{
		background-color: {$section_bg_color};
		border-color: {$box_border_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.et_pb_module #wcf-embed-checkout-form .woocommerce table.shop_table th{
		color: {$field_label_color};
	}
	/*.et_pb_module #wcf-embed-checkout-form .woocommerce .woocommerce-info,
	.et_pb_module #wcf-embed-checkout-form .woocommerce-message{
		border-top-color: {$primary_color};
		background-color: {$hl_bg_color};
	}*/

	.et_pb_module #wcf-embed-checkout-form .woocommerce a:not(.wcf-next-button){
		color: {$primary_color} !important;
	}
	.et_pb_module #wcf-embed-checkout-form .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: {$field_color};
	}
	.et_pb_module #wcf-embed-checkout-form ::-webkit-input-placeholder { /* Chrome/Opera/Safari */
		color: {$field_color};
	}
	.et_pb_module #wcf-embed-checkout-form ::-moz-placeholder { /* Firefox 19+ */
		color: {$field_color};
	}
	.et_pb_module #wcf-embed-checkout-form :-ms-input-placeholder { /* IE 10+ */
		color: {$field_color};
	}
	.et_pb_module #wcf-embed-checkout-form :-moz-placeholder { /* Firefox 18- */
		color: {$field_color};
	}
	
	.et_pb_module #wcf-embed-checkout-form .woocommerce form p.form-row label {
		color: {$field_label_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce #order_review button,
	.et_pb_module #wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small {
		color: {$submit_color};
		background: {$submit_bg_color};
		padding-top: {$submit_tb_padding}px;
		padding-bottom: {$submit_tb_padding}px;
		padding-left: {$submit_lr_padding}px;
		padding-right: {$submit_lr_padding}px;
		border-color: {$submit_border_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button{
		background: {$submit_bg_color};
		border: 1px {$submit_border_color} solid;
		color: {$submit_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout form.login .button:hover, 
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button:hover,
	.et_pb_module #wcf-embed-checkout-form .woocommerce #payment #place_order:hover,
	.et_pb_module #wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small:hover{
		color: {$submit_hover_color};
		background-color: {$submit_bg_hover_color};
		border-color: {$submit_border_hover_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-customer-info-main-wrapper h3,
	.et_pb_module #wcf-embed-checkout-form .woocommerce h3,
	.et_pb_module #wcf-embed-checkout-form .woocommerce h3 span,
	.et_pb_module #wcf-embed-checkout-form .woocommerce-checkout #order_review_heading{
		color: {$section_heading_color};
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}
	.et_pb_module #wcf-embed-checkout-form .woocommerce-info::before,
	.et_pb_module #wcf-embed-checkout-form .woocommerce-message::before{
		color: {$primary_color};
	}
	.et_pb_module #wcf-embed-checkout-form {
	    font-family: {$base_font_family};
	}";

if ( 'modern-checkout' === $checkout_layout ) {
	$output .= "
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce-checkout table.shop_table {
			background-color: {$hl_bg_color} !important;
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce-checkout #payment div.payment_box{
			background-color: {$hl_bg_color} !important;
		}
		";
}

$output .= 'img.emoji, img.wp-smiley {}';
