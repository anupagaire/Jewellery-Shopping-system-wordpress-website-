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
	.wcf-embed-checkout-form .wcf-checkout-header-image img{
		width: {$header_logo_width}px;
	}
	.wcf-embed-checkout-form .woocommerce #payment input[type=checkbox]:focus,
	.wcf-embed-checkout-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:focus,
	.wcf-embed-checkout-form .woocommerce #payment input[type=radio]:checked:focus,
	.wcf-embed-checkout-form .woocommerce #payment input[type=radio]:not(:checked):focus{
		box-shadow: 0 0 2px rgba( " . $r . ',' . $g . ',' . $b . ", .8);
	}
	
	.wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box{
		background-color: {$hl_bg_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.wcf-embed-checkout-form #add_payment_method #payment div.payment_box::before,
	.wcf-embed-checkout-form .woocommerce-cart #payment div.payment_box::before,
	.wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box::before
	{
	    border-bottom-color: {$hl_bg_color};
	    border-right-color: transparent;
	    border-left-color: transparent;
	    border-top-color: transparent;
	    position: absolute;
	}

	.wcf-embed-checkout-form .woocommerce #payment [type='radio']:checked + label,
	.wcf-embed-checkout-form .woocommerce #payment [type='radio']:not(:checked) + label{
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type='text'],
	.wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
	.wcf-embed-checkout-form .woocommerce form .form-row textarea,
	.wcf-embed-checkout-form .select2-container--default .select2-selection--single,
	.wcf-embed-checkout-form .woocommerce form .form-row select.select {
		border-color: {$field_border_color};
		padding-top: {$field_tb_padding}px;
		padding-bottom: {$field_tb_padding}px;
		padding-left: {$field_lr_padding}px;
		padding-right: {$field_lr_padding}px;
		min-height: {$field_input_size};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.wcf-embed-checkout-form .woocommerce .col2-set .col-1,
	.wcf-embed-checkout-form .woocommerce .col2-set .col-2,
	.wcf-embed-checkout-form .woocommerce-checkout .shop_table,
	.wcf-embed-checkout-form .woocommerce-checkout #order_review_heading,
	.wcf-embed-checkout-form .woocommerce-checkout #payment,
	.wcf-embed-checkout-form .woocommerce form.checkout_coupon
	{
		background-color: {$section_bg_color};
		border-color: {$box_border_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	
	.wcf-embed-checkout-form .woocommerce form p.form-row label {
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce #payment button,
	.wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button,
	.wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small {
		padding-top: {$submit_tb_padding}px;
		padding-bottom: {$submit_tb_padding}px;
		padding-left: {$submit_lr_padding}px;
		padding-right: {$submit_lr_padding}px;
		border-color: {$submit_border_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
	.wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button{
		border: 1px {$submit_border_color} solid;
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce-checkout form.login .button:hover,
	.wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button:hover,
	.wcf-embed-checkout-form .woocommerce #payment #place_order:hover,
	.wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small:hover{
		border-color: {$submit_border_hover_color};
	}
	.wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-customer-info-main-wrapper h3,
	.wcf-embed-checkout-form .woocommerce h3,
	.wcf-embed-checkout-form .woocommerce h3 span,
	.wcf-embed-checkout-form .woocommerce-checkout #order_review_heading{
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}
	.wcf-embed-checkout-form{
	    font-family: {$base_font_family};
	}";

if ( 'modern-checkout' === $checkout_layout ) {
	$output .= "
		.wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce-checkout table.shop_table {
			background-color: {$hl_bg_color} !important;
		}
		.wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce-checkout #payment div.payment_box{
			background-color: {$hl_bg_color} !important;
		}
		";
}

	$output .= 'img.emoji, img.wp-smiley {}';
