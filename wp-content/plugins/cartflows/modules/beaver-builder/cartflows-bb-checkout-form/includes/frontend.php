<?php
/**
 * Frontend view
 *
 * @package cartflows
 */

$data_settings = array();
$checkout_id   = get_the_ID();

/* Add module setting options to filters */
$module->dynamic_option_filters();

do_action( 'cartflows_bb_before_checkout_shortcode', $checkout_id );

$data_settings = apply_filters( 'cartflows_bb_checkout_settings', $data_settings );

?>
<div class="cartflows-bb__checkout-form" data-settings-data="<?php echo esc_attr( wp_json_encode( $data_settings ) ); ?>">
	<?php echo do_shortcode( '[cartflows_checkout]' ); ?>
</div>
