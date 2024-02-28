<?php
/**
 * Admin Base HTML
 *
 * @package CARTFLOWS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wcf-menu-page-header <?php echo esc_attr( implode( ' ', $header_wrapper_class ) ); ?>">
	<div class="wcf-container wcf-flex">
		<div class="wcf-title">
			<span class="screen-reader-text"><?php echo esc_html( CARTFLOWS_NAME ); ?></span>
			<img class="wcf-logo" src="<?php echo esc_url_raw( CARTFLOWS_URL . 'assets/images/cartflows-logo.svg' ); ?>" alt="" />
		</div>
		<div class="wcf-top-links">
			<?php
				esc_attr_e( 'Generate More Leads & More Sales', 'cartflows' );
			?>
		</div>
	</div>
</div>
