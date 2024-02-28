<?php
/**
 * Optin Form Module for Beaver Builder
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Optin Form Module for Beaver Builder
 *
 * @since 1.6.15
 */
class Cartflows_BB_Optin_Form extends FLBuilderModule {

	/**
	 * Constructor function for the module. You must pass the
	 * name, description, dir and url in an array to the parent class.
	 *
	 * @method __construct
	 */
	public function __construct() {

		$step_type          = Cartflows_BB_Helper::cartflows_bb_step_type();
		$is_bb_setting_page = Cartflows_BB_Helper::wcf_is_bb_setting_page();
		$is_enabled         = ( wcf()->is_woo_active && ( 'optin' === $step_type || $is_bb_setting_page ) ) ? true : false;

		parent::__construct(
			array(
				'name'            => __( 'Optin Form', 'cartflows' ),
				'description'     => __( 'Optin Form.', 'cartflows' ),
				'category'        => __( 'Cartflows Modules', 'cartflows' ),
				'group'           => __( 'Cartflows Modules', 'cartflows' ),
				'dir'             => CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-optin-form/',
				'url'             => CARTFLOWS_URL . 'modules/beaver-builder/cartflows-bb-optin-form/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-optin-form.svg',
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

		if ( '' !== $icon && file_exists( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-optin-form/icon/' . $icon ) ) {
			// file_get_contents is fine for local files. https://github.com/WordPress/WordPress-Coding-Standards/pull/1374/files#diff-400e43bc09c24262b43f26fce487fdabR43-R52.
			return file_get_contents( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-optin-form/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

	/**
	 * Dynamic options of module and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		$settings = $this->settings;

		add_filter(
			'cartflows_optin_meta_wcf-input-fields-skins',
			function ( $value ) use ( $settings ) {

				$value = $settings->input_skins;

				return $value;
			},
			10,
			1
		);

		do_action( 'cartflows_bb_optin_options_filters', $this->settings );

	}

	/**
	 * Function to get skin types.
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public static function get_skin_types() {

		$skin_options = array(
			'default'         => __( 'Default', 'cartflows' ),
			'floating-labels' => __( 'Floating Labels', 'cartflows' ),
		);

		return $skin_options;
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_BB_Optin_Form',
	array(
		'style' => array(
			'title'    => __( 'Style', 'cartflows' ),
			'sections' => array(
				'global_style' => array(
					'title'  => __( 'Global', 'cartflows' ),
					'fields' => array(
						'global_primary_color' => array(
							'type'        => 'color',
							'label'       => __( 'Primary Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'global_typography'    => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout label, .wcf-optin-form .checkout.woocommerce-checkout input, .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
							),
						),
					),
				),
				'input_style'  => array(
					'title'  => __( 'Input Fields', 'cartflows' ),
					'fields' => array(
						'input_skins'           => array(
							'type'    => 'select',
							'label'   => __( 'Style', 'cartflows' ),
							'default' => 'default',
							'options' => Cartflows_BB_Optin_Form::get_skin_types(),
						),
						'label_color'           => array(
							'type'        => 'color',
							'label'       => __( 'Label Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout label',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'input_bgcolor'         => array(
							'type'        => 'color',
							'label'       => __( 'Field Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'input_color'           => array(
							'type'        => 'color',
							'label'       => __( 'Input Text / Placeholder Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'input_text_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout label, .cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
							),
						),
						'input_border_style'    => array(
							'type'    => 'select',
							'label'   => __( 'Border Style', 'cartflows' ),
							'default' => 'none',
							'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows' ),
							'options' => array(
								'none'   => __( 'None', 'cartflows' ),
								'solid'  => __( 'Solid', 'cartflows' ),
								'dashed' => __( 'Dashed', 'cartflows' ),
								'dotted' => __( 'Dotted', 'cartflows' ),
								'double' => __( 'Double', 'cartflows' ),
							),
							'toggle'  => array(
								'solid'  => array(
									'fields' => array( 'input_border_width', 'input_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'input_border_width', 'input_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'input_border_width', 'input_border_color' ),
								),
								'double' => array(
									'fields' => array( 'input_border_width', 'input_border_color' ),
								),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
								'property' => 'border-style',
							),
						),
						'input_border_width'    => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'input_border_color'    => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'input_border_radius'   => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-bb__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
				'button_style' => array(
					'title'  => __( 'Buttons', 'cartflows' ),
					'fields' => array(
						'button_text_color'         => array(
							'type'       => 'color',
							'label'      => __( 'Text Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'button_text_hover_color'   => array(
							'type'       => 'color',
							'label'      => __( 'Text Hover Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order:hover,
								.wcf-optin-form .woocommerce #order_review #payment button:hover',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'button_bg_color'           => array(
							'type'       => 'color',
							'label'      => __( 'Background Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'button_bg_hover_color'     => array(
							'type'       => 'color',
							'label'      => __( 'Background Hover Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order:hover,
								.wcf-optin-form .woocommerce #order_review #payment button:hover',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'button_typography'         => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
							),
						),
						'button_border_style'       => array(
							'type'    => 'select',
							'label'   => __( 'Border Style', 'cartflows' ),
							'default' => 'none',
							'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows' ),
							'options' => array(
								'none'   => __( 'None', 'cartflows' ),
								'solid'  => __( 'Solid', 'cartflows' ),
								'dashed' => __( 'Dashed', 'cartflows' ),
								'dotted' => __( 'Dotted', 'cartflows' ),
								'double' => __( 'Double', 'cartflows' ),
							),
							'toggle'  => array(
								'solid'  => array(
									'fields' => array( 'button_border_width', 'button_border_color', 'button_border_hover_color' ),
								),
								'dashed' => array(
									'fields' => array( 'button_border_width', 'button_border_color', 'button_border_hover_color' ),
								),
								'dotted' => array(
									'fields' => array( 'button_border_width', 'button_border_color', 'button_border_hover_color' ),
								),
								'double' => array(
									'fields' => array( 'button_border_width', 'button_border_color', 'button_border_hover_color' ),
								),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
								'property' => 'border-style',
							),
						),
						'button_border_width'       => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'button_border_color'       => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'button_border_hover_color' => array(
							'type'       => 'color',
							'label'      => __( 'Border Hover Color', 'cartflows' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order:hover,
								.wcf-optin-form .woocommerce #order_review #payment button:hover',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'button_border_radius'      => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order,
								.wcf-optin-form .woocommerce #order_review #payment button',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
			),
		),
	)
);
