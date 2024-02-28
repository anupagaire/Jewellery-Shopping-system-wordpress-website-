<?php
/**
 * Thank you post meta fields.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Meta Boxes setup
 */
class Cartflows_Thankyou_Meta_Data extends Cartflows_Step_Meta_Base {


	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;


	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Page Header Tabs
	 *
	 * @param  int   $step_id Post ID.
	 * @param  array $options Post meta.
	 */
	public function get_settings( $step_id, $options = array() ) {

		$this->step_id = $step_id;
		$this->options = $options;

		$common_tabs = $this->common_tabs();
		$add_tabs    = array(
			'settings' => array(
				'title'    => __( 'Settings', 'cartflows' ),
				'id'       => 'settings',
				'class'    => '',
				'icon'     => 'dashicons-format-aside',
				'priority' => 40,
			),

		);

		$tabs            = array_merge( $common_tabs, $add_tabs );
		$settings        = $this->get_settings_fields( $step_id );
		$design_settings = $this->get_design_fields( $step_id );

		$settings_data = array(
			'tabs'            => $tabs,
			'settings'        => $settings,
			'design_settings' => $design_settings,
		);

		return $settings_data;
	}

	/**
	 * Get design settings data.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_design_fields( $step_id ) {

		$options = $this->get_data( $step_id );

		$settings = array(
			'settings' => array(
				'shortcode'        => array(
					'title'    => __( 'Shortcode', 'cartflows' ),
					'slug'     => 'shortcode',
					'priority' => 10,
					'fields'   => array(
						'thankyou-shortcode' => array(
							'type'          => 'text',
							'name'          => 'thankyou-shortcode',
							'label'         => __( 'Order Details', 'cartflows' ),
							'value'         => '[cartflows_order_details]',
							'help'          => esc_html__( 'Add this shortcode to your optin page', 'cartflows' ),
							'readonly'      => true,
							'display_align' => 'vertical',
						),
					),
				),

				'heading'          => array(
					'title'  => __( 'Heading', 'cartflows' ),
					'slug'   => 'heading',
					'fields' => array(
						'heading-color'       => array(
							'type'   => 'color-picker',
							'name'   => 'wcf-tq-heading-color',
							'label'  => __( 'Color', 'cartflows' ),
							'value'  => $options['wcf-tq-heading-color'],
							'withBg' => true,
						),
						'heading-font-family' => array(
							'type'              => 'font-family',
							'name'              => 'wcf-tq-heading-font-family',
							'label'             => __( 'Font Family', 'cartflows' ),
							'value'             => $options['wcf-tq-heading-font-family'],
							'font_weight_name'  => 'wcf-tq-heading-font-wt',
							'font_weight_value' => $options['wcf-tq-heading-font-wt'],
							'for'               => '',
							'display_align'     => 'vertical',
						),
					),
				),

				'text'             => array(
					'title'  => __( 'Text', 'cartflows' ),
					'slug'   => 'text',
					'fields' => array(
						'text-color'       => array(
							'type'   => 'color-picker',
							'name'   => 'wcf-tq-text-color',
							'label'  => __( 'Color', 'cartflows' ),
							'value'  => $options['wcf-tq-text-color'],
							'withBg' => true,
						),
						'text-font-family' => array(
							'type'          => 'font-family',
							'name'          => 'wcf-tq-font-family',
							'label'         => __( 'Font Family', 'cartflows' ),
							'value'         => $options['wcf-tq-font-family'],
							'display_align' => 'vertical',
						),
						'text-font-size'   => array(
							'type'          => 'number',
							'name'          => 'wcf-tq-font-size',
							'label'         => __( 'Font Size (In px)', 'cartflows' ),
							'value'         => $options['wcf-tq-font-size'],
							'display_align' => 'vertical',
						),
					),
				),

				'advanced-options' => array(
					'title'  => __( 'Advanced Options', 'cartflows' ),
					'slug'   => 'advanced_options',
					'fields' => array(
						'wcf-tq-advance-options-fields' => array(
							'type'         => 'toggle',
							'label'        => __( 'Enable Advanced Options', 'cartflows' ),
							'name'         => 'wcf-tq-advance-options-fields',
							'value'        => $options['wcf-tq-advance-options-fields'],
							'is_fullwidth' => true,
						),
						'wcf-show-details-section'      => array(
							'type'          => 'number',
							'label'         => __( 'Container Width (In px)', 'cartflows' ),
							'name'          => 'wcf-tq-container-width',
							'value'         => $options['wcf-tq-container-width'],
							'display_align' => 'vertical',
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'wcf-tq-advance-options-fields',
										'operator' => '===',
										'value'    => 'yes',
									),
								),
							),
						),
						'section-bg-color'              => array(
							'type'       => 'color-picker',
							'name'       => 'wcf-tq-section-bg-color',
							'label'      => __( 'Section Background Color', 'cartflows' ),
							'value'      => $options['wcf-tq-section-bg-color'],
							'withBg'     => true,
							'conditions' => array(
								'fields' => array(
									array(
										'name'     => 'wcf-tq-advance-options-fields',
										'operator' => '===',
										'value'    => 'yes',
									),
								),
							),
						),
					),
				),

			),
		);

		return $settings;
	}


	/**
	 * Get settings fields.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_settings_fields( $step_id ) {

		$options = $this->get_data( $step_id );

		$settings = array(
			'settings' => array(
				'general'         => array(
					'title'    => __( 'General', 'cartflows' ),
					'slug'     => 'general',
					'priority' => 20,
					'fields'   => array(
						'slug'                     => array(
							'type'          => 'text',
							'name'          => 'post_name',
							'label'         => __( 'Step Slug', 'cartflows' ),
							'value'         => get_post_field( 'post_name', $step_id ),
							'display_align' => 'vertical',
						),
						'step-note'                => array(
							'type'          => 'textarea',
							'name'          => 'wcf-step-note',
							'label'         => __( 'Step Note', 'cartflows' ),
							'value'         => get_post_meta( $step_id, 'wcf-step-note', true ),
							'rows'          => 2,
							'cols'          => 38,
							'display_align' => 'vertical',
						),
						'wcf-thanku-custom-script' => array(
							'type'          => 'textarea',
							'label'         => __( 'Custom Script', 'cartflows' ),
							'name'          => 'wcf-custom-script',
							'value'         => $options['wcf-custom-script'],
							'tooltip'       => __( 'Enter custom JS/CSS. Wrap your custom CSS in style tag.', 'cartflows' ),
							'display_align' => 'vertical',
						),
					),
				),
				'thankyou_fields' => array(
					'title'    => __( 'Options', 'cartflows' ),
					'slug'     => 'thankyou_fields',
					'priority' => 10,
					'fields'   => array(
						'wcf-show-overview-section' => array(
							'type'         => 'toggle',
							'label'        => __( 'Enable Order Overview', 'cartflows' ),
							'name'         => 'wcf-show-overview-section',
							'value'        => $options['wcf-show-overview-section'],
							'is_fullwidth' => true,
						),
						'wcf-show-details-section'  => array(
							'type'         => 'toggle',
							'label'        => __( 'Enable Order Details', 'cartflows' ),
							'name'         => 'wcf-show-details-section',
							'value'        => $options['wcf-show-details-section'],
							'is_fullwidth' => true,
						),
						'wcf-show-billing-section'  => array(
							'type'         => 'toggle',
							'label'        => __( 'Enable Billing Details', 'cartflows' ),
							'name'         => 'wcf-show-billing-section',
							'value'        => $options['wcf-show-billing-section'],
							'is_fullwidth' => true,
						),
						'wcf-show-shipping-section' => array(
							'type'         => 'toggle',
							'label'        => __( 'Enable Shipping Details', 'cartflows' ),
							'name'         => 'wcf-show-shipping-section',
							'value'        => $options['wcf-show-shipping-section'],
							'is_fullwidth' => true,
						),
					),
				),

				'settings'        => array(
					'title'    => __( 'Advanced', 'cartflows' ),
					'slug'     => 'advanced_setting',
					'priority' => 30,
					'fields'   => array(
						'wcf-tq-text'                  => array(
							'type'          => 'text',
							'label'         => __( 'Thank You Page Text', 'cartflows' ),
							'name'          => 'wcf-tq-text',
							'value'         => $options['wcf-tq-text'],
							'placeholder'   => __( 'Thank you. Your order has been received.', 'cartflows' ),
							'display_align' => 'vertical',
						),
						'wcf-show-tq-redirect-section' => array(
							'type'         => 'toggle',
							'label'        => __( 'Redirect After Purchase', 'cartflows' ),
							'name'         => 'wcf-show-tq-redirect-section',
							'value'        => $options['wcf-show-tq-redirect-section'],
							'help'         => __( 'Enter comma seprated field name. E.g. first_name, last_name', 'cartflows' ),
							'is_fullwidth' => true,
						),
						'wcf-tq-redirect-link'         => array(
							'type'          => 'text',
							'label'         => __( 'Redirect Link', 'cartflows' ),
							'name'          => 'wcf-tq-redirect-link',
							'value'         => $options['wcf-tq-redirect-link'],
							'placeholder'   => __( 'https://', 'cartflows' ),
							'tooltip'       => __( 'Users will be redirected to this URL instead of thank you page.', 'cartflows' ),
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'wcf-show-tq-redirect-section',
										'operator' => '===',
										'value'    => 'yes',
									),
								),
							),
							'display_align' => 'vertical',
						),
					),
				),

			),
		);

		return $settings;
	}

	/**
	 * Get data.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_data( $step_id ) {

			$thanku_data = array();

			// Stored data.
			$stored_meta = get_post_meta( $step_id );

			// Default.
			$thank_meta = self::get_meta_option( $step_id );

			// Set stored and override defaults.
		foreach ( $thank_meta as $key => $value ) {
			if ( array_key_exists( $key, $stored_meta ) ) {
				$thanku_data[ $key ] = ( isset( $stored_meta[ $key ][0] ) ) ? maybe_unserialize( $stored_meta[ $key ][0] ) : '';
			} else {
				$thanku_data[ $key ] = ( isset( $thank_meta[ $key ]['default'] ) ) ? $thank_meta[ $key ]['default'] : '';
			}
		}

		return $thanku_data;

	}

	/**
	 * Get meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function get_meta_option( $post_id ) {

			$meta_option = wcf()->options->get_thankyou_fields( $post_id );

		return $meta_option;

	}

}

/**
 * Kicking this off by calling 'get_instance()' method.
 */
Cartflows_Thankyou_Meta_Data::get_instance();
