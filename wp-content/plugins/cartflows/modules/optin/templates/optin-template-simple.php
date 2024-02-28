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
$optin_layout = 'one-column';
$fields_skins = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-input-fields-skins' );
?>
<div id="wcf-optin-form" class="wcf-optin-form wcf-optin-form-one-column wcf-field-<?php echo esc_attr( $fields_skins ); ?>">

<!-- CHECKOUT SHORTCODE -->
<?php do_action( 'cartflows_optin_before_main_section', $optin_layout ); ?>

<?php

$checkout_html = do_shortcode( '[woocommerce_checkout]' );

if (
		empty( $checkout_html ) ||
		trim( $checkout_html ) == '<div class="woocommerce"></div>'
	) {

	echo esc_html__( 'Your cart is currently empty.', 'cartflows' );
} else {
	// Ignoring the escaping rule as we are echoing shortcode.
	echo $checkout_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>

<?php do_action( 'cartflows_optin_after_main_section', $optin_layout ); ?>
<!-- END CHECKOUT SHORTCODE -->
</div>
