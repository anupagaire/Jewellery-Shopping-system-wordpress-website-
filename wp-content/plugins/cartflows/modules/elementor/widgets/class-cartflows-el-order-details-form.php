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


if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Order Details Form Widget
 *
 * @since 1.6.15
 */
class Cartflows_Order_Details_Form extends Widget_Base {

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

		if ( 'thankyou' === $step_type && wcf()->is_woo_active ) {
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
		return 'order-details-form';
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
		return __( 'Order Details Form', 'cartflows' );
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
		return 'wcf-el-icon-order-detail';
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
		return array( 'cartflows', 'order details', 'form' );
	}

	/**
	 * Register Order Details Form controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_controls() {

		// Content Tab.
		$this->register_thankyou_controls();

		// Style Tab.
		$this->register_spacing_controls();
		$this->register_heading_style_controls();
		$this->register_general_style_controls();
		$this->register_section_order_review_style_controls();
		$this->register_section_downloads_style_controls();
		$this->register_section_order_details_style_controls();
		$this->register_section_customer_details_style_controls();

	}

	/**
	 * Register Thank you page Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_thankyou_controls() {

		$this->start_controls_section(
			'section_thankyou_fields',
			array(
				'label' => __( 'General', 'cartflows' ),
			)
		);

			$this->add_control(
				'thankyou_text',
				array(
					'label'       => __( 'Thank You Text', 'cartflows' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Thank you. Your order has been received.', 'cartflows' ),
					'label_block' => true,
				)
			);

			$this->add_control(
				'show_order_overview',
				array(
					'label'        => __( 'Order Overview', 'cartflows' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'cartflows' ),
					'label_off'    => __( 'Hide', 'cartflows' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'show_order_details',
				array(
					'label'        => __( 'Order Details', 'cartflows' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'cartflows' ),
					'label_off'    => __( 'Hide', 'cartflows' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'show_billing_address',
				array(
					'label'        => __( 'Billing Address', 'cartflows' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'cartflows' ),
					'label_off'    => __( 'Hide', 'cartflows' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'show_shipping_address',
				array(
					'label'        => __( 'Shipping Address', 'cartflows' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'cartflows' ),
					'label_off'    => __( 'Hide', 'cartflows' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

		$this->end_controls_section();

	}

	/**
	 * Register spacing Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_spacing_controls() {

		$this->start_controls_section(
			'order_details_form_spacing_styling',
			array(
				'label' => __( 'Spacing', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'heading_spacing',
				array(
					'label'     => __( 'Heading Bottom Spacing', 'cartflows' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'max' => 50,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .cartflows-elementor__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$this->add_control(
				'sections_spacing',
				array(
					'label'     => __( 'Spacing Between Sections', 'cartflows' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'max' => 50,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .woocommerce-order ul.order_details,
						{{WRAPPER}} .woocommerce-order .woocommerce-customer-details,
						{{WRAPPER}} .woocommerce-order .woocommerce-order-details,
						{{WRAPPER}} .woocommerce-order .woocommerce-order-downloads,
						{{WRAPPER}} .woocommerce-order .woocommerce-bacs-bank-details,
						{{WRAPPER}} .woocommerce-order-details.mollie-instructions' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
				)
			);

		$this->end_controls_section();

	}

	/**
	 * Register heading Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_heading_style_controls() {

		$this->start_controls_section(
			'order_details_form_heading_styling',
			array(
				'label' => __( 'Heading', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'heading_align',
				array(
					'label'     => __( 'Alignment', 'cartflows' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'left'   => array(
							'title' => __( 'Left', 'cartflows' ),
							'icon'  => 'fa fa-align-left',
						),
						'center' => array(
							'title' => __( 'Center', 'cartflows' ),
							'icon'  => 'fa fa-align-center',
						),
						'right'  => array(
							'title' => __( 'Right', 'cartflows' ),
							'icon'  => 'fa fa-align-right',
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .cartflows-elementor__order-details-form .woocommerce-order .woocommerce-thankyou-order-received' => 'text-align: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'heading_color',
				array(
					'label'     => __( 'Text Color', 'cartflows' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .cartflows-elementor__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'heading_typography',
					'label'    => 'Typography',
					'selector' => '{{WRAPPER}} .cartflows-elementor__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received',
				)
			);

		$this->end_controls_section();

	}

	/**
	 * Register General Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_general_style_controls() {

		$this->start_controls_section(
			'order_details_form_general_styling',
			array(
				'label' => __( 'Sections', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'label_section_heading',
			array(
				'label' => __( 'Sections Heading', 'cartflows' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'section_heading_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-elementor__order-details-form .wcf-thankyou-wrap .woocommerce-order h2' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_heading_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .cartflows-elementor__order-details-form .wcf-thankyou-wrap .woocommerce-order h2',
			)
		);

		$this->add_control(
			'section_content_heading',
			array(
				'label'     => __( 'Sections Content', 'cartflows' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-elementor__order-details-form .wcf-thankyou-wrap .woocommerce-order,
					{{WRAPPER}} .woocommerce-order-downloads table.shop_table' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_text_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li, {{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table, {{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address,
				{{WRAPPER}} .woocommerce-order-downloads table.shop_table',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_all_background_color',
				'label'     => __( 'Background Color', 'cartflows' ),
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, {{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, {{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details,
				{{WRAPPER}} .woocommerce-order-downloads',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Section Order Review Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_section_order_review_style_controls() {

		$this->start_controls_section(
			'section_order_review_styling',
			array(
				'label'     => __( 'Order Overview', 'cartflows' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_order_overview' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_order_review_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li',
			)
		);

		$this->add_control(
			'section_order_review_text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_order_review_background_color',
				'label'    => __( 'Background Color', 'cartflows' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Section Order downloads Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_section_downloads_style_controls() {

		$this->start_controls_section(
			'section_downloads_styling',
			array(
				'label' => __( 'Downloads', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_downloads_label_heading',
			array(
				'label' => __( 'Heading', 'cartflows' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_downloads_heading_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .woocommerce-order h2.woocommerce-order-downloads__title,
				{{WRAPPER}} .woocommerce-order .woocommerce-order-downloads h2.woocommerce-order-downloads__title',
			)
		);

		$this->add_control(
			'section_downloads_heading_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-order h2.woocommerce-order-downloads__title,
					{{WRAPPER}} .woocommerce-order .woocommerce-order-downloads h2.woocommerce-order-downloads__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_downloads_content_heading',
			array(
				'label'     => __( 'Content', 'cartflows' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_downloads_text_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads table.shop_table',
			)
		);

		$this->add_control(
			'section_downloads_text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads table.shop_table' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_downloads_background_color',
				'label'    => __( 'Background Color', 'cartflows' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Section Order Details Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_section_order_details_style_controls() {

		$this->start_controls_section(
			'section_order_details_styling',
			array(
				'label'     => __( 'Order Details', 'cartflows' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_order_details' => 'yes',
				),
			)
		);

		$this->add_control(
			'section_order_details_label_heading',
			array(
				'label' => __( 'Heading', 'cartflows' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_order_details_heading_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title',
			)
		);

		$this->add_control(
			'section_order_details_heading_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_order_details_content_heading',
			array(
				'label'     => __( 'Content', 'cartflows' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_order_details_text_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table',
			)
		);

		$this->add_control(
			'section_order_details_text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_order_details_background_color',
				'label'    => __( 'Background Color', 'cartflows' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details',
			)
		);

		$this->end_controls_section();
	}


	/**
	 * Register Section Customer Details Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_section_customer_details_style_controls() {

		$this->start_controls_section(
			'section_customer_details_styling',
			array(
				'label' => __( 'Customer Details', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_customer_details_label_heading',
			array(
				'label' => __( 'Heading', 'cartflows' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_customer_details_heading_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title',
			)
		);

		$this->add_control(
			'section_customer_details_heading_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_customer_details_content_heading',
			array(
				'label'     => __( 'Content', 'cartflows' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_customer_details_text_typography',
				'label'    => 'Typography',
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address',
			)
		);

		$this->add_control(
			'section_customer_details_text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_customer_details_background_color',
				'label'    => __( 'Background Color', 'cartflows' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Order Details Form output on the frontend.
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

		$order_overview = self::$settings['show_order_overview'] ? self::$settings['show_order_overview'] : 'no';

		$order_details = self::$settings['show_order_details'] ? self::$settings['show_order_details'] : 'no';

		$shipping_address = self::$settings['show_shipping_address'] ? self::$settings['show_shipping_address'] : 'no';

		$billing_address = self::$settings['show_billing_address'] ? self::$settings['show_billing_address'] : 'no';

		?>
		<div class = "cartflows-elementor__order-details-form cartflows-elementor__display-order-overview-<?php echo esc_attr( $order_overview ); ?> cartflows-elementor__display-order-details-<?php echo esc_attr( $order_details ); ?> cartflows-elementor__display-billing-address-<?php echo esc_attr( $billing_address ); ?> cartflows-elementor__display-shipping-address-<?php echo esc_attr( $shipping_address ); ?>">
			<?php echo do_shortcode( '[cartflows_order_details]' ); ?>
		</div>
		<?php
	}

	/**
	 * Dynamic options of elementor and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		if ( ! empty( self::$settings['thankyou_text'] ) ) {

			add_filter(
				'cartflows_thankyou_meta_wcf-tq-text',
				function( $text ) {

					$text = self::$settings['thankyou_text'];

					return $text;
				},
				10,
				1
			);
		}

	}

	/**
	 * Render Order Details Form output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function content_template() {
	}
}
