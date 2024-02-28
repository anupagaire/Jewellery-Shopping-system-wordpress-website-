<?php
/**
 * Frontend view
 *
 * @package cartflows
 */

$order_overview = $settings->show_order_overview ? $settings->show_order_overview : 'no';

$order_details = $settings->show_order_details ? $settings->show_order_details : 'no';

$shipping_address = $settings->show_shipping_address ? $settings->show_shipping_address : 'no';

$billing_address = $settings->show_billing_address ? $settings->show_billing_address : 'no';

/* Add module setting options to filters */
$module->dynamic_option_filters();

?>
<div class = "cartflows-bb__order-details-form cartflows-bb__display-order-overview-<?php echo esc_attr( $order_overview ); ?> cartflows-bb__display-order-details-<?php echo esc_attr( $order_details ); ?> cartflows-bb__display-billing-address-<?php echo esc_attr( $billing_address ); ?> cartflows-bb__display-shipping-address-<?php echo esc_attr( $shipping_address ); ?>">
	<?php echo do_shortcode( '[cartflows_order_details]' ); ?>
</div>
