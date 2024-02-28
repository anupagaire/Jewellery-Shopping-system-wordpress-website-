<?php
/**
 * Frontend view
 *
 * @package next-step-button
 */

?>
<div class="<?php echo esc_attr( $module->get_classname() ); ?>">

	<?php
	if ( isset( $settings->threed_button_options ) && ( 'animate_top' == $settings->threed_button_options || 'animate_bottom' == $settings->threed_button_options || 'animate_left' == $settings->threed_button_options || 'animate_right' == $settings->threed_button_options ) ) {
		?>
		<p class="perspective">
		<?php
	}
	?>

	<a href="?class=wcf-next-step" class="cartflows-bb__next-step-button cartflows-bb__next-step-button cartflows-bb__next-step-creative-button <?php echo esc_attr( 'cartflows-bb__next-step-creative-' . $settings->style . '-btn' ); ?> <?php echo esc_attr( $module->get_button_style() ); ?>" role="button" >
		<?php
		if ( ! empty( $settings->icon ) && ( 'before' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) :

			if ( 'flat' == $settings->style && isset( $settings->flat_button_options ) && ( 'animate_to_right' == $settings->flat_button_options || 'animate_to_left' == $settings->flat_button_options || 'animate_from_top' == $settings->flat_button_options || 'animate_from_bottom' == $settings->flat_button_options ) ) {
				$add_class_to_icon = '';
			} else {
				$add_class_to_icon = 'cartflows-bb__next-step-creative-button-icon cartflows-bb__next-step-button-icon-before cartflows-bb__next-step-creative-button-icon-before';
			}
			?>

			<i class="<?php echo esc_attr( $add_class_to_icon ); ?> fa <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>

		<?php endif; ?>
		<?php if ( ! empty( $settings->text ) ) : ?>
			<span class="cartflows-bb__next-step-button-text cartflows-bb__next-step-creative-button-text"><?php echo esc_html( $settings->text ); ?></span>
		<?php endif; ?>
		<?php
		if ( ! empty( $settings->icon ) && 'after' == $settings->icon_position ) :
			if ( 'flat' == $settings->style && isset( $settings->flat_button_options ) && ( 'animate_to_right' == $settings->flat_button_options || 'animate_to_left' == $settings->flat_button_options || 'animate_from_top' == $settings->flat_button_options || 'animate_from_bottom' == $settings->flat_button_options ) ) {
				$add_class_to_icon = '';
			} else {
				$add_class_to_icon = 'cartflows-bb__next-step-button-icon-after cartflows-bb__next-step-creative-button-icon-after';
			}
			?>
			<i class="cartflows-bb__next-step-button-icon cartflows-bb__next-step-creative-button-icon <?php echo esc_attr( $add_class_to_icon ); ?> fa <?php echo esc_attr( $settings->icon ); ?>"></i>
		<?php endif; ?>
	</a>

	<?php
	if ( isset( $settings->threed_button_options ) && ( 'animate_top' == $settings->threed_button_options || 'animate_bottom' == $settings->threed_button_options || 'animate_left' == $settings->threed_button_options || 'animate_right' == $settings->threed_button_options ) ) {
		?>
		</p>
		<?php
	}
	?>
</div>
