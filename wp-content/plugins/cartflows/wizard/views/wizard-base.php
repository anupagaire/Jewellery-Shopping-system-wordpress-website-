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
set_current_screen();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php esc_html_e( 'CartFlows Setup', 'cartflows' ); ?></title>

		<script type="text/javascript">
			addLoadEvent = function(func){
				if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}
			};
			var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
			var pagenow = '';
		</script>
		<?php wp_print_scripts( array( 'cartflows-wizard', 'cartflows-setup-helper' ) ); ?>
		<?php
		do_action( 'admin_print_styles' );
		do_action( 'admin_head' );
		?>
	</head>
	<body class="cartflows-setup wp-core-ui" style="background-color: #f0f0f1;">
		<div id="wcf-setup-wizard-page" class="wcf-setup-wizard-page-wrapper" ></div>
	</body>
	<?php wp_footer(); ?>
</html>
<?php exit; ?>
