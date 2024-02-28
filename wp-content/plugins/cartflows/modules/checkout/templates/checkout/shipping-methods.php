<?php
/**
 * CartFlows Mobile Order Review Table for Modern Checkout.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$available_methods        = $package['rates'];
$show_package_details     = count( $packages ) > 1;
$show_shipping_calculator = is_cart() && apply_filters( 'woocommerce_shipping_show_shipping_calculator', $first, $i, $package );
$package_details          = implode( ', ', $product_names );
/* translators: %d: shipping package number */
$package_name          = apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'cartflows' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'cartflows' ), $i, $package );
$index                 = $i;
$chosen_method         = $chosen_method;
$formatted_destination = WC()->countries->get_formatted_address( $package['destination'], ', ' );

$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( WC()->customer->has_calculated_shipping() );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';
?>

<!-- Mobile responsive shipping methods template -->

<tr class="woocommerce-shipping-totals shipping wcf-shipping-methods">
	<th colspan="2" data-title="<?php echo esc_attr( $package_name ); ?>">
		<div class="wcf-shipping-methods-wrapper">
			<div class="wcf-shipping-methods-title"><?php echo wp_kses_post( $package_name ); ?></div>
			<div class="wcf-shipping-method-options">
				<?php if ( $available_methods ) : ?>
					<ul id="shipping_method" class="woocommerce-shipping-methods">
						<?php foreach ( $available_methods as $method ) : ?>
							<li>
								<?php
								if ( 1 < count( $available_methods ) ) {
									printf( '<input type="radio" name="shipping_method[%1$d]_wcf" data-index="%1$d" id="wcf_shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', esc_attr( $index ), esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) );
								} else {
									printf( '<input type="hidden" name="shipping_method[%1$d]_wcf" data-index="%1$d" id="wcf_shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', esc_attr( $index ), esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) );
								}
								// Skipping the escaping check for wc_cart_totals_shipping_method_label as it is of WooCommerce and it does it's own escaping.
								printf( '<label for="wcf_shipping_method_%1$s_%2$s">%3$s</label>', esc_attr( $index ), esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								do_action( 'woocommerce_after_shipping_rate', $method, $index );
								?>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php if ( is_cart() ) : ?>
						<p class="woocommerce-shipping-destination">
							<?php
							if ( $formatted_destination ) {
								// Translators: $s shipping destination.
								printf( esc_html__( 'Shipping to %s.', 'cartflows' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' );
								$calculator_text = esc_html__( 'Change address', 'cartflows' );
							} else {
								echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', __( 'Shipping options will be updated during checkout.', 'cartflows' ) ) );
							}
							?>
						</p>
					<?php endif; ?>
					<?php
				elseif ( ! $has_calculated_shipping || ! $formatted_destination ) :
					if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
						echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'cartflows' ) ) );
					} else {
						echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'cartflows' ) ) );
					}
				elseif ( ! is_cart() ) :
					echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'cartflows' ) ) );
				else :
					// Translators: $s shipping destination.
					echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s.', 'cartflows' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ) ) );
					$calculator_text = esc_html__( 'Enter a different address', 'cartflows' );
				endif;
				?>

				<?php if ( $show_package_details ) : ?>
					<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
				<?php endif; ?>

				<?php if ( $show_shipping_calculator ) : ?>
					<?php woocommerce_shipping_calculator( $calculator_text ); ?>
				<?php endif; ?>
			</div>
		</div>
	</th>
</tr>
<?php
