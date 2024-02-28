<?php
/**
 * Checkout template
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );
$fields_skins    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );
$checkout_skin   = '';

/**
 * Added this condition to add backward compatibility of the floating labes styles
 *
 * @since 1.9.0
 *
 * @to-do: Remove this condition after 2-3 update.
 * */
if ( 'style-one' === $fields_skins ) {
	$fields_skins = 'modern-label';
}

if ( 'modern-one-column' === $checkout_layout || 'modern-checkout' === $checkout_layout ) {
	// Adding a layout specific classes depending on the checkout style modern-checout OR modern-one-Column.
	$checkout_skin   = 'modern-checkout' === $checkout_layout ? 'wcf-modern-skin-two-column' : 'wcf-modern-skin-one-column';
	$checkout_layout = 'modern-checkout'; // Keeping the style and a common to modern checkout class for one and two column layouts.
}

$checkout_layout = apply_filters( 'cartflows_checkout_form_layout', $checkout_layout );

?>
<div id="wcf-embed-checkout-form" class="wcf-embed-checkout-form wcf-embed-checkout-form-<?php echo esc_attr( $checkout_layout ); ?> <?php echo esc_attr( $checkout_skin ); ?> wcf-field-<?php echo esc_attr( $fields_skins ); ?>">
<!-- CHECKOUT SHORTCODE -->
<?php do_action( 'cartflows_add_before_main_section', $checkout_layout ); ?>

<?php
	$checkout_html = do_shortcode( '[woocommerce_checkout]' );

if (
		empty( $checkout_html ) ||
		trim( $checkout_html ) == '<div class="woocommerce"></div>'
	) {
	do_action( 'cartflows_checkout_cart_empty', $checkout_id );
	echo esc_html__( 'Your cart is currently empty.', 'cartflows' );
} else {
	// Ignoring the escaping rule as we are echoing shortcode.
	echo $checkout_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>

<?php do_action( 'cartflows_add_after_main_section', $arg = '' ); ?>
<!-- END CHECKOUT SHORTCODE -->
</div>
