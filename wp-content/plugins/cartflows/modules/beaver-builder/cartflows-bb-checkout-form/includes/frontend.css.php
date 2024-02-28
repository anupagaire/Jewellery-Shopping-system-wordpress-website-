<?php
/**
 * BB Checkout Form Module front-end CSS php file.
 *
 * @package cartflows
 */

//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

global $post;

?>

#fl-field-checkout_layout .cartflows-bb-link,
#fl-field-input_skins .cartflows-bb-link {
	color: #0086b0;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-modern-checkout,
body .wcf-pre-checkout-offer-wrapper{
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->global_primary_color ) ? '--wcf-primary-color:' . FLBuilderColor::hex_or_rgb( $settings->global_primary_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->global_text_color ) ? '--wcf-text-color:' . FLBuilderColor::hex_or_rgb( $settings->global_text_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->heading_color ) ? '--wcf-heading-color:' . FLBuilderColor::hex_or_rgb( $settings->heading_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->label_color ) ? '--wcf-field-label-color:' . FLBuilderColor::hex_or_rgb( $settings->label_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->input_bgcolor ) ? '--wcf-field-bg-color:' . FLBuilderColor::hex_or_rgb( $settings->input_bgcolor ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->input_color ) ? '--wcf-field-text-color:' . FLBuilderColor::hex_or_rgb( $settings->input_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->input_border_color ) ? '--wcf-field-border-color:' . FLBuilderColor::hex_or_rgb( $settings->input_border_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->button_text_color ) ? '--wcf-btn-text-color:' . FLBuilderColor::hex_or_rgb( $settings->button_text_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->button_text_hover_color ) ? '--wcf-btn-hover-text-color:' . FLBuilderColor::hex_or_rgb( $settings->button_text_hover_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->button_bg_color ) ? '--wcf-btn-bg-color:' . FLBuilderColor::hex_or_rgb( $settings->button_bg_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color ) ? '--wcf-btn-bg-hover-color:' . FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->field_label_color ) ? '--wcf-field-error-label-color:' . FLBuilderColor::hex_or_rgb( $settings->field_label_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->error_field_border_color ) ? '--wcf-field-error-border-color:' . FLBuilderColor::hex_or_rgb( $settings->error_field_border_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->payment_section_text_color ) ? '--wcf-payment-section-label-color:' . FLBuilderColor::hex_or_rgb( $settings->payment_section_text_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->payment_section_desc_color ) ? '--wcf-payment-section-desc-text-color:' . FLBuilderColor::hex_or_rgb( $settings->payment_section_desc_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->payment_info_bg_color ) ? '--wcf-payment-section-desc-bg-color:' . FLBuilderColor::hex_or_rgb( $settings->payment_info_bg_color ) . ';' : '' ); ?>
	<?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->payment_section_bg_color ) ? '--wcf-payment-section-bg-color:' . FLBuilderColor::hex_or_rgb( $settings->payment_section_bg_color ) . ';' : '' ); ?>

}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before {
	border-top-color: <?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->global_primary_color ) ? FLBuilderColor::hex_or_rgb( $settings->global_primary_color ) . ';' : '' ); ?>
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn {
	background-color: <?php echo ( '' !== FLBuilderColor::hex_or_rgb( $settings->global_primary_color ) ? FLBuilderColor::hex_or_rgb( $settings->global_primary_color ) . ';' : '' ); ?>
	color: #fff;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'global_typography',
			'selector'     => ".fl-node-$id .cartflows-bb__checkout-form .wcf-embed-checkout-form",
		)
	);
}
?>

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'heading_typography',
			'selector'     => ".fl-node-$id .cartflows-bb__checkout-form .wcf-embed-checkout-form .woocommerce h3,
            .fl-node-$id .cartflows-bb__checkout-form .wcf-embed-checkout-form .woocommerce h3 span,
            .fl-node-$id .cartflows-bb__checkout-form .wcf-embed-checkout-form .woocommerce-checkout #order_review_heading,
            .fl-node-$id .cartflows-bb__checkout-form .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-name,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce .col2-set .col-1 h3,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce .col2-set .col-2 h3",
		)
	);
}
?>

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'input_text_typography',
			'selector'     => ".fl-node-$id .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .fl-node-$id .wcf-embed-checkout-form .woocommerce form .form-row textarea,
            .fl-node-$id .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce form .form-row select.select,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce .col2-set .col-1,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce .col2-set .col-2,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce form p.form-row label,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce #payment [type='radio']:checked + label,
            .fl-node-$id .wcf-embed-checkout-form .woocommerce #payment [type='radio']:not(:checked) + label,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce form .form-row select,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce #order_review .wcf-custom-coupon-field input[type='text']",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"],
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row textarea,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row select.select,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row select {
	border-radius: <?php echo ( '' != $settings->input_border_radius ) ? $settings->input_border_radius : '4'; ?>px;
}

<?php if ( 'none' !== $settings->input_border_style ) { ?>
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"],
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row textarea,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row select.select,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form .form-row select {
		border-style: <?php echo ( '' != $settings->input_border_style ) ? $settings->input_border_style : 'solid'; ?>;
		border-width: <?php echo ( '' != $settings->input_border_width ) ? $settings->input_border_width : '1'; ?>px;
	}

<?php } ?>

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'button_typography',
			'selector'     => ".fl-node-$id .wcf-embed-checkout-form .woocommerce #order_review button,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
			.fl-node-$id .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
			.fl-node-$id .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
			.fl-node-$id .wcf-embed-checkout-form form.checkout_coupon .button,
			.fl-node-$id .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
			body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn",
		)
	);
}
?>

body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_text_color ); ?>;
}

body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_bg_color ); ?>;
}

<?php // Button text hover color. ?>

body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn:hover {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_text_hover_color ); ?>;
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color ); ?>;
	border-color:  <?php echo FLBuilderColor::hex_or_rgb( $settings->button_border_hover_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce #order_review button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form form.checkout_coupon .button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn {
	border-radius: <?php echo ( '' != $settings->button_border_radius ) ? $settings->button_border_radius : ''; ?>px;
}

<?php if ( 'none' != $settings->button_border_style ) { ?>

	body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn {
		border-style: <?php echo ( '' != $settings->button_border_style ) ? $settings->button_border_style : 'solid'; ?>;
		border-width: <?php echo ( '' != $settings->button_border_width ) ? $settings->button_border_width : '0'; ?>px;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->button_border_color ); ?>;
	}

<?php } else { ?>

	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce #order_review button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce #payment #place_order,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form form.checkout_coupon .button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
	.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
	body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn {
		border-width: 0px !important;
	}

<?php } ?>

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods {
	border-radius: <?php echo ( '' != $settings->payment_section_border_radius ) ? $settings->payment_section_border_radius : '0'; ?>px;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods {
	padding-top: <?php echo ( '' != $settings->payment_section_padding_dimension_top ) ? $settings->payment_section_padding_dimension_top : ''; ?>px;
	padding-bottom: <?php echo ( '' != $settings->payment_section_padding_dimension_bottom ) ? $settings->payment_section_padding_dimension_bottom : ''; ?>px;
	padding-left: <?php echo ( '' != $settings->payment_section_padding_dimension_left ) ? $settings->payment_section_padding_dimension_left : ''; ?>px;
	padding-right: <?php echo ( '' != $settings->payment_section_padding_dimension_right ) ? $settings->payment_section_padding_dimension_right : ''; ?>px;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods {
	margin-top: <?php echo ( '' != $settings->payment_section_margin_dimension_top ) ? $settings->payment_section_margin_dimension_top : ''; ?>px;
	margin-bottom: <?php echo ( '' != $settings->payment_section_margin_dimension_bottom ) ? $settings->payment_section_margin_dimension_bottom : ''; ?>px;
	margin-left: <?php echo ( '' != $settings->payment_section_margin_dimension_left ) ? $settings->payment_section_margin_dimension_left : ''; ?>px;
	margin-right: <?php echo ( '' != $settings->payment_section_margin_dimension_right ) ? $settings->payment_section_margin_dimension_right : ''; ?>px;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce .woocommerce-error,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->error_text_color ); ?>;
	border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->error_border_color ); ?>;
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->error_bg_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table th,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table th.product-name,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table th.product-total,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table td {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->column_text_color ); ?>;
}

.fl-node-<?php echo $id; ?>  .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->column_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tbody,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.cart-discount,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.cart-subtotal,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.order-total:not( .recurring-total ) th,
.fl-node-<?php echo $id; ?> .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.order-total:not( .recurring-total ) td{
	border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->column_color ); ?>;
}
