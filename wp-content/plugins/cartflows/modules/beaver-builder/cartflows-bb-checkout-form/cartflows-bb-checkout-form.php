<?php
/**
 * Checkout Form Module for Beaver Builder
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checkout Form Module for Beaver Builder
 *
 * @since 1.6.15
 */
class Cartflows_BB_Checkout_Form extends FLBuilderModule {

	/**
	 * Constructor function for the module. You must pass the
	 * name, description, dir and url in an array to the parent class.
	 *
	 * @method __construct
	 */
	public function __construct() {

		$step_type          = Cartflows_BB_Helper::cartflows_bb_step_type();
		$is_bb_setting_page = Cartflows_BB_Helper::wcf_is_bb_setting_page();
		$is_enabled         = ( wcf()->is_woo_active && ( 'checkout' === $step_type || $is_bb_setting_page ) ) ? true : false;

		parent::__construct(
			array(
				'name'            => __( 'Checkout Form', 'cartflows' ),
				'description'     => __( 'Checkout Form.', 'cartflows' ),
				'category'        => __( 'Cartflows Modules', 'cartflows' ),
				'group'           => __( 'Cartflows Modules', 'cartflows' ),
				'dir'             => CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-checkout-form/',
				'url'             => CARTFLOWS_URL . 'modules/beaver-builder/cartflows-bb-checkout-form/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-checkout-form.svg',
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

		if ( '' !== $icon && file_exists( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-checkout-form/icon/' . $icon ) ) {
			// file_get_contents is fine for local files. https://github.com/WordPress/WordPress-Coding-Standards/pull/1374/files#diff-400e43bc09c24262b43f26fce487fdabR43-R52.
			return file_get_contents( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-checkout-form/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

	/**
	 * Dynamic options of module and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		if ( ! _is_cartflows_pro() ) {
			add_filter(
				'cartflows_checkout_meta_wcf-checkout-layout',
				function ( $value ) {

					$value = 'modern-checkout';

					return $value;
				},
				10,
				1
			);
		}

		$settings = $this->settings;

		$checkout_fields = array(
			// Input Fields.
			array(
				'filter_slug'  => 'wcf-fields-skins',
				'setting_name' => 'input_skins',
			),
			array(
				'filter_slug'  => 'wcf-checkout-layout',
				'setting_name' => 'checkout_layout',
			),
		);

		if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

			foreach ( $checkout_fields as $key => $field ) {

				$setting_name  = $field['setting_name'];
				$setting_value = $settings->$setting_name;

				if ( '' !== $setting_value ) {

					add_filter(
						'cartflows_checkout_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_value ) {

							$value = $setting_value;

							return $value;
						},
						10,
						1
					);
				}
			}
		}

		do_action( 'cartflows_bb_checkout_options_filters', $settings );

	}

	/**
	 * Function to get layout types.
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public static function get_layout_types() {

		$layout_options = array();

		if ( ! _is_cartflows_pro() ) {
			$layout_options = array(
				'modern-checkout'    => __( 'Modern Checkout', 'cartflows' ),
				'modern-one-column'  => __( 'Modern One Column', 'cartflows' ),
				'one-column'         => __( 'One Column', 'cartflows' ),
				'two-column'         => __( 'Two Column', 'cartflows' ),
				'multistep-checkout' => __( 'MultiStep Checkout ( PRO )', 'cartflows' ),
				'two-step'           => __( 'Two Step ( PRO )', 'cartflows' ),
			);
		} else {
			$layout_options = array(
				'modern-checkout'    => __( 'Modern Checkout', 'cartflows' ),
				'modern-one-column'  => __( 'Modern One Column', 'cartflows' ),
				'multistep-checkout' => __( 'MultiStep Checkout', 'cartflows' ),
				'one-column'         => __( 'One Column', 'cartflows' ),
				'two-column'         => __( 'Two Column', 'cartflows' ),
				'two-step'           => __( 'Two Step', 'cartflows' ),
			);
		}

		return $layout_options;
	}

	/**
	 * Function to get skin types.
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public static function get_skin_types() {

		$skin_options = array(
			'default'      => __( 'Default', 'cartflows' ),
			'modern-label' => __( 'Modern Labels', 'cartflows' ),
		);

		return $skin_options;
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_BB_Checkout_Form',
	array(
		'general' => array(
			'title'    => __( 'General', 'cartflows' ),
			'sections' => array(
				'layout' => array(
					'title'  => 'Layout',
					'fields' => array(
						'checkout_layout' => array(
							'type'        => 'select',
							'label'       => __( 'Select Layout', 'cartflows' ),
							/* translators: %s: link */
							'description' => ! _is_cartflows_pro() ? sprintf( __( 'The PRO layout options are available in the CartFlows Pro. %1$s  Upgrade Now! %2$s', 'cartflows' ), '<a href="https://cartflows.com/" target="blank" class="cartflows-bb-link">', '</a>' ) : '',
							'default'     => 'modern-checkout',
							'options'     => Cartflows_BB_Checkout_Form::get_layout_types(),
							'preview'     => array(
								'type' => 'refresh',
							),
						),
					),
				),
			),
		),
		'style'   => array(
			'title'    => __( 'Style', 'cartflows' ),
			'sections' => array(
				'global_style'  => array(
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
						'global_text_color'    => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form, .wcf-embed-checkout-form #payment .woocommerce-privacy-policy-text p',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'global_typography'    => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form, .cartflows-bb__checkout-form .wcf-embed-checkout-form',
							),
						),
					),
				),
				'heading'       => array(
					'title'  => __( 'Heading', 'cartflows' ),
					'fields' => array(
						'heading_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form .woocommerce h3, .wcf-embed-checkout-form .woocommerce h3 span, .wcf-embed-checkout-form .woocommerce-checkout #order_review_heading, .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-name, .wcf-embed-checkout-form .woocommerce .col2-set .col-1 h3,
								.wcf-embed-checkout-form .woocommerce .col2-set .col-2 h3',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'heading_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form .woocommerce h3, .wcf-embed-checkout-form .woocommerce h3 span, .wcf-embed-checkout-form .woocommerce-checkout #order_review_heading, .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-name, .wcf-embed-checkout-form .woocommerce .col2-set .col-1 h3,
								.wcf-embed-checkout-form .woocommerce .col2-set .col-2 h3',
							),
						),
					),
				),
				'input_style'   => array(
					'title'  => __( 'Input Fields', 'cartflows' ),
					'fields' => array(
						'input_skins'           => array(
							'type'    => 'select',
							'label'   => __( 'Style', 'cartflows' ),

							'default' => 'modern-label',
							'options' => Cartflows_BB_Checkout_Form::get_skin_types(),
							'preview' => array(
								'type' => 'refresh',
							),
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
								'selector' => '.woocommerce-checkout label, .woocommerce form p.form-row label',
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
								'selector' => '#order_review .wcf-custom-coupon-field input[type="text"],
								.woocommerce form .form-row input.input-text,
								.woocommerce form .form-row textarea,
								.select2-container--default .select2-selection--single,
								.woocommerce form .form-row select.select,
								.woocommerce form .form-row select',
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
								'selector' => '#order_review .wcf-custom-coupon-field input[type="text"],
								.woocommerce form .form-row input.input-text,
								.woocommerce form .form-row textarea,
								.select2-container--default .select2-selection--single,
								.woocommerce form .form-row select,
								.wcf-embed-checkout-form .woocommerce form .form-row select,
								.wcf-embed-checkout-form ::placeholder,
								.wcf-embed-checkout-form ::-webkit-input-placeholder',
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
								'selector' => '.wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form .select2-container--default .select2-selection--single, .wcf-embed-checkout-form .woocommerce form .form-row select.select, .wcf-embed-checkout-form .woocommerce .col2-set .col-1,  .wcf-embed-checkout-form .woocommerce .col2-set .col-2, .wcf-embed-checkout-form .woocommerce form p.form-row label, .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:checked + label, .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:not(:checked) + label, .wcf-embed-checkout-form .woocommerce form .form-row select, .wcf-embed-checkout-form .woocommerce #order_review .wcf-custom-coupon-field input[type="text"]',
							),
						),
						'input_border_style'    => array(
							'type'    => 'select',
							'label'   => __( 'Border Style', 'cartflows' ),
							'default' => 'solid',
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
									'fields' => array( 'input_border_width', 'input_border_radius', 'input_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'input_border_width', 'input_border_radius', 'input_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'input_border_width', 'input_border_radius', 'input_border_color' ),
								),
								'double' => array(
									'fields' => array( 'input_border_width', 'input_border_radius', 'input_border_color' ),
								),
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
							'default'     => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '#order_review .wcf-custom-coupon-field input[type="text"],
								.woocommerce form .form-row input.input-text,
								.woocommerce form .form-row textarea,
								.select2-container--default .select2-selection--single,
								.woocommerce form .form-row select.select,
								.woocommerce form .form-row select',
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
								'selector' => '#order_review .wcf-custom-coupon-field input[type="text"],
								.woocommerce form .form-row input.input-text,
								.woocommerce form .form-row textarea,
								.select2-container--default .select2-selection--single,
								.woocommerce form .form-row select.select,
								.woocommerce form .form-row select',
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
								'selector' => '#order_review .wcf-custom-coupon-field input[type="text"],
								.woocommerce form .form-row input.input-text,
								.woocommerce form .form-row textarea,
								.select2-container--default .select2-selection--single,
								.woocommerce form .form-row select.select,
								.woocommerce form .form-row select',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
				'button_style'  => array(
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
								'selector' => '.woocommerce #order_review button,
								.woocommerce form.woocommerce-form-login .form-row button,
								.woocommerce #order_review button.wcf-btn-small,
								.woocommerce-checkout form.woocommerce-form-login .button,
								.woocommerce-checkout form.checkout_coupon .button,
								form.checkout_coupon .button,
								.wcf-embed-checkout-form-nav-btns .wcf-next-button,
								.wcf-embed-checkout-form-nav-btns a.wcf-next-button,
								button.wcf-pre-checkout-offer-btn',
								'property' => 'color',
								'unit'     => 'px',
							),
							'preview'    => array(
								'type' => 'refresh',
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
								'selector' => '.woocommerce-checkout form.login .button:hover,
								.woocommerce-checkout form.checkout_coupon .button:hover,
								.woocommerce #payment #place_order:hover,
								.woocommerce #order_review button.wcf-btn-small:hover,
								form.checkout_coupon .button:hover,
								.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover,
								.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button:hover,
								button.wcf-pre-checkout-offer-btn:hover',
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
								'selector' => '.woocommerce #order_review button,
								.woocommerce form.woocommerce-form-login .form-row button,
								.woocommerce #order_review button.wcf-btn-small,
								.woocommerce-checkout form.woocommerce-form-login .button,
								.woocommerce-checkout form.checkout_coupon .button,
								form.checkout_coupon .button,
								.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
								.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
								button.wcf-pre-checkout-offer-btn',
								'property' => 'background-color',
								'unit'     => 'px',
							),
							'preview'    => array(
								'type' => 'refresh',
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
								'selector' => '.woocommerce-checkout form.login .button:hover,
								.woocommerce-checkout form.checkout_coupon .button:hover,
								.woocommerce #payment #place_order:hover,
								.woocommerce #order_review button.wcf-btn-small:hover,
								form.checkout_coupon .button:hover,
								.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover,
								.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button:hover,
								button.wcf-pre-checkout-offer-btn:hover',
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
								'selector' => '.wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button,  .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,  .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
								body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
							),
							'preview'    => array(
								'type' => 'refresh',
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
								'selector' => '.woocommerce #order_review button,
								.woocommerce form.woocommerce-form-login .form-row button,
								.woocommerce #order_review button.wcf-btn-small,
								.woocommerce-checkout form.woocommerce-form-login .button,
								.woocommerce-checkout form.checkout_coupon .button,
								form.checkout_coupon .button,
								.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
								.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
								button.wcf-pre-checkout-offer-btn',
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
								'selector' => '.woocommerce #order_review button,
									.woocommerce form.woocommerce-form-login .form-row button,
									.woocommerce #order_review button.wcf-btn-small,
									.woocommerce-checkout form.woocommerce-form-login .button,
									.woocommerce-checkout form.checkout_coupon .button,
									form.checkout_coupon .button,
									.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
									.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
									button.wcf-pre-checkout-offer-btn',
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
								'selector' => '.woocommerce #order_review button,
									.woocommerce form.woocommerce-form-login .form-row button,
									.woocommerce #order_review button.wcf-btn-small,
									.woocommerce-checkout form.woocommerce-form-login .button,
									.woocommerce-checkout form.checkout_coupon .button,
									form.checkout_coupon .button,
									.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
									.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
									button.wcf-pre-checkout-offer-btn',
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
								'selector' => '.woocommerce-checkout form.login .button:hover,
									.woocommerce-checkout form.checkout_coupon .button:hover,
									.woocommerce #payment #place_order:hover,
									.woocommerce #order_review button.wcf-btn-small:hover,
									form.checkout_coupon .button:hover,
									.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover,
									.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button:hover,
									button.wcf-pre-checkout-offer-btn:hover',
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
								'selector' => '.woocommerce #order_review button,
									.woocommerce form.woocommerce-form-login .form-row button,
									.woocommerce #order_review button.wcf-btn-small,
									.woocommerce-checkout form.woocommerce-form-login .button,
									.woocommerce-checkout form.checkout_coupon .button,
									form.checkout_coupon .button,
									.woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
									.woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
									button.wcf-pre-checkout-offer-btn',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
				'payment_style' => array(
					'title'  => __( 'Payment Section', 'cartflows' ),
					'fields' => array(
						'payment_section_text_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-checkout #payment, .woocommerce-checkout #payment label, .woocommerce-checkout #payment a',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'payment_section_desc_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Description Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-checkout #payment div.payment_box',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'payment_info_bg_color'            => array(
							'type'        => 'color',
							'label'       => __( 'Information Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'payment_section_bg_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Section Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-checkout #payment ul.payment_methods',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'payment_section_padding_dimension' => array(
							'type'       => 'dimension',
							'label'      => __( 'Section Padding', 'cartflows' ),
							'slider'     => true,
							'units'      => array( 'px' ),
							'responsive' => false,
							'preview'    => array(
								'type'      => 'css',
								'selector'  => '.wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods',
								'property'  => 'padding',
								'unit'      => 'px',
								'important' => true,
							),
						),
						'payment_section_margin_dimension' => array(
							'type'       => 'dimension',
							'label'      => __( 'Margin', 'cartflows' ),
							'slider'     => true,
							'units'      => array( 'px' ),
							'responsive' => false,
							'preview'    => array(
								'type'      => 'css',
								'selector'  => '.wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods',
								'property'  => 'margin',
								'unit'      => 'px',
								'important' => true,
							),
						),
						'payment_section_border_radius'    => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-checkout #payment ul.payment_methods',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
				'error_style'   => array(
					'title'  => __( 'Field Validation & Error Messages', 'cartflows' ),
					'fields' => array(
						'field_label_color'        => array(
							'type'        => 'color',
							'label'       => __( 'Field Label Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-checkout .woocommerce-invalid label,
								.wcf-embed-checkout-form .woocommerce form p.form-row.woocommerce-invalid label,
								.woocommerce form .form-row.woocommerce-invalid label',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'error_field_border_color' => array(
							'type'        => 'color',
							'label'       => __( 'Field Border Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.select2-container--default.field-required .select2-selection--single,
								.woocommerce form .form-row input.input-text.field-required,
								.woocommerce form .form-row textarea.input-text.field-required,
								.woocommerce #order_review .input-text.field-required
								.woocommerce form .form-row.woocommerce-invalid .select2-container,
								.woocommerce form .form-row.woocommerce-invalid input.input-text,
								.woocommerce form .form-row.woocommerce-invalid select',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'error_text_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Error Message Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce .woocommerce-error,
								.woocommerce .woocommerce-NoticeGroup .woocommerce-error,
								.woocommerce .woocommerce-notices-wrapper .woocommerce-error',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'error_bg_color'           => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce .woocommerce-error,
								.woocommerce .woocommerce-NoticeGroup .woocommerce-error,
								.woocommerce .woocommerce-notices-wrapper .woocommerce-error',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'error_border_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Border Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce .woocommerce-error,
								.woocommerce .woocommerce-NoticeGroup .woocommerce-error,
								.woocommerce .woocommerce-notices-wrapper .woocommerce-error',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
					),
				),
				'column_style'  => array(
					'title'  => __( 'Order Review', 'cartflows' ),
					'fields' => array(
						'column_text_color' => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table th, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table td',
								'property' => 'color',
							),
						),
						'column_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
					),
				),
			),
		),
	)
);
