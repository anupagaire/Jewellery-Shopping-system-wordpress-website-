<?php
/**
 * Frontend view
 *
 * @package cartflows
 */

/* Add module setting options to filters */
$module->dynamic_option_filters();

$optin_id = get_the_id();

do_action( 'cartflows_bb_before_optin_shortcode', $optin_id );

?>
<div class = "cartflows-bb__optin-form">
	<?php echo do_shortcode( '[cartflows_optin]' ); ?>
</div>
