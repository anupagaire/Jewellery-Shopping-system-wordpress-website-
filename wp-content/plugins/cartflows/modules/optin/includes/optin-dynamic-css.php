<?php
/**
 * Dynamic checkout css
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$output .= "
	.wcf-optin-form .woocommerce #payment input[type=checkbox]:checked:before,
	.wcf-optin-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:checked:before{
	    color: {$primary_color};
	}
	.wcf-optin-form .woocommerce #payment input[type=radio]:checked:before{
		background-color: {$primary_color};
	}
	.wcf-optin-form .woocommerce #payment input[type=checkbox]:focus, 
	.wcf-optin-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:focus,
	.wcf-optin-form .woocommerce #payment input[type=radio]:checked:focus,
	.wcf-optin-form .woocommerce #payment input[type=radio]:not(:checked):focus{
		border-color: {$primary_color};
		box-shadow: 0 0 2px rgba( " . $r . ',' . $g . ',' . $b . ", .8);
	}
	.wcf-optin-form .woocommerce-checkout label{
		color: {$field_label_color};
	}

	.wcf-optin-form #order_review .wcf-custom-coupon-field input[type='text'],
	.wcf-optin-form .woocommerce form .form-row input.input-text,
	.wcf-optin-form .woocommerce form .form-row textarea,
	.wcf-optin-form .select2-container--default .select2-selection--single {
		color: {$field_color};
		background: {$field_bg_color};
		border-color: {$field_border_color};
		padding-top: {$field_tb_padding}px;
		padding-bottom: {$field_tb_padding}px;
		padding-left: {$field_lr_padding}px;
		padding-right: {$field_lr_padding}px;
		min-height: {$field_input_size};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.wcf-optin-form .woocommerce .col2-set .col-1, 
	.wcf-optin-form .woocommerce .col2-set .col-2,
	.wcf-optin-form .woocommerce-checkout .shop_table,
	.wcf-optin-form .woocommerce-checkout #order_review_heading,
	.wcf-optin-form .woocommerce-checkout #payment,
	.wcf-optin-form .woocommerce form.checkout_coupon {
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.woocommerce table.shop_table th{
		color: {$field_label_color};
	}

	.wcf-optin-form .woocommerce a{
		color: {$primary_color};
	}
	.wcf-optin-form .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: {$field_color};
	}
	.wcf-optin-form ::-webkit-input-placeholder { /* Chrome/Opera/Safari */
		color: {$field_color};
	}
	.wcf-optin-form ::-moz-placeholder { /* Firefox 19+ */
		color: {$field_color};
	}
	.wcf-optin-form :-ms-input-placeholder { /* IE 10+ */
		color: {$field_color};
	}
	.wcf-optin-form :-moz-placeholder { /* Firefox 18- */
		color: {$field_color};
	}
	.wcf-optin-form .woocommerce form p.form-row label {
		color: {$field_label_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	.wcf-optin-form .woocommerce #order_review button,
	.wcf-optin-form .woocommerce form.woocommerce-form-login .form-row button, 
	.wcf-optin-form .woocommerce #order_review button.wcf-btn-small {
		color: {$submit_color};
		background: {$submit_bg_color};
		padding-top: {$submit_tb_padding}px;
		padding-bottom: {$submit_tb_padding}px;
		padding-left: {$submit_lr_padding}px;
		padding-right: {$submit_lr_padding}px;
		border-color: {$submit_border_color};
		min-height: {$submit_button_height};
		font-size: {$button_font_size}px;
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	    width: {$submit_button_width};

	}

	.wcf-optin-form .woocommerce-checkout form.woocommerce-form-login .button, 
	.wcf-optin-form .woocommerce-checkout form.checkout_coupon .button{
		background: {$submit_bg_color};
		border: 1px {$submit_border_color} solid;
		color: {$submit_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.wcf-optin-form .woocommerce-checkout form.login .button:hover, 
	.wcf-optin-form .woocommerce-checkout form.checkout_coupon .button:hover,
	.wcf-optin-form .woocommerce #payment #place_order:hover,
	.wcf-optin-form .woocommerce #order_review button.wcf-btn-small:hover{
		color: {$submit_hover_color};
		background-color: {$submit_bg_hover_color};
		border-color: {$submit_border_hover_color};
	}
	.wcf-optin-form .woocommerce-info::before,
	.wcf-optin-form .woocommerce-message::before{
		color: {$primary_color};
	}
	.wcf-optin-form{
	    font-family: {$base_font_family};
	}
	img.emoji, img.wp-smiley {}";

if ( 'custom' == $submit_button_height ) {
	$output .= "
		.wcf-optin-form .woocommerce #order_review #payment button{
			margin: {$optin_button_position};
		}
	";
}
