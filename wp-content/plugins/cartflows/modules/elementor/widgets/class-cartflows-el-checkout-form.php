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
 * Checkout Form Widget
 *
 * @since 1.6.15
 */
class Cartflows_Checkout_Form extends Widget_Base {

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

		if ( 'checkout' === $step_type && wcf()->is_woo_active ) {
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
		return 'checkout-form';
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
		return __( 'Checkout Form', 'cartflows' );
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
		return 'wcf-el-icon-checkout-form';
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
		return array( 'cartflows', 'checkout', 'form' );
	}

	/**
	 * Global Primary Color
	 *
	 * @since x.x.x
	 * @access public
	 *
	 * @var string $gcp_primary_theme_color Primary Color.
	 * @return string $gcp_primary_theme_color Primary Color.
	 */
	public static $gcp_primary_theme_color = '';

	/**
	 * Global Primary Text Color
	 *
	 * @since x.x.x
	 * @access public
	 *
	 * @var string string $gcp_primary_text_color Primary Text Color.
	 * @return string $gcp_primary_text_color Primary Text Color.
	 */
	public static $gcp_primary_text_color = '';

	/**
	 * Global Secondary Color
	 *
	 * @since x.x.x
	 * @access public
	 *
	 * @var string $gcp_secondary_theme_color Secondary Color.
	 * @return string $gcp_secondary_theme_color Secondary Color.
	 */
	public static $gcp_secondary_theme_color = '';

	/**
	 * Register cart controls controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_controls() {

		// Assign global colors to the global vars.
		$flow_id                         = wcf()->utils->get_flow_id();
		self::$gcp_primary_theme_color   = ! empty( $flow_id ) ? get_post_meta( $flow_id, 'wcf-gcp-primary-color', true ) : '';
		self::$gcp_secondary_theme_color = ! empty( $flow_id ) ? get_post_meta( $flow_id, 'wcf-gcp-secondary-color', true ) : '';
		self::$gcp_primary_text_color    = ! empty( $flow_id ) ? get_post_meta( $flow_id, 'wcf-gcp-primary-text-color', true ) : '';

		/**
		 * Self::$gcp_primary_font_family   = !empty( $flow_id ) ? get_post_meta( $flow_id, 'wcf-gcp-primary-base-font-family', true ) : '';
		 * self::$gcp_primary_font_family   = ! empty( $gcp_primary_font_family ) ? str_replace("'", "", explode( ',', $gcp_primary_font_family, 2 )[0] ) : '';
		*/

		// Content Tab.
		$this->register_general_content_controls();

		// Style Tab.
		$this->register_global_style_controls();
		$this->register_heading_style_controls();
		$this->register_input_style_controls();
		$this->register_button_style_controls();
		$this->register_payment_section_style_controls();
		$this->register_error_style_controls();
		$this->register_order_review_style_controls();
	}

	/**
	 * Function to get layout types.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function get_layout_types() {

		$layout_options = array();

		if ( ! _is_cartflows_pro() ) {
			$layout_options = array(
				'modern-checkout'    => __( 'Modern Checkout', 'cartflows' ),
				'modern-one-column'  => __( 'Modern One Column', 'cartflows' ),
				'one-column'         => __( 'One Column', 'cartflows' ),
				'two-column'         => __( 'Two Column', 'cartflows' ),
				'two-step'           => __( 'Two Step ( PRO )', 'cartflows' ),
				'multistep-checkout' => __( 'MultiStep Checkout ( PRO )', 'cartflows' ),
			);
		} else {
			$layout_options = array(
				'modern-checkout'    => __( 'Modern Checkout', 'cartflows' ),
				'modern-one-column'  => __( 'Modern One Column', 'cartflows' ),
				'one-column'         => __( 'One Column', 'cartflows' ),
				'two-column'         => __( 'Two Column', 'cartflows' ),
				'two-step'           => __( 'Two Step', 'cartflows' ),
				'multistep-checkout' => __( 'MultiStep Checkout', 'cartflows' ),
			);
		}

		return $layout_options;
	}

	/**
	 * Function to get skin types.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function get_skin_types() {

		$skin_options = array(
			'default'      => __( 'Default', 'cartflows' ),
			'modern-label' => __( 'Modern Labels', 'cartflows' ),
		);

		return $skin_options;
	}

	/**
	 * Register General Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_general_content_controls() {

		$this->start_controls_section(
			'section_general_fields',
			array(
				'label' => __( 'Layout', 'cartflows' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Select Layout', 'cartflows' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'modern-checkout',
				'options' => $this->get_layout_types(),
			)
		);

		if ( ! _is_cartflows_pro() ) {

			$this->add_control(
				'layout_upgrade_pro',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'This feature is available in the CartFlows higher plan. <a href="%s" target="_blank" rel="noopener">Upgrade Now!</a>.', 'cartflows' ), CARTFLOWS_DOMAIN_URL ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array(
						'layout' => array( 'two-step', 'multistep-checkout' ),
					),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Register General Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_global_style_controls() {
		$this->start_controls_section(
			'section_general_style_fields',
			array(
				'label' => __( 'Global', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'global_primary_color',
				array(
					'label'     => __( 'Primary Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => self::$gcp_primary_theme_color,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-primary-color: {{VALUE}};',

						/* Only Modern Checkout related CSS */
						'{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row input.input-text:focus,
						{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row textarea:focus,
						{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review .wcf-custom-coupon-field input.input-text:focus' => 'box-shadow: 0 0 0 1px {{VALUE}};',

						/* Only Modern Checkout related CSS */

						'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before' => 'border-top-color: {{VALUE}};',

						'{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,
						{{WRAPPER}} .wcf-embed-checkout-form form .checkout_coupon .button,
						body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn' => 'background-color: {{VALUE}}; color: #fff;',
					),
				)
			);

			$this->add_control(
				'global_text_color',
				array(
					'label'     => __( 'Text Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => self::$gcp_primary_text_color,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-text-color: {{VALUE}}; --wcf-link-color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'global_typography',
					'label'    => 'Typography',
					'default'  => '',
					'selector' => '{{WRAPPER}} .cartflows-elementor__checkout-form .wcf-embed-checkout-form',
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Heading Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_heading_style_controls() {
		$this->start_controls_section(
			'section_heading_style_fields',
			array(
				'label' => __( 'Heading', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'heading_text_color',
				array(
					'label'     => __( 'Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => self::$gcp_secondary_theme_color,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form, {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-heading-color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'heading_typography',
					'label'    => 'Typography',
					'selector' => '{{WRAPPER}} .cartflows-elementor__checkout-form .wcf-embed-checkout-form .woocommerce h3,
					{{WRAPPER}} .cartflows-elementor__checkout-form .wcf-embed-checkout-form .woocommerce h3 span,
					{{WRAPPER}} .cartflows-elementor__checkout-form .wcf-embed-checkout-form .woocommerce-checkout #order_review_heading,
					{{WRAPPER}} .cartflows-elementor__checkout-form .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-name,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .col2-set .col-1 h3,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .col2-set .col-2 h3',
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
					'selector' => '{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row textarea,
					{{WRAPPER}} .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select.select,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .col2-set .col-1,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .col2-set .col-2,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form p.form-row label,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:checked + label,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:not(:checked) + label,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select',
				)
			);

			$this->add_control(
				'label_color',
				array(
					'label'     => __( 'Label Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout, {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-field-label-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'input_bgcolor',
				array(
					'label'     => __( 'Field Background Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form, {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-field-bg-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'input_color',
				array(
					'label'     => __( 'Input Text / Placeholder Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form, {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-field-text-color: {{VALUE}};',
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
						'{{WRAPPER}} .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"],
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row textarea,
						{{WRAPPER}} .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select.select,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select' => 'border-style: {{VALUE}};',
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
						'{{WRAPPER}} .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"],
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row textarea,
						{{WRAPPER}} .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select.select,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$this->add_control(
				'input_border_color',
				array(
					'label'     => __( 'Border Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form, {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-field-border-color: {{VALUE}};',
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
						'{{WRAPPER}} .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"],
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row textarea,
						{{WRAPPER}} .wcf-embed-checkout-form .select2-container--default .select2-selection--single,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select.select,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce form .form-row select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'label' => __( 'Buttons', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'buttons_typography',
					'label'    => 'Typography',
					'selector' => '{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #payment #place_order:before,
					{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
					{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
					{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
					{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
					body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
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
								'{{WRAPPER}} .wcf-embed-checkout-form, {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-btn-text-color: {{VALUE}};',
							),
						)
					);

					$this->add_group_control(
						Group_Control_Background::get_type(),
						array(
							'name'     => 'btn_background_color',
							'label'    => __( 'Background Color', 'cartflows' ),
							'types'    => array( 'classic', 'gradient' ),
							'selector' => '{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
							{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
							{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
							{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
							body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
						)
					);

					$this->add_group_control(
						Group_Control_Border::get_type(),
						array(
							'name'     => 'btn_border',
							'label'    => __( 'Border', 'cartflows' ),
							'selector' => '{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
							{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
							{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
							{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
							body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
						)
					);

					$this->add_responsive_control(
						'btn_border_radius',
						array(
							'label'      => __( 'Rounded Corners', 'cartflows' ),
							'type'       => Controls_Manager::DIMENSIONS,
							'size_units' => array( 'px', '%' ),
							'selectors'  => array(
								'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
								{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
								{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
								{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
								body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							),
						)
					);

					$this->add_group_control(
						Group_Control_Box_Shadow::get_type(),
						array(
							'name'     => 'button_box_shadow',
							'selector' => '{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small,
							{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button,
							{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button,
							{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn,
							body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
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
								'{{WRAPPER}} .wcf-embed-checkout-form,
								{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-btn-hover-text-color: {{VALUE}};',
								'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn:hover' => 'color: {{VALUE}};',
							),
						)
					);

					$this->add_control(
						'button_hover_border_color',
						array(
							'label'     => __( 'Border Hover Color', 'cartflows' ),
							'type'      => Controls_Manager::COLOR,
							'selectors' => array(
								'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button:hover,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button:hover,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button:hover,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small:hover,
								{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon:hover,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button:hover,
								{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button:hover,
								{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover,
								{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn:hover,
								body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn:hover' => 'border-color: {{VALUE}};',
							),
						)
					);

					$this->add_group_control(
						Group_Control_Background::get_type(),
						array(
							'name'     => 'button_background_hover_color',
							'label'    => __( 'Background Color', 'cartflows' ),
							'types'    => array( 'classic', 'gradient' ),
							'selector' => '{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button:hover,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button:hover,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button:hover,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small:hover,
							{{WRAPPER}} .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon:hover,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button:hover,
							{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button:hover,
							{{WRAPPER}} .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover,
							{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn:hover,
							body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn:hover',
						)
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Sections Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_payment_section_style_controls() {
		$this->start_controls_section(
			'section_payment_style_fields',
			array(
				'label' => __( 'Payment Section', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
			$this->add_control(
				'payment_section_text_color',
				array(
					'label'     => __( 'Text Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-payment-section-label-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'payment_section_desc_color',
				array(
					'label'     => __( 'Description Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						 {{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-payment-section-desc-text-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'payment_section_bg_color',
				array(
					'label'     => __( 'Section Background Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-payment-section-bg-color: {{VALUE}};',
					),
					'separator' => 'before',
				)
			);

			$this->add_control(
				'payment_info_bg_color',
				array(
					'label'     => __( 'Information Background Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-payment-section-desc-bg-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'payment_section_padding',
				array(
					'label'      => __( 'Section Padding', 'cartflows' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'separator'  => 'before',
				)
			);

			$this->add_control(
				'payment_section_margin',
				array(
					'label'      => __( 'Section Margin', 'cartflows' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					),
				)
			);

			$this->add_control(
				'payment_section_radius',
				array(
					'label'      => __( 'Section Rounded Corners', 'cartflows' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Sections error Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_error_style_controls() {
		$this->start_controls_section(
			'section_error_style_fields',
			array(
				'label' => __( 'Field Validation & Error Messages', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'error_fields_text',
				array(
					'label'       => __( 'Field Validation', 'cartflows' ),
					'type'        => Controls_Manager::HEADING,
					'label_block' => true,
				)
			);

			$this->add_control(
				'error_label_color',
				array(
					'label'     => __( 'Label Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .woocommerce form .form-row.woocommerce-invalid label' => '--wcf-field-error-label-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'error_field_border_color',
				array(
					'label'     => __( 'Field Border Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form,
						{{WRAPPER}} .wcf-embed-checkout-form-modern-checkout' => '--wcf-field-error-border-color: {{VALUE}};',

					),
					'separator' => 'after',
				)
			);

			$this->add_control(
				'error_fields_section',
				array(
					'label'       => __( 'Error Messages', 'cartflows' ),
					'type'        => Controls_Manager::HEADING,
					'label_block' => true,
				)
			);

			$this->add_control(
				'error_text_color',
				array(
					'label'     => __( 'Error Message Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-error,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'error_bg_color',
				array(
					'label'     => __( 'Background Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-error,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'error_border_color',
				array(
					'label'     => __( 'Border Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-error,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error,
						{{WRAPPER}} .wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error' => 'border-color: {{VALUE}};',

					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register order review column style controls
	 */
	protected function register_order_review_style_controls() {

		$this->start_controls_section(
			'section_order_review_column',
			array(
				'label'     => __( 'Order Review', 'cartflows' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout' => 'modern-checkout',
				),
			)
		);

		$this->add_control(
			'order_review_section_text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table th,
					 {{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table td' => 'color: {{VALUE}};',
				),
				'separator' => 'before',
				'condition' => array(
					'layout' => 'modern-checkout',
				),
			)
		);

		$this->add_control(
			'order_review_section_bg_color',
			array(
				'label'     => __( 'Background Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tbody,
					 {{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.cart-discount,
					 {{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.cart-subtotal,
					 {{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.order-total:not( .recurring-total ) th,
					 {{WRAPPER}} .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table tfoot tr.order-total:not( .recurring-total ) td' => 'border-color: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'layout' => 'modern-checkout',
				),
			)
		);

	}

	/**
	 * Cartflows Checkout Form Styler.
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public function is_reload_preview_required() {
		return true;
	}

	/**
	 * Render Checkout Form output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function render() {

		self::$settings = $this->get_settings_for_display();

		$checkout_id = get_the_id();

		/* Add elementor setting options to filters */
		$this->dynamic_option_filters();

		do_action( 'cartflows_elementor_before_checkout_shortcode', $checkout_id );

		?>
		<div class = "wcf-el-checkout-form cartflows-elementor__checkout-form">
			<?php echo do_shortcode( '[cartflows_checkout]' ); ?>
		</div>
		<?php

	}

	/**
	 * Dynamic options of elementor and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		$checkout_fields = array(
			array(
				'filter_slug'  => 'wcf-fields-skins',
				'setting_name' => 'input_skins',
			),
			array(
				'filter_slug'  => 'wcf-checkout-layout',
				'setting_name' => 'layout',
			),
		);

		if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

			foreach ( $checkout_fields as $key => $field ) {

				$setting_name = $field['setting_name'];

				if ( '' !== self::$settings[ $setting_name ] ) {

					add_filter(
						'cartflows_checkout_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_name ) {

							$value = self::$settings[ $setting_name ];

							return $value;
						},
						10,
						1
					);
				}
			}
		}

		do_action( 'cartflows_elementor_checkout_options_filters', self::$settings );
	}

}
