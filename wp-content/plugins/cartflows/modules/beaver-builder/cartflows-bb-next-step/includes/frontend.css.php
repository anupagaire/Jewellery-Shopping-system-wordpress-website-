<?php
/**
 * BB Next Step Button Module front-end CSS php file.
 *
 * @package BB Next Step Button Module
 */

 //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

global $post;

$settings->bg_color         = Cartflows_BB_Helper::cartflows_bb_colorpicker( $settings, 'bg_color', true );
$settings->bg_hover_color   = Cartflows_BB_Helper::cartflows_bb_colorpicker( $settings, 'bg_hover_color', true );
$settings->text_color       = Cartflows_BB_Helper::cartflows_bb_colorpicker( $settings, 'text_color' );
$settings->text_hover_color = Cartflows_BB_Helper::cartflows_bb_colorpicker( $settings, 'text_hover_color' );

// Border Size.
if ( 'transparent' == $settings->style ) {
	$border_size = ( '' !== trim( $settings->border_size ) ) ? $settings->border_size : '2';
} else {
	$border_size = 1;
}
// Border Color.
if ( ! empty( $settings->bg_color ) ) {
	$border_color = $settings->bg_color;
}
if ( ! empty( $settings->bg_hover_color ) ) {
	$border_hover_color = $settings->bg_hover_color;
}

// Old Background Gradient Setting.
if ( isset( $settings->three_d ) && $settings->three_d ) {
	$settings->style = 'gradient';
}

// Background Gradient.
if ( ! empty( $settings->bg_color ) ) {
	$hex_bg        = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_color );
	$bg_grad_start = '#' . FLBuilderColor::adjust_brightness( $hex_bg, 30, 'lighten' );
}
if ( ! empty( $settings->bg_hover_color ) ) {
	$hex_hover_bg        = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_hover_color );
	$bg_hover_grad_start = '#' . FLBuilderColor::adjust_brightness( $hex_hover_bg, 30, 'lighten' );
}

?>

<?php if ( ! empty( $settings->icon ) ) { ?>

	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap i {
		font-size: <?php echo $settings->icon_size . 'px'; ?>;
	}

	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap .cartflows-bb__next-step-creative-button .cartflows-bb__next-step-creative-button-icon-before {
		margin-right: <?php echo $settings->icon_spacing . 'px'; ?>;
	}

	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap .cartflows-bb__next-step-creative-button .cartflows-bb__next-step-creative-button-icon-after {
		margin-left: <?php echo $settings->icon_spacing . 'px'; ?>;
	}

<?php } ?>

<?php if ( 'animate_top' == $settings->threed_button_options || 'animate_bottom' == $settings->threed_button_options ) { ?>
/* 3D Fix */

	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap.cartflows-bb__next-step-creative-button-width-auto .perspective,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap.cartflows-bb__next-step-creative-button-width-custom .perspective {
		display: inline-block;
		max-width: 100%;
	}
<?php } ?>

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'button_typo',
			'selector'     => ".fl-node-$id .cartflows-bb__next-step-creative-button-wrap a,.fl-node-$id .cartflows-bb__next-step-creative-button-wrap a:visited",
		)
	);
}
?>

<?php if ( 'default' !== $settings->style ) { ?>
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
		<?php
		if ( 'custom' == $settings->width ) {
				$padding_top_bottom = ( '' !== $settings->padding_top_bottom ) ? $settings->padding_top_bottom : '0';
				$padding_left_right = ( '' !== $settings->padding_left_right ) ? $settings->padding_left_right : '0';
			?>

			padding-top: <?php echo $padding_top_bottom; ?>px;
			padding-bottom: <?php echo $padding_top_bottom; ?>px;
			padding-left: <?php echo $padding_left_right; ?>px;
			padding-right: <?php echo $padding_left_right; ?>px;
			<?php
		}

		if ( '' != $settings->border_radius ) :
			?>
			border-radius: <?php echo $settings->border_radius; ?>px;
			-moz-border-radius: <?php echo $settings->border_radius; ?>px;
			-webkit-border-radius: <?php echo $settings->border_radius; ?>px;
			<?php endif; ?>
			<?php if ( 'custom' == $settings->width ) : ?>
			width: <?php echo $settings->custom_width; ?>px;
			min-height: <?php echo $settings->custom_height; ?>px;
			display: -webkit-inline-box;
			display: -ms-inline-flexbox;
			display: inline-flex;
			-webkit-box-align: center;
			-ms-flex-align: center;
			align-items: center;
			-webkit-box-pack: center;
			-ms-flex-pack: center;
			justify-content: center;
		<?php endif; ?>

		<?php
		if ( 'transparent' == $settings->style ) : // Transparent.
			?>
			border: <?php echo $border_size; ?>px solid <?php echo $border_color; ?>;
		<?php endif; ?>

		<?php if ( ! empty( $settings->bg_color ) ) : ?>
			background: <?php echo $settings->bg_color; ?>;
			border: <?php echo $border_size; ?>px solid <?php echo $border_color; ?>;
			<?php
			if ( 'transparent' == $settings->style ) : // Transparent.
				?>
				background: none;
			<?php endif; ?>

			<?php if ( 'gradient' == $settings->style ) : // Gradient. ?>
			background: -moz-linear-gradient(top,  <?php echo $bg_grad_start; ?> 0%, <?php echo $settings->bg_color; ?> 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $bg_grad_start; ?>), color-stop(100%,<?php echo $settings->bg_color; ?>)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top,  <?php echo $bg_grad_start; ?> 0%,<?php echo $settings->bg_color; ?> 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top,  <?php echo $bg_grad_start; ?> 0%,<?php echo $settings->bg_color; ?> 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top,  <?php echo $bg_grad_start; ?> 0%,<?php echo $settings->bg_color; ?> 100%); /* IE10+ */
			background: linear-gradient(to bottom,  <?php echo $bg_grad_start; ?> 0%,<?php echo $settings->bg_color; ?> 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bg_grad_start; ?>', endColorstr='<?php echo $settings->bg_color; ?>',GradientType=0 ); /* IE6-9 */
			<?php endif; ?>
		<?php endif; ?>
	}
	<?php
} else {
	?>

	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover {
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->border_hover_color ); ?>;
	}

	<?php
	$padding_top_bottom = $settings->button_padding_dimension_top;
	?>
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
		<?php
		if ( isset( $settings->button_padding_dimension_top ) ) {
			echo 'padding-top:' . $settings->button_padding_dimension_top . 'px;';
		}
		if ( isset( $settings->button_padding_dimension_bottom ) ) {
			echo 'padding-bottom:' . $settings->button_padding_dimension_bottom . 'px;';
		}
		if ( isset( $settings->button_padding_dimension_left ) ) {
			echo 'padding-left:' . $settings->button_padding_dimension_left . 'px;';
		}
		if ( isset( $settings->button_padding_dimension_right ) ) {
			echo 'padding-right:' . $settings->button_padding_dimension_right . 'px;';
		}
		?>
	}
	<?php
	if ( class_exists( 'FLBuilderCSS' ) ) {
		// Border - Settings.
		FLBuilderCSS::border_field_rule(
			array(
				'settings'     => $settings,
				'setting_name' => 'button_border',
				'selector'     => ".fl-node-$id .cartflows-bb__next-step-module-content.cartflows-bb__next-step-creative-button-wrap a, .fl-node-$id a.cartflows-bb__next-step-button",
			)
		);
	}
	?>
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-button {
		background: <?php echo $settings->bg_color; ?>;
	}
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-module-content.cartflows-bb__next-step-creative-button-wrap a:hover {
		<?php echo 'border-color:' . FLBuilderColor::hex_or_rgb( $settings->border_hover_color ) . ';'; ?>
	}
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover {
		background: <?php echo $settings->bg_hover_color; ?>;
	}
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a *,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited * {
		color: <?php echo $settings->text_color; ?>;
	}
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover *,
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-button:hover {
		color: <?php echo $settings->text_hover_color; ?>;
	}
<?php } ?>

	<?php if ( is_array( $settings->button_typo ) ) { ?>
		<?php if ( isset( $settings->button_typo['line_height'] ) && is_array( $settings->button_typo['line_height'] ) && 'custom' == $settings->width && '' != $settings->custom_height ) { ?>
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
				line-height: <?php echo $settings->custom_height; ?>px;
			}
		<?php } elseif ( isset( $settings->button_typo['line_height'] ) && is_object( $settings->button_typo['line_height'] ) && 'custom' == $settings->width && '' != $settings->custom_height ) { ?>
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
				line-height: <?php echo $settings->custom_height; ?>px;
			}
		<?php } ?>
	<?php } elseif ( is_object( $settings->button_typo ) ) { ?>
			<?php if ( isset( $settings->button_typo->line_height ) && is_object( $settings->button_typo->line_height ) && 'custom' == $settings->width && '' != $settings->custom_height ) { ?>
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
				line-height: <?php echo $settings->custom_height; ?>px;
			}
		<?php } elseif ( isset( $settings->button_typo->line_height ) && is_object( $settings->button_typo->line_height ) && 'custom' == $settings->width && '' != $settings->custom_height ) { ?>
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
			html.internet-explorer .fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
				line-height: <?php echo $settings->custom_height; ?>px;
			}
		<?php } ?>
	<?php } ?>

<?php
if ( 'custom' == $settings->width && '' != $settings->custom_height ) :
	$translate_text = intval( $settings->custom_height ) + ( intval( $padding_top_bottom ) * 2 ) + 50;  
	?>
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-flat-btn.cartflows-bb__next-step-animate_from_top-btn:hover .cartflows-bb__next-step-button-text {
	-webkit-transform: translateY(<?php echo $translate_text; ?>px);
	-moz-transform: translateY(<?php echo $translate_text; ?>px);
	-ms-transform: translateY(<?php echo $translate_text; ?>px);
	-o-transform: translateY(<?php echo $translate_text; ?>px);
	transform: translateY(<?php echo $translate_text; ?>px);
}

.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-flat-btn.cartflows-bb__next-step-animate_from_bottom-btn:hover .cartflows-bb__next-step-button-text {
	-webkit-transform: translateY(-<?php echo $translate_text; ?>px);
	-moz-transform: translateY(-<?php echo $translate_text; ?>px);
	-ms-transform: translateY(-<?php echo $translate_text; ?>px);
	-o-transform: translateY(-<?php echo $translate_text; ?>px);
	transform: translateY(-<?php echo $translate_text; ?>px);
}
<?php endif; ?>

<?php if ( ! empty( $settings->text_color ) && 'default' !== $settings->style ) : ?>
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a *,
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited,
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited * {
	color: <?php echo $settings->text_color; ?>;
}
<?php endif; ?>

<?php if ( ! empty( $settings->bg_hover_color ) ) : ?>
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover {
	<?php if ( 'transparent' != $settings->style && 'gradient' != $settings->style && 'default' != $settings->style ) { ?>
		background: <?php echo $settings->bg_hover_color; ?>;
	<?php } ?>
	<?php if ( 'default' !== $settings->style ) { ?>
		border: <?php echo $border_size; ?>px solid <?php echo $border_hover_color; ?>;
	<?php } ?>
	<?php if ( 'gradient' == $settings->style ) : // Gradient. ?>
	background: -moz-linear-gradient(top,  <?php echo $bg_hover_grad_start; ?> 0%, <?php echo $settings->bg_hover_color; ?> 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $bg_hover_grad_start; ?>), color-stop(100%,<?php echo $settings->bg_hover_color; ?>)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  <?php echo $bg_hover_grad_start; ?> 0%,<?php echo $settings->bg_hover_color; ?> 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  <?php echo $bg_hover_grad_start; ?> 0%,<?php echo $settings->bg_hover_color; ?> 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  <?php echo $bg_hover_grad_start; ?> 0%,<?php echo $settings->bg_hover_color; ?> 100%); /* IE10+ */
	background: linear-gradient(to bottom,  <?php echo $bg_hover_grad_start; ?> 0%,<?php echo $settings->bg_hover_color; ?> 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bg_hover_grad_start; ?>', endColorstr='<?php echo $settings->bg_hover_color; ?>',GradientType=0 ); /* IE6-9 */
	<?php endif; ?>
}
<?php endif; ?>

<?php if ( ! empty( $settings->text_hover_color ) && 'default' !== $settings->style ) : ?>
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover,
.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:hover * {
	color: <?php echo $settings->text_hover_color; ?>;
}
<?php endif; ?>

<?php
// Responsive button Alignment.
if ( $global_settings->responsive_enabled ) :
	?>
	@media ( max-width: <?php echo $global_settings->medium_breakpoint . 'px'; ?> ) {
		<?php if ( 'default' === $settings->style ) { ?>
			.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
			.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
				<?php
				if ( isset( $settings->button_padding_dimension_top_medium ) ) {
					echo 'padding-top:' . $settings->button_padding_dimension_top_medium . 'px;';
				}
				if ( isset( $settings->button_padding_dimension_bottom_medium ) ) {
					echo 'padding-bottom:' . $settings->button_padding_dimension_bottom_medium . 'px;';
				}
				if ( isset( $settings->button_padding_dimension_left_medium ) ) {
					echo 'padding-left:' . $settings->button_padding_dimension_left_medium . 'px;';
				}
				if ( isset( $settings->button_padding_dimension_right_medium ) ) {
					echo 'padding-right:' . $settings->button_padding_dimension_right_medium . 'px;';
				}
				?>
			}
		<?php } ?>
	}
	@media ( max-width: <?php echo $global_settings->responsive_breakpoint; ?>px ) {
		.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap.cartflows-bb__next-step-creative-button-reponsive-<?php echo $settings->mob_align; ?> {
			text-align: <?php echo $settings->mob_align; ?>;
		}
		<?php if ( 'default' === $settings->style ) { ?>
			.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a,
			.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a:visited {
				<?php
				if ( isset( $settings->button_padding_dimension_top_responsive ) ) {
					echo 'padding-top:' . $settings->button_padding_dimension_top_responsive . 'px;';
				}
				if ( isset( $settings->button_padding_dimension_bottom_responsive ) ) {
					echo 'padding-bottom:' . $settings->button_padding_dimension_bottom_responsive . 'px;';
				}
				if ( isset( $settings->button_padding_dimension_left_responsive ) ) {
					echo 'padding-left:' . $settings->button_padding_dimension_left_responsive . 'px;';
				}
				if ( isset( $settings->button_padding_dimension_right_responsive ) ) {
					echo 'padding-right:' . $settings->button_padding_dimension_right_responsive . 'px;';
				}
				?>
			}
		<?php } ?>
	}
<?php endif; ?>

<?php /* Transparent New Style CSS*/ ?>
<?php
if ( ! empty( $settings->style ) && 'transparent' == $settings->style ) {
	?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-none-btn:hover{
		<?php
		if ( 'none' == $settings->transparent_button_options ) {
			if ( 'border' == $settings->hover_attribute ) {
				?>
			border-color:<?php echo $settings->bg_hover_color; ?>;
				<?php
			} else {
				?>
			background:<?php echo $settings->bg_hover_color; ?>;
				<?php
			}
		} else {
			?>
		background:<?php echo $settings->bg_hover_color; ?>;
			<?php
		}
		?>
	}
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-none-btn:hover .cartflows-bb__next-step-creative-button-icon {
		<?php if ( '' != $settings->text_hover_color && 'FFFFFF' != $settings->text_hover_color && 'none' == $settings->transparent_button_options ) { ?>
			color: <?php echo $settings->text_hover_color; ?>
		<?php } else { ?>
			color: <?php echo $settings->text_color; ?>;
		<?php } ?>
	}
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-none-btn:hover .cartflows-bb__next-step-creative-button-text {
		<?php if ( '' != $settings->text_hover_color && 'FFFFFF' != $settings->text_hover_color && 'none' == $settings->transparent_button_options ) { ?>
			color: <?php echo $settings->text_hover_color; ?>
		<?php } else { ?>
			color: <?php echo $settings->text_color; ?>;
		<?php } ?>
	}
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fade-btn:hover{
		background: <?php echo $settings->bg_hover_color; ?>;
	}

	/*transparent-fill-top*/
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-top-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		height: 100%;
	}

	/*transparent-fill-bottom*/
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-bottom-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		height: 100%;
	}

	/*transparent-fill-left*/
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-left-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		width: 100%;
	}
	/*transparent-fill-right*/
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-right-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		width: 100%;
	}

	/*transparent-fill-center*/
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-center-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		height: calc( 100% + <?php echo $border_size . 'px'; ?> );
		width: calc( 100% + <?php echo $border_size . 'px'; ?> );
	}

	/* transparent-fill-diagonal */
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-diagonal-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		height: 260%;
	}

	/*transparent-fill-horizontal*/
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-transparent-fill-horizontal-btn:hover:after{
		background: <?php echo $settings->bg_hover_color; ?>;
		height: calc( 100% + <?php echo $border_size . 'px'; ?> );
		width: calc( 100% + <?php echo $border_size . 'px'; ?> );
	}



	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-transparent-fill-diagonal-btn:hover {
		background: none;
	}

	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-<?php echo $settings->transparent_button_options; ?>-btn:hover .cartflows-bb__next-step-creative-button-text,
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-transparent-btn.cartflows-bb__next-step-<?php echo $settings->transparent_button_options; ?>-btn:hover i{
		color: <?php echo $settings->text_hover_color; ?>;

		position: relative;
		z-index: 9;
	}
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-<?php echo $settings->transparent_button_options; ?>-btn:hover .cartflows-bb__next-step-creative-button-icon {
		color: <?php echo $settings->text_hover_color; ?>;
		position: relative;
		z-index: 9;
	}
	<?php
}
?>

<?php /* 3D New Style CSS*/ ?>
<?php
if ( ! empty( $settings->style ) && 'threed' == $settings->style ) {
	?>
	<?php /* 3D Move Down*/ ?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_down-btn {
		<?php
		$hex_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_shadow_color, 10, 'darken' );
		?>
		box-shadow: 0 6px <?php echo $shadow_color; ?>;
	}
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_down-btn:hover{
		<?php
		$hex_hover_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_hover_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_hover_shadow_color, 10, 'darken' );
		?>
		top: 2px;
		box-shadow: 0 4px <?php echo $shadow_color; ?>;
	}

	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_down-btn:active{
		box-shadow:none!important;
		-webkit-transition:all 50ms linear;
		-moz-transition:all 50ms linear;
		transition:all 50ms linear;
		top: 6px;
	}


	<?php /* 3D Move Up*/ ?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_up-btn{
		<?php

		$hex_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_shadow_color, 10, 'darken' );
		?>
		box-shadow: 0 -6px <?php echo $shadow_color; ?>;
	}
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_up-btn:hover{
		<?php
		$hex_hover_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_hover_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_hover_shadow_color, 10, 'darken' );
		?>
		top: -2px;
		box-shadow: 0 -4px <?php echo $shadow_color; ?>;
	}

	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_up-btn:active{
		box-shadow:none!important;
		-webkit-transition:all 50ms linear;
		-moz-transition:all 50ms linear;
		transition:all 50ms linear;
		top: -6px;
	}

	<?php /* 3D Move Left*/ ?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_left-btn{
		<?php
		$hex_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_shadow_color, 10, 'darken' );
		?>
		box-shadow: -6px 0 <?php echo $shadow_color; ?>;
	}
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_left-btn:hover{
		<?php
		$hex_hover_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_hover_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_hover_shadow_color, 10, 'darken' );
		?>
		left: -2px;
		box-shadow: -4px 0 <?php echo $shadow_color; ?>;
	}

	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_left-btn:active {
		box-shadow:none!important;
		-webkit-transition:all 50ms linear;
		-moz-transition:all 50ms linear;
		transition:all 50ms linear;
		left: -6px;
	}


	<?php /* 3D Move Right*/ ?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_right-btn{
		<?php
		$hex_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_shadow_color, 10, 'darken' );
		?>
		box-shadow: 6px 0 <?php echo $shadow_color; ?>;
	}

	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_right-btn:hover{
		<?php
		$hex_hover_shadow_color = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_hover_color );

		$shadow_color = '#' . FLBuilderColor::adjust_brightness( $hex_hover_shadow_color, 10, 'darken' );
		?>
		left: 2px;
		box-shadow: 4px 0 <?php echo $shadow_color; ?>;
	}

	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-threed_right-btn:active {
		box-shadow:none!important;
		-webkit-transition:all 50ms linear;
		-moz-transition:all 50ms linear;
		transition:all 50ms linear;
		left: 6px;
	}

	<?php /* Animate Background Color */ ?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-<?php echo $settings->threed_button_options; ?>-btn:hover:after{
		<?php
		$hex_bg_color     = Cartflows_BB_Helper::cartflows_bb_parse_color_to_hex( $settings->bg_hover_color );
		$background_color = '#' . FLBuilderColor::adjust_brightness( $hex_bg_color, 10, 'darken' );
		?>
		background: <?php echo $background_color; ?>;
	}


	<?php /* Text Color*/ ?>
	.fl-node-<?php echo $id; ?> a.cartflows-bb__next-step-creative-threed-btn.cartflows-bb__next-step-<?php echo $settings->threed_button_options; ?>-btn:hover .cartflows-bb__next-step-creative-button-text{
		color: <?php echo $settings->text_hover_color; ?>;
	}

	<?php /* 3D Padding for Shadow */ ?>
	.fl-node-<?php echo $id; ?> .cartflows-bb__next-step-creative-button-wrap {
		<?php if ( 'threed_down' == $settings->threed_button_options ) : ?>
			padding-bottom: 6px;
		<?php elseif ( 'threed_up' == $settings->threed_button_options ) : ?>
			padding-top: 6px;
		<?php elseif ( 'threed_left' == $settings->threed_button_options ) : ?>
			padding-left: 6px;
		<?php elseif ( 'threed_right' == $settings->threed_button_options ) : ?>
			padding-right: 6px;
		<?php endif; ?>

	}
	<?php
}
?>
