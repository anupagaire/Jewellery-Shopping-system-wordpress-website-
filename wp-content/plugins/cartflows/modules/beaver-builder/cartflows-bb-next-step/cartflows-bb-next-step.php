<?php
/**
 * Next Step button Module for Beaver Builder
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Next Step button Module for Beaver Builder
 *
 * @since 1.6.15
 */
class Cartflows_BB_Next_Step extends FLBuilderModule {
	/**
	 * Constructor function for the module. You must pass the
	 * name, description, dir and url in an array to the parent class.
	 *
	 * @method __construct
	 */
	public function __construct() {

		$step_type          = Cartflows_BB_Helper::cartflows_bb_step_type();
		$is_bb_setting_page = Cartflows_BB_Helper::wcf_is_bb_setting_page();
		$is_enabled         = ( 'landing' === $step_type || $is_bb_setting_page ) ? true : false;

		parent::__construct(
			array(
				'name'            => __( 'Next Step Button', 'cartflows' ),
				'description'     => __( 'A simple next step button.', 'cartflows' ),
				'category'        => __( 'Cartflows Modules', 'cartflows' ),
				'group'           => __( 'Cartflows Modules', 'cartflows' ),
				'dir'             => CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-next-step/',
				'url'             => CARTFLOWS_URL . 'modules/beaver-builder/cartflows-bb-next-step/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-next-step.svg',
				'enabled'         => $is_enabled,
			)
		);
	}

	/**
	 * Function to get the icon for the module
	 *
	 * @method get_icons
	 * @param string $icon gets the icon for the module.
	 */
	public function get_icon( $icon = '' ) {

		if ( '' !== $icon && file_exists( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-next-step/icon/' . $icon ) ) {
			// file_get_contents is fine for local files. https://github.com/WordPress/WordPress-Coding-Standards/pull/1374/files#diff-400e43bc09c24262b43f26fce487fdabR43-R52.
			return file_get_contents( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-next-step/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

	/**
	 * Function that gets the class names.
	 *
	 * @method get_classname
	 */
	public function get_classname() {
		$classname = 'cartflows-bb__next-step-button-wrap cartflows-bb__next-step-button-wrap cartflows-bb__next-step-creative-button-wrap';

		if ( ! empty( $this->settings->width ) ) {
			$classname .= ' cartflows-bb__next-step-button-width-' . $this->settings->width;
			$classname .= ' cartflows-bb__next-step-creative-button-width-' . $this->settings->width;
		}
		if ( ! empty( $this->settings->align ) ) {
			$classname .= ' cartflows-bb__next-step-button-' . $this->settings->align;
			$classname .= ' cartflows-bb__next-step-creative-button-' . $this->settings->align;
		}
		if ( ! empty( $this->settings->mob_align ) ) {
			$classname .= ' cartflows-bb__next-step-button-reponsive-' . $this->settings->mob_align;
			$classname .= ' cartflows-bb__next-step-creative-button-reponsive-' . $this->settings->mob_align;
		}
		if ( ! empty( $this->settings->icon ) ) {
			$classname .= ' cartflows-bb__next-step-button-has-icon';
			$classname .= ' cartflows-bb__next-step-creative-button-has-icon';
		}

		if ( empty( $this->settings->text ) ) {
			$classname .= ' cartflows-bb__next-step-creative-button-icon-no-text';
		}

		return $classname;
	}

	/**
	 * Function that gets the button styling.
	 *
	 * @method get_button_style
	 */
	public function get_button_style() {
		$btn_style = '';

		if ( ! empty( $this->settings->style ) && 'transparent' == $this->settings->style ) {
			if ( isset( $this->settings->transparent_button_options ) && ! empty( $this->settings->transparent_button_options ) ) {
				$btn_style .= ' cartflows-bb__next-step-' . $this->settings->transparent_button_options . '-btn';
			}
		}

		if ( ! empty( $this->settings->style ) && 'threed' == $this->settings->style ) {
			if ( isset( $this->settings->threed_button_options ) && ! empty( $this->settings->threed_button_options ) ) {
				$btn_style .= ' cartflows-bb__next-step-' . $this->settings->threed_button_options . '-btn';
			}
		}

		if ( ! empty( $this->settings->style ) && 'flat' == $this->settings->style ) {
			if ( isset( $this->settings->flat_button_options ) && ! empty( $this->settings->flat_button_options ) ) {
				$btn_style .= ' cartflows-bb__next-step-' . $this->settings->flat_button_options . '-btn';
			}
		}

		return $btn_style;
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_BB_Next_Step',
	array(

		'general'             => array(
			'title'    => __( 'General', 'cartflows' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'text'          => array(
							'type'        => 'text',
							'label'       => __( 'Text', 'cartflows' ),
							'default'     => __( 'Next Step', 'cartflows' ),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.fl-button-text',
							),
							'connections' => array( 'string' ),
						),
						'icon'          => array(
							'type'        => 'icon',
							'label'       => __( 'Icon', 'cartflows' ),
							'show_remove' => true,
							'show'        => array(
								'fields' => array( 'icon_position', 'icon_spacing', 'icon_size' ),
							),
							'preview'     => array(
								'type' => 'refresh',
							),
						),
						'icon_position' => array(
							'type'    => 'select',
							'label'   => __( 'Icon Position', 'cartflows' ),
							'default' => 'before',
							'options' => array(
								'before' => __( 'Before Text', 'cartflows' ),
								'after'  => __( 'After Text', 'cartflows' ),
							),
							'preview' => array(
								'type' => 'refresh',
							),
						),
						'icon_spacing'  => array(
							'type'      => 'unit',
							'label'     => __( 'Icon Spacing', 'cartflows' ),
							'slider'    => true,
							'units'     => array( 'px' ),
							'maxlength' => '30',
							'size'      => '5',
						),
					),
				),
			),
		),
		'style'               => array(
			'title'    => __( 'Style', 'cartflows' ),
			'sections' => array(
				'style'      => array(
					'title'  => __( 'Style', 'cartflows' ),
					'fields' => array(
						'style'                      => array(
							'type'    => 'select',
							'label'   => __( 'Type', 'cartflows' ),
							'default' => 'default',
							'class'   => 'creative_button_styles',
							'options' => array(
								'default'     => __( 'Default', 'cartflows' ),
								'flat'        => __( 'Flat', 'cartflows' ),
								'gradient'    => __( 'Gradient', 'cartflows' ),
								'transparent' => __( 'Transparent', 'cartflows' ),
								'threed'      => __( '3D', 'cartflows' ),
							),
							'toggle'  => array(
								'default' => array(
									'fields' => array( 'button_padding_dimension', 'button_border', 'border_hover_color' ),
								),
							),
						),
						'border_size'                => array(
							'type'        => 'unit',
							'label'       => __( 'Border Size', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '5',
							'placeholder' => '2',
						),
						'transparent_button_options' => array(
							'type'    => 'select',
							'label'   => __( 'Hover Styles', 'cartflows' ),
							'default' => 'transparent-fade',
							'options' => array(
								'none'                    => __( 'None', 'cartflows' ),
								'transparent-fade'        => __( 'Fade Background', 'cartflows' ),
								'transparent-fill-top'    => __( 'Fill Background From Top', 'cartflows' ),
								'transparent-fill-bottom' => __( 'Fill Background From Bottom', 'cartflows' ),
								'transparent-fill-left'   => __( 'Fill Background From Left', 'cartflows' ),
								'transparent-fill-right'  => __( 'Fill Background From Right', 'cartflows' ),
								'transparent-fill-center' => __( 'Fill Background Vertical', 'cartflows' ),
								'transparent-fill-diagonal' => __( 'Fill Background Diagonal', 'cartflows' ),
								'transparent-fill-horizontal' => __( 'Fill Background Horizontal', 'cartflows' ),
							),
						),
						'threed_button_options'      => array(
							'type'    => 'select',
							'label'   => __( 'Hover Styles', 'cartflows' ),
							'default' => 'threed_down',
							'options' => array(
								'threed_down'    => __( 'Move Down', 'cartflows' ),
								'threed_up'      => __( 'Move Up', 'cartflows' ),
								'threed_left'    => __( 'Move Left', 'cartflows' ),
								'threed_right'   => __( 'Move Right', 'cartflows' ),
								'animate_top'    => __( 'Animate Top', 'cartflows' ),
								'animate_bottom' => __( 'Animate Bottom', 'cartflows' ),
							),
						),
						'flat_button_options'        => array(
							'type'    => 'select',
							'label'   => __( 'Hover Styles', 'cartflows' ),
							'default' => 'none',
							'options' => array(
								'none'                => __( 'None', 'cartflows' ),
								'animate_to_left'     => __( 'Appear Icon From Right', 'cartflows' ),
								'animate_to_right'    => __( 'Appear Icon From Left', 'cartflows' ),
								'animate_from_top'    => __( 'Appear Icon From Top', 'cartflows' ),
								'animate_from_bottom' => __( 'Appear Icon From Bottom', 'cartflows' ),
							),
						),
					),
				),
				'formatting' => array(
					'title'  => __( 'Structure', 'cartflows' ),
					'fields' => array(
						'width'                    => array(
							'type'    => 'select',
							'label'   => __( 'Width', 'cartflows' ),
							'default' => 'auto',
							'options' => array(
								'auto'   => _x( 'Auto', 'Width.', 'cartflows' ),
								'full'   => __( 'Full Width', 'cartflows' ),
								'custom' => __( 'Custom', 'cartflows' ),
							),
							'toggle'  => array(
								'auto'   => array(
									'fields' => array( 'align', 'mob_align' ),
								),
								'full'   => array(
									'fields' => array(),
								),
								'custom' => array(
									'fields' => array( 'align', 'mob_align', 'custom_width', 'custom_height', 'padding_top_bottom', 'padding_left_right' ),
								),
							),
						),
						'align'                    => array(
							'type'    => 'align',
							'label'   => __( 'Alignment', 'cartflows' ),
							'default' => 'center',
							'options' => array(
								'center' => __( 'Center', 'cartflows' ),
								'left'   => __( 'Left', 'cartflows' ),
								'right'  => __( 'Right', 'cartflows' ),
							),
						),
						'mob_align'                => array(
							'type'    => 'align',
							'label'   => __( 'Mobile Alignment', 'cartflows' ),
							'default' => 'center',
							'options' => array(
								'center' => __( 'Center', 'cartflows' ),
								'left'   => __( 'Left', 'cartflows' ),
								'right'  => __( 'Right', 'cartflows' ),
							),
						),
						'button_padding_dimension' => array(
							'type'       => 'dimension',
							'label'      => __( 'Padding', 'cartflows' ),
							'slider'     => true,
							'units'      => array( 'px' ),
							'responsive' => true,
							'preview'    => array(
								'type'      => 'css',
								'selector'  => '.cartflows-bb__next-step-creative-button-wrap a',
								'property'  => 'padding',
								'unit'      => 'px',
								'important' => true,
							),
						),
						'button_border'            => array(
							'type'    => 'border',
							'label'   => __( 'Border', 'cartflows' ),
							'slider'  => true,
							'units'   => array( 'px' ),
							'preview' => array(
								'type'      => 'css',
								'selector'  => '.cartflows-bb__next-step-creative-button-wrap a',
								'property'  => 'border',
								'unit'      => 'px',
								'important' => true,
							),
						),
						'border_hover_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Border Hover Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'custom_width'             => array(
							'type'      => 'unit',
							'label'     => __( 'Custom Width', 'cartflows' ),
							'default'   => '200',
							'maxlength' => '3',
							'size'      => '4',
							'slider'    => true,
							'units'     => array( 'px' ),
						),
						'custom_height'            => array(
							'type'      => 'unit',
							'label'     => __( 'Custom Height', 'cartflows' ),
							'default'   => '45',
							'maxlength' => '3',
							'size'      => '4',
							'slider'    => true,
							'units'     => array( 'px' ),
						),
						'padding_top_bottom'       => array(
							'type'        => 'unit',
							'label'       => __( 'Padding Top/Bottom', 'cartflows' ),
							'placeholder' => '0',
							'maxlength'   => '3',
							'size'        => '4',
							'slider'      => true,
							'units'       => array( 'px' ),
						),
						'padding_left_right'       => array(
							'type'        => 'unit',
							'label'       => __( 'Padding Left/Right', 'cartflows' ),
							'placeholder' => '0',
							'maxlength'   => '3',
							'size'        => '4',
							'slider'      => true,
							'units'       => array( 'px' ),
						),
						'border_radius'            => array(
							'type'      => 'unit',
							'label'     => __( 'Round Corners', 'cartflows' ),
							'maxlength' => '3',
							'size'      => '4',
							'slider'    => true,
							'units'     => array( 'px' ),
							'preview'   => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__next-step-creative-button-wrap a',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
				'colors'     => array(
					'title'  => __( 'Colors', 'cartflows' ),
					'fields' => array(
						'text_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__next-step-creative-button-wrap a',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'text_hover_color' => array(
							'type'        => 'color',
							'label'       => __( 'Text Hover Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__next-step-creative-button-wrap a:hover',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'bg_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'bg_hover_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Background Hover Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'hover_attribute'  => array(
							'type'    => 'select',
							'label'   => __( 'Apply Hover Color To', 'cartflows' ),
							'default' => 'bg',
							'options' => array(
								'border' => __( 'Border', 'cartflows' ),
								'bg'     => __( 'Background', 'cartflows' ),
							),
							'width'   => '75px',
						),

					),
				),
			),
		),
		'creative_typography' => array(
			'title'    => __( 'Typography', 'cartflows' ),
			'sections' => array(
				'typography' => array(
					'title'  => __( 'Button Settings', 'cartflows' ),
					'fields' => array(
						'icon_size'   => array(
							'type'      => 'unit',
							'label'     => __( 'Icon Size', 'cartflows' ),
							'slider'    => true,
							'units'     => array( 'px' ),
							'maxlength' => '30',
							'size'      => '5',
						),
						'button_typo' => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__next-step-creative-button',
							),
						),
					),
				),
			),
		),
	)
);
