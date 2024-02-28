<?php
/**
 * BB Order Details Form Module front-end CSS php file.
 *
 * @package cartflows
 */

//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

global $post;
?>

.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received {
	margin-bottom: <?php echo $settings->heading_spacing; ?>px;
}

.fl-node-<?php echo $id; ?> .woocommerce-order ul.order_details,
.fl-node-<?php echo $id; ?> .woocommerce-order .woocommerce-customer-details,
.fl-node-<?php echo $id; ?> .woocommerce-order .woocommerce-order-details,
.fl-node-<?php echo $id; ?> .woocommerce-order .woocommerce-order-downloads,
.fl-node-<?php echo $id; ?> .woocommerce-order .woocommerce-bacs-bank-details,
.fl-node-<?php echo $id; ?> .woocommerce-order-details.mollie-instructions {
	margin-bottom: <?php echo $settings->sections_spacing; ?>px;
}

.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->heading_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'heading_typography',
			'selector'     => ".fl-node-$id .cartflows-bb__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received,
			.fl-node-$id .woocommerce-thankyou-order-received",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap .woocommerce-order h2 {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->sections_heading_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'sections_heading_typo',
			'selector'     => ".fl-node-$id .cartflows-bb__order-details-form .wcf-thankyou-wrap .woocommerce-order h2",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap .woocommerce-order,
.fl-node-<?php echo $id; ?> .woocommerce-order-downloads table.shop_table,
.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap p:not( .woocommerce-thankyou-order-received ) {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->sections_content_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details,
.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details,
.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details,
.fl-node-<?php echo $id; ?> .woocommerce-order-downloads,
.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order-downloads {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->sections_bg_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'sections_content_typo',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li,
			.fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table,
			.fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address,
			.fl-node-$id .woocommerce-order-downloads table.shop_table,
			.fl-node-$id .cartflows-bb__order-details-form .wcf-thankyou-wrap p:not( .woocommerce-thankyou-order-received )",
		)
	);
}
?>

<?php
// Downloads Section.
?>
.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap h2.woocommerce-order-downloads__title,
.fl-node-<?php echo $id; ?> .cartflows-bb__order-details-form .wcf-thankyou-wrap h2.woocommerce-order-downloads__title
{
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->downloads_heading_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'downloads_heading_typography',
			'selector'     => ".fl-node-$id .woocommerce-order h2.woocommerce-order-downloads__title,
			.fl-node-$id .woocommerce-order .woocommerce-order-downloads h2.woocommerce-order-downloads__title",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads table.shop_table {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->downloads_text_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->downloads_background_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'downloads_text_typography',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads table.shop_table",
		)
	);
}
?>

<?php
// Order overview Section.
?>
.fl-node-<?php echo $id; ?> .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->order_overview_text_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->order_overview_background_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'order_overview_text_typography',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li",
		)
	);
}
?>

<?php
// Order details Section.
?>
.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->order_details_heading_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'order_details_heading_typography',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table,
.fl-node-<?php echo $id; ?> .woocommerce-order .woocommerce-order-details p.order-again {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->order_details_text_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->order_details_background_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'order_details_text_typography',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table, .fl-node-$id .woocommerce-order .woocommerce-order-details p.order-again",
		)
	);
}
?>

<?php
// Customer details Section.
?>
.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->customer_details_heading_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'customer_details_heading_typography',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address,
.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address p {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->customer_details_text_color ); ?>;
}

.fl-node-<?php echo $id; ?> .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details {
	background-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->customer_details_background_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'customer_details_text_typography',
			'selector'     => ".fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address, .fl-node-$id .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address p",
		)
	);
}
?>
