<?php
/**
 * Dynamic DIVI Thank you css.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$output = "
	
	.et_pb_module #wcf-thankyou-wrap{
		color: {$text_color};
		font-family: {$text_font_family};
		max-width:{$container_width}px;
		font-size: {$text_font_size}px;
	}

	.et_pb_module #wcf-thankyou-wrap .woocommerce-order h2.woocommerce-column__title, 
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order h2.woocommerce-order-details__title, 
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order-details h2,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order h2.wc-bacs-bank-details-heading,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order h2.woocommerce-order-downloads__title {
		color: {$heading_text_color};
		font-family: {$heading_font_family};
		font-weight: {$heading_font_weight};
	}

	.et_pb_module #wcf-thankyou-wrap .woocommerce-order ul.order_details,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-bacs-bank-details,
	.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads{
		background-color: {$section_bg_color}
	}
	img.emoji, img.wp-smiley {}
	";

if ( 'no' == $show_order_review ) {
	$output .= '
		.et_pb_module #wcf-thankyou-wrap .woocommerce-order ul.order_details{
			display: none !important;
		}
		';
}

if ( 'no' == $show_order_details ) {
	$output .= '
		.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details{
			display: none !important;
		}
		';
}

if ( 'no' == $show_billing_details ) {
	$output .= '
		.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column--billing-address{
			display: none;
		}
		.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column--shipping-address{
			float:left;
		}
		';
}

if ( 'no' == $show_shipping_details ) {
	$output .= '
		.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column--shipping-address{
			display: none ;
		}
		';
}

if ( 'no' == $show_billing_details && 'no' == $show_shipping_details ) {
	$output .= '
		.et_pb_module #wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details{
			display: none !important;
		}
		';
}
