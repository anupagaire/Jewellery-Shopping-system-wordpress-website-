<?php
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Widget_Base;
use Elementor\Widget_Button;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Next Step Button Widget
 *
 * @since 1.11.8
 */
class CartFlows_Next_Step_Button extends Widget_Base {

	/**
	 * Module should load or not.
	 *
	 * @since 1.11.8
	 * @access public
	 * @param string $step_type Current step type.
	 *
	 * @return bool true|false.
	 */
	public static function is_enable( $step_type ) {

		if ( 'landing' === $step_type ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.11.8
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'next-step-button';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.11.8
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Next Step Button', 'cartflows' );
	}


	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.11.8
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wcf-el-icon-next-step';
	}


	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.11.8
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'cartflows-widgets' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.11.8
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'cartflows', 'next step', 'button' );
	}

	/**
	 * Retrieve Next Step Button sizes.
	 *
	 * @since 1.11.8
	 * @access public
	 *
	 * @return array Next Step Button Sizes.
	 */
	public static function get_button_sizes() {
		return Widget_Button::get_button_sizes();
	}

	/**
	 * Register Next Step Button controls.
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function register_controls() {

		// Content Tab.
		$this->register_button_content_controls();

		// Style Tab.
		$this->register_button_style_controls();
		$this->register_button_content_style_controls();
	}

	/**
	 * Register Next Step Button General Controls.
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function register_button_content_controls() {

		$this->start_controls_section(
			'section_general_fields',
			array(
				'label' => __( 'General', 'cartflows' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'cartflows' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'BUY NOW', 'cartflows' ),
				'dynamic' => array(
					'active' => true,
				),
			)
		);

		$this->add_control(
			'sub_title',
			array(
				'label'   => __( 'Sub Title', 'cartflows' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array(
					'active' => true,
				),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'cartflows' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon Position', 'cartflows' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before_title',
				'options'   => array(
					'before_title'           => __( 'Before Title', 'cartflows' ),
					'after_title'            => __( 'After Title', 'cartflows' ),
					'before_title_sub_title' => __( 'Before Title & Sub Title', 'cartflows' ),
					'after_title_sub_title'  => __( 'After Title & Sub Title', 'cartflows' ),
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'icon_vertical_align',
			array(
				'label'       => __( 'Icon Vertical Alignment', 'cartflows' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'center',
				'options'     => array(
					'flex-start' => array(
						'title' => __( 'Top', 'cartflows' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Middle', 'cartflows' ),
						'icon'  => 'eicon-v-align-middle',
					),
				),
				'condition'   => array(
					'icon_position' => array( 'before_title_sub_title', 'after_title_sub_title' ),
					'icon[value]!'  => '',
				),
				'selectors'   => array(
					'{{WRAPPER}} .cartflows-elementor__next-step-button-icon-wrap' => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_spacing',
			array(
				'label'     => __( 'Icon Spacing', 'cartflows' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-elementor__next-step-button-title-wrap .cartflows-elementor__before_title, {{WRAPPER}} .cartflows-elementor__before_title_sub_title' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cartflows-elementor__next-step-button-title-wrap .cartflows-elementor__after_title, {{WRAPPER}} .cartflows-elementor__after_title_sub_title' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon Size', 'cartflows' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-elementor__next-step-button-wrap .cartflows-elementor__next-step-button-icon-wrap i ' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cartflows-elementor__next-step-button-wrap .cartflows-elementor__next-step-button-icon-wrap svg ' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Next Step Button Styling Controls.
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function register_button_style_controls() {

		$flow_id                 = wcf()->utils->get_flow_id();
		$gcp_primary_theme_color = ! empty( $flow_id ) ? get_post_meta( $flow_id, 'wcf-gcp-primary-color', true ) : '';

		$this->start_controls_section(
			'next_step_button_styling',
			array(
				'label' => __( 'General', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'cartflows' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'    => array(
						'title' => __( 'Left', 'cartflows' ),
						'icon'  => 'fa fa-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'cartflows' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'cartflows' ),
						'icon'  => 'fa fa-align-right',
					),
					'justify' => array(
						'title' => __( 'Justify', 'cartflows' ),
						'icon'  => 'fa fa-align-justify',
					),
				),
				'default'      => 'left',
				'prefix_class' => 'elementor%s-align-',
			)
		);

		$this->add_control(
			'button_size',
			array(
				'label'   => __( 'Button Size', 'cartflows' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'md',
				'options' => self::get_button_sizes(),
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'cartflows' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( '_next_step_button_style' );

			$this->start_controls_tab(
				'_next_step_button_normal',
				array(
					'label' => __( 'Normal', 'cartflows' ),
				)
			);

				$this->add_control(
					'text_color',
					array(
						'label'     => __( 'Text Color', 'cartflows' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} a.cartflows-elementor__next-step-button-link' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'           => 'all_background_color',
						'label'          => __( 'Background Color', 'cartflows' ),
						'types'          => array( 'classic', 'gradient' ),
						'selector'       => '{{WRAPPER}} a.elementor-button',
						'fields_options' => array(
							'background' => array(
								'default' => 'classic',
							),
							'color'      => array(
								'default' => $gcp_primary_theme_color,
								'global'  => array(
									'default' => Global_Colors::COLOR_ACCENT,
								),
							),
						),
					)
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'     => 'all_border',
						'label'    => __( 'Border', 'cartflows' ),
						'selector' => '{{WRAPPER}} .elementor-button',
					)
				);

				$this->add_control(
					'all_border_radius',
					array(
						'label'      => __( 'Rounded Corners', 'cartflows' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%' ),
						'selectors'  => array(
							'{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
					)
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'_next_step_button_hover',
				array(
					'label' => __( 'Hover', 'cartflows' ),
				)
			);

				$this->add_control(
					'hover_text_color',
					array(
						'label'     => __( 'Hover Text Color', 'cartflows' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} a.cartflows-elementor__next-step-button-link:hover' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'           => 'hover_background_color',
						'label'          => __( 'Hover Background Color', 'cartflows' ),
						'types'          => array( 'classic', 'gradient' ),
						'selector'       => '{{WRAPPER}} a.elementor-button:hover',
						'fields_options' => array(
							'background' => array(
								'default' => 'classic',
							),
							'color'      => array(
								'global' => array(
									'default' => Global_Colors::COLOR_ACCENT,
								),
							),
						),
					)
				);

				$this->add_control(
					'border_hover_color',
					array(
						'label'     => __( 'Border Hover Color', 'cartflows' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} a.elementor-button:hover' => 'border-color: {{VALUE}};',
						),
					)
				);

				$this->add_control(
					'hover_animation',
					array(
						'label' => __( 'Hover Animation', 'cartflows' ),
						'type'  => Controls_Manager::HOVER_ANIMATION,
					)
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * Register Button Content Styling Controls.
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function register_button_content_style_controls() {

		$this->start_controls_section(
			'button_content_styling',
			array(
				'label' => __( 'Content', 'cartflows' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_align',
			array(
				'label'     => __( 'Text Alignment', 'cartflows' ),
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
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} a.elementor-button' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'align' => 'justify',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => 'Title Typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
				'selector' => '{{WRAPPER}} .cartflows-elementor__next-step-button-title',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'sub_title_typography',
				'label'     => 'Sub Title Typography',
				'global'    => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector'  => '{{WRAPPER}} .cartflows-elementor__next-step-button-sub-title',
				'condition' => array(
					'sub_title!' => '',
				),
			)
		);

		$this->add_control(
			'title_sub_title_spacing',
			array(
				'label'     => __( 'Title and Sub Title Spacing', 'cartflows' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-elementor__next-step-button-sub-title' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'sub_title!' => '',
				),
			)
		);

	}


	/**
	 * Render Next Step Button icon.
	 *
	 * @since 1.11.8
	 * @param string $position Icon positin.
	 * @param array  $settings settings.
	 * @access protected
	 */
	protected function render_button_icon( $position, $settings ) {

		$icon                = esc_attr( $settings['icon']['value'] );
		$icon_position_class = esc_attr( 'cartflows-elementor__' . $settings['icon_position'] );

		if ( '' !== $icon && $position === $settings['icon_position'] ) {
			?>
			<span class="cartflows-elementor__next-step-button-icon-wrap <?php echo esc_attr( $icon_position_class ); ?>">
				<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
			</span>
			<?php
		}

	}

	/**
	 * Render Next Step Button output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function render() {

		$settings    = $this->get_settings_for_display();
		$button_size = esc_attr( $settings['button_size'] );
		$title       = esc_html( $settings['title'] );
		$sub_title   = esc_html( $settings['sub_title'] );
		?>

		<div class="cartflows-elementor__next-step-button">
			<div class="cartflows-elementor__next-step-button-wrap">
				<a href="?class=wcf-next-step" class="cartflows-elementor__next-step-button-link elementor-button elementor-button-link elementor-size-<?php echo esc_attr( $button_size ); ?> elementor-animation-<?php echo esc_attr( $settings['hover_animation'] ); ?>">

					<div class="cartflows-elementor__next-step-inner-wrap">

						<?php $this->render_button_icon( 'before_title_sub_title', $settings ); ?>
							<span class="cartflows-elementor__next-step-button-content-wrap">
								<div class="cartflows-elementor__next-step-button-title-wrap">
									<?php $this->render_button_icon( 'before_title', $settings ); ?>
										<span class="cartflows-elementor__next-step-button-title"><?php echo esc_html( $title ); ?></span>
									<?php $this->render_button_icon( 'after_title', $settings ); ?>
								</div>
								<?php if ( '' !== $settings['sub_title'] ) { ?>
									<div class="cartflows-elementor__next-step-button-sub-title"><?php echo esc_html( $sub_title ); ?></div>
								<?php } ?>
							</span>
						<?php $this->render_button_icon( 'after_title_sub_title', $settings ); ?>

					</div>

				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Render Next Step Button output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function render_next_step_button() {
		?>
		<#
			function render_icon(position) {

				var iconHTML = elementor.helpers.renderIcon( view, settings.icon, {}, 'i' , 'object' );

				var icon_position_class = 'cartflows-elementor__'+settings.icon_position;

				if( '' !== settings.icon.value && position === settings.icon_position ){ #>
					<span class="cartflows-elementor__next-step-button-icon-wrap {{icon_position_class}}">
						{{ iconHTML.value }}
					</span>
				<#}
			}
		#>
		<div class="cartflows-elementor__next-step-button">
			<div class="cartflows-elementor__next-step-button-wrap">
				<a href="?class=wcf-next-step" class="cartflows-elementor__next-step-button-link elementor-button elementor-button-link elementor-size-{{ settings.button_size }} elementor-animation-{{ settings.hover_animation }}" >
					<div class="cartflows-elementor__next-step-inner-wrap">
						<# render_icon('before_title_sub_title') #>
							<span class="cartflows-elementor__next-step-button-content-wrap">
								<div class="cartflows-elementor__next-step-button-title-wrap">
									<# render_icon('before_title') #>
										<span class="cartflows-elementor__next-step-button-title">
											{{settings.title}}
										</span>
									<# render_icon('after_title') #>
								</div>
								<# if( '' !== settings.sub_title ){ #>
									<div class="cartflows-elementor__next-step-button-sub-title">{{settings.sub_title}}</div>
								<# } #>
							</span>
						<# render_icon('after_title_sub_title') #>
					</div>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Next Step Button output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since 1.11.8
	 * @access protected
	 */
	protected function content_template() {
		$this->render_next_step_button();
	}

}
