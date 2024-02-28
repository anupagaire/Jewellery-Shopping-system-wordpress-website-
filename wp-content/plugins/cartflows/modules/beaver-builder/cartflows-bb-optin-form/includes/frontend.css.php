<?php
/**
 * BB Order Details Form Module front-end CSS php file.
 *
 * @package cartflows
 */

//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order,
.fl-node-<?php echo $id; ?> .wcf-optin-form .woocommerce #order_review button {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->global_primary_color ); ?>;
	border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->global_primary_color ); ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover,
.fl-node-<?php echo $id; ?> .wcf-optin-form .woocommerce #order_review button:hover {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->global_primary_color ); ?>;
	border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->global_primary_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'global_typography',
			'selector'     => ".fl-node-$id .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout label,
            .fl-node-$id .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input,
            .fl-node-$id .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout label {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->label_color ); ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->input_bgcolor ); ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->input_color ); ?> !important;
}

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input {
	border-radius: <?php echo ( '' != $settings->input_border_radius ) ? $settings->input_border_radius : '0'; ?>px;
}

<?php if ( 'none' != $settings->input_border_style ) { ?>
	.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input {
		border-style: <?php echo ( '' != $settings->input_border_style ) ? $settings->input_border_style : 'solid'; ?>;
		border-width: <?php echo ( '' != $settings->input_border_width ) ? $settings->input_border_width : '0'; ?>px;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->input_border_color ); ?>;
	}

<?php } ?>

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'input_text_typography',
			'selector'     => ".fl-node-$id .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout label,
            .fl-node-$id .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input",
		)
	);
}
?>

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'button_typography',
			'selector'     => ".fl-node-$id .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
			.fl-node-$id .wcf-optin-form .woocommerce #order_review #payment button",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order,
.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .woocommerce #order_review button {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_text_color ); ?>;
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_bg_color ); ?>;
}

<?php // Button text hover color. ?>

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover,
.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .woocommerce #order_review button:hover {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_text_hover_color ); ?> !important;
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color ); ?> !important;
	border-color:  <?php echo FLBuilderColor::hex_or_rgb( $settings->button_border_hover_color ); ?> !important;
}

.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order,
.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .woocommerce #order_review button {
	border-radius: <?php echo ( '' != $settings->button_border_radius ) ? $settings->button_border_radius : '0'; ?>px;
}

<?php if ( 'none' != $settings->button_border_style ) { ?>

	.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order,
	.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .woocommerce #order_review button {
		border-style: <?php echo ( '' != $settings->button_border_style ) ? $settings->button_border_style : 'solid'; ?>;
		border-width: <?php echo ( '' != $settings->button_border_width ) ? $settings->button_border_width : '0'; ?>px;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_border_color ); ?>;
	}

<?php } else { ?>

	.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order,
	.fl-node-<?php echo $id; ?> .cartflows-bb__optin-form .wcf-optin-form .woocommerce #order_review button {
		border-width: 0px !important;
	}

<?php } ?>
