<?php
/**
 * Admin Base HTML.
 *
 * @package CARTFLOWS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wcf-menu-page-wrapper">
	<div id="wcf-menu-page">
		<div class="wcf-menu-page-content wcf-clear">
		<?php

			do_action( 'cartflows_render_admin_page_content', $menu_page_slug, $page_action );
		?>
		</div>
	</div>
</div>
