<?php
/**
 * Flow
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="robots" content="noindex">
	<title><?php wp_title( '-', true, 'right' ); ?><?php bloginfo( 'name' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<div class="wrapper">
		<header class="header">
		</header>

		<div class="main-container">
			<div class="checkout-forms">
				<!-- CHECKOUT SHORTCODE -->
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
				<!-- END CHECKOUT SHORTCODE -->
			</div>
		</div>

		<footer class="footer">
			<p><?php esc_html_e( 'Copyright &copy;', 'cartflows' ); ?> <?php
			echo esc_html( gmdate( 'Y' ) );
			echo ' ' . esc_html( get_bloginfo( 'name' ) );
			?>
			- <?php esc_html_e( 'All Rights Reserved', 'cartflows' ); ?></p>
		</footer>
	</div>

	<div class="wcf-hide">
	<?php wp_footer(); ?>
	</div>
</body>
</html>
