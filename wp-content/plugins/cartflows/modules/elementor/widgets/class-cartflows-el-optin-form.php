<?php
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Optin Form Widget
 *
 * @since 1.6.15
 */
class CartFlows_Optin_Form extends Widget_Base {

	/**
	 * Module should load or not.
	 *
	 * @since 1.6.15
	 * @access public
	 * @param string $step_type Current step type.
	 *
	 * @return bool true|false.
	 */
	public static function is_enable( $step_type ) {

		if ( 'optin' === $step_type && wcf()->is_woo_active ) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.6.15
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'optin-form';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.6.15
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Optin Form', 'cartflows' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.6.15
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wcf-el-icon-optin-form';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.6.15
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'cartflows-widgets' );
	}

	/**
	 * Settings
	 *
	 * @since 1.6.15
	 * @var object $settings
	 */
	public static $settings;

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.6.15
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'cartflows', 'optin', 'form' );
	}

	/**
	 * Register cart controls controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_controls() {

		// Style Tab.
		$this->register_general_style_controls();
		$this->register_input_style_controls();
		$this->register_button_style_controls();
	}

	/**
	 * Function to get skin types.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function get_skin_types() {

		$skin_options = array(
			'default'         => __( 'Default', 'cartflows' ),
			'floating-labels' => __( 'Floating Labels', 'cartflows' ),
		);

		return $skin_options;
	}

	/**
	 * Register General Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_general_style_controls() {
		$this->start_controls_section(
			'section_general_style_fields',
			array(
				'label' => __( 'General', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'general_primary_color',
				array(
					'label'     => __( 'Primary Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'general_typography',
					'label'    => 'Typography',
					'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout label, {{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input, {{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Input Fields Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_input_style_controls() {
		$this->start_controls_section(
			'input_section',
			array(
				'label' => __( 'Input Fields', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'input_skins',
			array(
				'label'   => __( 'Style', 'cartflows' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => $this->get_skin_types(),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_text_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout label, {{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Label Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_bgcolor',
			array(
				'label'     => __( 'Field Background Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_color',
			array(
				'label'     => __( 'Input Text / Placeholder Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'input_border_style',
			array(
				'label'       => __( 'Border Style', 'cartflows' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => false,
				'default'     => '',
				'options'     => array(
					''       => __( 'Inherit', 'cartflows' ),
					'solid'  => __( 'Solid', 'cartflows' ),
					'double' => __( 'Double', 'cartflows' ),
					'dotted' => __( 'Dotted', 'cartflows' ),
					'dashed' => __( 'Dashed', 'cartflows' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input' => 'border-style: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'input_border_size',
			array(
				'label'      => __( 'Border Width', 'cartflows' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'input_border_color',
			array(
				'label'     => __( 'Border Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'input_radius',
			array(
				'label'      => __( 'Rounded Corners', 'cartflows' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Button Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_button_style_controls() {

		$this->start_controls_section(
			'button_section',
			array(
				'label' => __( 'Submit Button', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'buttons_typography',
					'label'    => 'Typography',
					'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review #payment button#place_order',
				)
			);

			$this->start_controls_tabs( 'tabs_button_style' );

				$this->start_controls_tab(
					'tab_button_normal',
					array(
						'label' => __( 'Normal', 'cartflows' ),
					)
				);

					$this->add_control(
						'button_text_color',
						array(
							'label'     => __( 'Text Color', 'cartflows' ),
							'type'      => Controls_Manager::COLOR,
							'default'   => '',
							'selectors' => array(
								'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order' => 'color: {{VALUE}};',
							),
						)
					);

					$this->add_group_control(
						Group_Control_Background::get_type(),
						array(
							'name'     => 'btn_background_color',
							'label'    => __( 'Background Color', 'cartflows' ),
							'types'    => array( 'classic', 'gradient' ),
							'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
						)
					);

					$this->add_group_control(
						Group_Control_Border::get_type(),
						array(
							'name'     => 'btn_border',
							'label'    => __( 'Border', 'cartflows' ),
							'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
						)
					);

					$this->add_responsive_control(
						'btn_border_radius',
						array(
							'label'      => __( 'Rounded Corners', 'cartflows' ),
							'type'       => Controls_Manager::DIMENSIONS,
							'size_units' => array( 'px', '%' ),
							'selectors'  => array(
								'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							),
						)
					);

					$this->add_group_control(
						Group_Control_Box_Shadow::get_type(),
						array(
							'name'     => 'button_box_shadow',
							'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
						)
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_button_hover',
					array(
						'label' => __( 'Hover', 'cartflows' ),
					)
				);

					$this->add_control(
						'btn_hover_color',
						array(
							'label'     => __( 'Text Color', 'cartflows' ),
							'type'      => Controls_Manager::COLOR,
							'selectors' => array(
								'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover' => 'color: {{VALUE}};',
							),
						)
					);

					$this->add_control(
						'button_hover_border_color',
						array(
							'label'     => __( 'Border Hover Color', 'cartflows' ),
							'type'      => Controls_Manager::COLOR,
							'selectors' => array(
								'{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover' => 'border-color: {{VALUE}};',
							),
						)
					);

					$this->add_group_control(
						Group_Control_Background::get_type(),
						array(
							'name'     => 'button_background_hover_color',
							'label'    => __( 'Background Color', 'cartflows' ),
							'types'    => array( 'classic', 'gradient' ),
							'selector' => '{{WRAPPER}} .wcf-el-optin-form.cartflows-elementor__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover',
						)
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}


	/**
	 * Cartflows Optin Form Styler.
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public function is_reload_preview_required() {
		return true;
	}

	/**
	 * Render Optin Form output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function render() {

		self::$settings = $this->get_settings_for_display();

		/* Add elementor setting options to filters */
		$this->dynamic_option_filters();

		$checkout_id = get_the_id();

		do_action( 'cartflows_elementor_before_optin_shortcode', $checkout_id );

		?>
		<div class = "wcf-el-optin-form cartflows-elementor__optin-form">
			<?php echo do_shortcode( '[cartflows_optin]' ); ?>
		</div>
		<?php

	}

	/**
	 * Dynamic options of elementor and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		$optin_fields = array(

			// Input Fields.
			array(
				'filter_slug'  => 'wcf-input-fields-skins',
				'setting_name' => 'input_skins',
			),
		);

		if ( isset( $optin_fields ) && is_array( $optin_fields ) ) {

			foreach ( $optin_fields as $key => $field ) {

				$setting_name = $field['setting_name'];

				add_filter(
					'cartflows_optin_meta_' . $field['filter_slug'],
					function ( $value ) use ( $setting_name ) {

						$value = self::$settings[ $setting_name ];

						return $value;
					},
					10,
					1
				);
			}
		}

		do_action( 'cartflows_elementor_optin_options_filters', self::$settings );
	}
}
