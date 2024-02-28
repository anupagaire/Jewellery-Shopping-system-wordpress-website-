<?php
/**
 * Optin post meta fields
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
class Cartflows_Optin_Meta_Data extends Cartflows_Step_Meta_Base {


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
		add_filter( 'cartflows_admin_optin_step_meta_fields', array( $this, 'filter_values' ), 10, 2 );

		// Step API data.
		add_filter( 'cartflows_admin_optin_step_data', array( $this, 'add_optin_step_api_data' ), 10, 2 );
	}

	/**
	 * Add required data to api.
	 *
	 * @param  array $api_data data.
	 * @param  int   $step_id step id.
	 * @since 1.10.0
	 */
	public function add_optin_step_api_data( $api_data, $step_id ) {

		$field_data                 = $this->custom_fields_data( $step_id );
		$api_data['custom_fields']  = $field_data;
		$api_data['billing_fields'] = $field_data['billing_fields'];
		return $api_data;
	}

		/**
		 * Add custom meta fields
		 *
		 * @param array $post_id post id.
		 */
	public function custom_fields_data( $post_id ) {

		$billing_fields = $this->get_field_settings( $post_id, 'billing', '' );

		$custom_fields = array(
			'extra_fields'   => array(
				'fields' => array(
					'enable-optin-field-editor' => array(
						'type'         => 'checkbox',
						'label'        => __( 'Enable Custom Field Editor', 'cartflows' ),
						'name'         => 'wcf-optin-enable-custom-fields',
						'is_fullwidth' => true,
					),
				),
			),
			'billing_fields' => array(
				'fields' => $billing_fields,
			),
		);

		return $custom_fields;
	}

	/**
	 * Add custom meta fields
	 *
	 * @param string $post_id post id.
	 * @param array  $fields fields.
	 * @param array  $new_fields new fields.
	 */
	public function get_field_settings( $post_id, $fields, $new_fields ) {

		$ordered_billing_fields = wcf()->options->get_optin_meta_value( $post_id, 'wcf-optin-fields-billing' );

		if ( isset( $ordered_billing_fields ) && ! empty( $ordered_billing_fields ) ) {
			$billing_fields = $ordered_billing_fields;

		} else {
			$billing_fields = Cartflows_Helper::get_optin_fields( 'billing', $post_id );
		}

		if ( isset( $billing_fields ) && ! empty( $billing_fields ) ) {
			$data_array = $billing_fields;
		}

		if ( isset( $new_fields ) && ! empty( $new_fields ) && is_array( $new_fields ) ) {
			$data_array = $new_fields;
		}
		$field_args = array();

		foreach ( $data_array as $key => $value ) {
			$field_args = $this->prepare_field_arguments( $key, $value, $post_id, $fields );

			foreach ( $field_args as $arg_key => $arg_val ) {

				if ( ! in_array( $arg_key, $value, true ) ) {

					$data_array[ $key ][ $arg_key ] = $arg_val;
				}
			}

			$data_array[ $key ] = Cartflows_Helper::get_instance()->prepare_custom_field_settings( $data_array[ $key ], $key, $field_args, $fields, 'optin' );
		}

		return $data_array;
	}

	/**
	 * Filter checkout values
	 *
	 * @param  array $options options.
	 * @param  int   $step_id post id.
	 */
	public function filter_values( $options, $step_id ) {

		if ( ! empty( $options['wcf-optin-product'][0] ) ) {

			$product_id  = intval( $options['wcf-optin-product'][0] );
			$product_obj = wc_get_product( $product_id );

			if ( $product_obj ) {
				$options['wcf-optin-product'] = array(
					'value' => $product_id,
					'label' => $product_obj->get_name() . ' (#' . $product_obj->get_id() . ')',
				);
			}
		}

		if ( isset( $options['wcf-optin-fields-billing'] ) ) {
			$options['wcf-optin-fields-billing'] = $this->get_field_settings( $step_id, 'billing', '' );
		}

		return $options;
	}

	/**
	 * Page Header Tabs
	 *
	 * @param  int   $step_id Post meta.
	 * @param  array $options options.
	 */
	public function get_settings( $step_id, $options = array() ) {

		$common_tabs = $this->common_tabs();
		$add_tabs    = array(
			'products'          => array(
				'title'    => __( 'Products', 'cartflows' ),
				'id'       => 'products',
				'class'    => '',
				'icon'     => 'dashicons-format-aside',
				'priority' => 20,
			),
			'optin_form_fields' => array(
				'title'    => __( 'Optin Form', 'cartflows' ),
				'id'       => 'optin_form_fields',
				'class'    => '',
				'icon'     => 'dashicons-format-aside',
				'priority' => 30,
			),
			'settings'          => array(
				'title'    => __( 'Settings', 'cartflows' ),
				'id'       => 'settings',
				'class'    => '',
				'icon'     => 'dashicons-format-aside',
				'priority' => 40,
			),
		);

		$options = $this->get_data( $step_id );

		$tabs            = array_merge( $common_tabs, $add_tabs );
		$settings        = $this->get_settings_fields( $step_id );
		$design_settings = $this->get_design_fields( $step_id );

		$settings_data = array(
			'tabs'            => $tabs,
			'settings'        => $settings,
			'page_settings'   => $this->get_page_settings( $step_id ),
			'design_settings' => $design_settings,
		);

		return $settings_data;
	}

	/**
	 * Get Page Settings Options
	 *
	 * @param int $step_id Step ID.
	 */
	public function get_page_settings( $step_id ) {

		$options = $this->get_data( $step_id );

		$settings = array(
			'settings' => array(
				'product' => array(
					'title'    => __( 'Product', 'cartflows' ),
					'priority' => 30,
					'fields'   => array(
						'optin-product' => array(
							'type'                   => 'product',
							'name'                   => 'wcf-optin-product',
							'label'                  => __( 'Select Free Product', 'cartflows' ),
							'help'                   => __( 'Select Free and Virtual product only.', 'cartflows' ),
							'allowed_product_types'  => array( 'simple' ),
							'placeholder'            => __( 'Type to search for a product...', 'cartflows' ),
							'excluded_product_types' => array(),
							'include_product_types'  => array(),
						),
						'optin-doc'     => array(
							'type'    => 'doc',
							/* translators: %1$1s: link html start, %2$12: link html end*/
							'content' => sprintf( __( 'For more information about the CartFlows Optin step please %1$sClick here.%2$s', 'cartflows' ), '<a href="https://cartflows.com/docs/introducing-cartflows-optin-feature/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
						),
					),

				),

			),
		);

		return $settings;
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
				'shortcode'      => array(
					'title'    => __( 'Shortcode', 'cartflows' ),
					'slug'     => 'shortcodes',
					'priority' => 10,
					'fields'   => array(
						'optin-shortcode' => array(
							'type'          => 'text',
							'name'          => 'optin-shortcode',
							'label'         => __( 'Optin Form', 'cartflows' ),
							'value'         => '[cartflows_optin]',
							'help'          => esc_html__( 'Add this shortcode to your optin page', 'cartflows' ),
							'readonly'      => true,
							'display_align' => 'vertical',
						),
					),
				),

				'global'         => array(
					'title'    => __( 'Global Settings', 'cartflows' ),
					'slug'     => 'global_settings',
					'priority' => 20,
					'fields'   => array(
						'primary-color'       => array(
							'type'   => 'color-picker',
							'name'   => 'wcf-primary-color',
							'label'  => __( 'Primary Color', 'cartflows' ),
							'value'  => $options['wcf-primary-color'],
							'withBg' => true,
						),
						'heading-font-family' => array(
							'type'          => 'font-family',
							'name'          => 'wcf-base-font-family',
							'label'         => __( 'Font Family', 'cartflows' ),
							'value'         => $options['wcf-base-font-family'],
							'display_align' => 'vertical',
						),
					),
				),

				'input-fields'   => array(
					'title'    => __( 'Input Fields', 'cartflows' ),
					'slug'     => 'input_fields',
					'priority' => 30,
					'fields'   => array(
						'style'              => array(
							'type'          => 'select',
							'label'         => __( 'Style', 'cartflows' ),
							'name'          => 'wcf-input-fields-skins',
							'value'         => $options['wcf-input-fields-skins'],
							'display_align' => 'vertical',
							'options'       => array(
								array(
									'value' => 'default',
									'label' => esc_html__( 'Default', 'cartflows' ),
								),
								array(
									'value' => 'floating-labels',
									'label' => esc_html__( 'Floating Labels', 'cartflows' ),
								),
							),

						),
						'input-font-family'  => array(
							'type'              => 'font-family',
							'for'               => 'wcf-input',
							'label'             => esc_html__( 'Font Family', 'cartflows' ),
							'name'              => 'wcf-input-font-family',
							'value'             => $options['wcf-input-font-family'],
							'font_weight_name'  => 'wcf-input-font-weight',
							'font_weight_value' => $options['wcf-input-font-weight'],
							'font_weight_for'   => 'wcf-input',
							'display_align'     => 'vertical',
						),

						'input-font-size'    => array(
							'type'          => 'select',
							'label'         => __( 'Size', 'cartflows' ),
							'name'          => 'wcf-input-field-size',
							'value'         => $options['wcf-input-field-size'],
							'display_align' => 'vertical',
							'options'       => array(
								array(
									'value' => '33px',
									'label' => esc_html__( 'Extra Small', 'cartflows' ),
								),
								array(
									'value' => '38px',
									'label' => esc_html__( 'Small', 'cartflows' ),
								),
								array(
									'value' => '44px',
									'label' => esc_html__( 'Medium', 'cartflows' ),
								),
								array(
									'value' => '58px',
									'label' => esc_html__( 'Large', 'cartflows' ),
								),
								array(
									'value' => '68px',
									'label' => esc_html__( 'Extra Large', 'cartflows' ),
								),
								array(
									'value' => 'custom',
									'label' => esc_html__( 'Custom', 'cartflows' ),
								),
							),
						),
						'input-bottom-space' => array(
							'type'          => 'number',
							'label'         => __( 'Top Bottom Spacing', 'cartflows' ),
							'name'          => 'wcf-field-tb-padding',
							'value'         => $options['wcf-field-tb-padding'],
							'display_align' => 'vertical',
						),
						'input-left-space'   => array(
							'type'          => 'number',
							'label'         => __( 'Left Right Spacing', 'cartflows' ),
							'name'          => 'wcf-field-lr-padding',
							'value'         => $options['wcf-field-lr-padding'],
							'display_align' => 'vertical',
						),
						'input-label-color'  => array(
							'type'   => 'color-picker',
							'label'  => __( 'Label Color', 'cartflows' ),
							'name'   => 'wcf-field-label-color',
							'value'  => $options['wcf-field-label-color'],
							'withBg' => true,
						),
						'input-text-color'   => array(
							'type'   => 'color-picker',
							'label'  => __( 'Text / Placeholder Color', 'cartflows' ),
							'name'   => 'wcf-field-color',
							'value'  => $options['wcf-field-color'],
							'withBg' => true,
						),
						'input-bg-color'     => array(
							'type'   => 'color-picker',
							'label'  => __( 'Background Color', 'cartflows' ),
							'name'   => 'wcf-field-bg-color',
							'value'  => $options['wcf-field-bg-color'],
							'withBg' => true,
						),
						'input-border-color' => array(
							'type'   => 'color-picker',
							'label'  => __( 'Border Color', 'cartflows' ),
							'name'   => 'wcf-field-border-color',
							'value'  => $options['wcf-field-border-color'],
							'withBg' => true,
						),

					),
				),

				'button-options' => array(
					'title'    => __( 'Submit Button', 'cartflows' ),
					'slug'     => 'button_options',
					'priority' => 40,
					'fields'   => array(
						'button-font-size'          => array(
							'type'          => 'number',
							'label'         => __( 'Font Size', 'cartflows' ),
							'name'          => 'wcf-submit-font-size',
							'value'         => $options['wcf-submit-font-size'],
							'display_align' => 'vertical',
						),
						'button-font-family'        => array(
							'type'              => 'font-family',
							'for'               => 'wcf-button',
							'label'             => esc_html__( 'Font Family', 'cartflows' ),
							'name'              => 'wcf-button-font-family',
							'value'             => $options['wcf-button-font-family'],
							'font_weight_name'  => 'wcf-button-font-weight',
							'font_weight_value' => $options['wcf-button-font-weight'],
							'font_weight_for'   => 'wcf-button',
							'display_align'     => 'vertical',

						),

						'button-bottom-space'       => array(
							'type'          => 'select',
							'label'         => __( 'Size', 'cartflows' ),
							'name'          => 'wcf-submit-button-size',
							'value'         => $options['wcf-submit-button-size'],
							'display_align' => 'vertical',
							'options'       => array(
								array(
									'value' => '33px',
									'label' => esc_html__( 'Extra Small', 'cartflows' ),
								),
								array(
									'value' => '38px',
									'label' => esc_html__( 'Small', 'cartflows' ),
								),
								array(
									'value' => '44px',
									'label' => esc_html__( 'Medium', 'cartflows' ),
								),
								array(
									'value' => '58px',
									'label' => esc_html__( 'Large', 'cartflows' ),
								),
								array(
									'value' => '68px',
									'label' => esc_html__( 'Extra Large', 'cartflows' ),
								),
								array(
									'value' => 'custom',
									'label' => esc_html__( 'Custom', 'cartflows' ),
								),
							),
						),
						'button-top-space'          => array(
							'type'          => 'number',
							'label'         => __( 'Top Bottom Spacing', 'cartflows' ),
							'name'          => 'wcf-submit-tb-padding',
							'value'         => $options['wcf-submit-tb-padding'],
							'display_align' => 'vertical',
						),
						'button-left-space'         => array(
							'type'          => 'number',
							'label'         => __( 'Left Right Spacing', 'cartflows' ),
							'name'          => 'wcf-submit-lr-padding',
							'value'         => $options['wcf-submit-lr-padding'],
							'display_align' => 'vertical',
						),
						'button-text-position'      => array(
							'type'          => 'select',
							'label'         => __( 'Position', 'cartflows' ),
							'name'          => 'wcf-submit-button-position',
							'value'         => $options['wcf-submit-button-position'],
							'display_align' => 'vertical',
							'options'       => array(
								array(
									'value' => 'left',
									'label' => esc_html__( 'Left', 'cartflows' ),
								),
								array(
									'value' => 'center',
									'label' => esc_html__( 'Center', 'cartflows' ),
								),
								array(
									'value' => 'right',
									'label' => esc_html__( 'Right', 'cartflows' ),
								),
							),
						),
						'button-bg-color'           => array(
							'type'   => 'color-picker',
							'label'  => __( 'Text Color', 'cartflows' ),
							'name'   => 'wcf-submit-color',
							'value'  => $options['wcf-submit-color'],
							'withBg' => true,
						),
						'button-text-hover-color'   => array(
							'type'   => 'color-picker',
							'label'  => __( 'Text Hover Color', 'cartflows' ),
							'name'   => 'wcf-submit-hover-color',
							'value'  => $options['wcf-submit-hover-color'],
							'withBg' => true,
						),
						'button-bg-color'           => array(
							'type'   => 'color-picker',
							'label'  => __( 'Background Color', 'cartflows' ),
							'name'   => 'wcf-submit-bg-color',
							'value'  => $options['wcf-submit-bg-color'],
							'withBg' => true,
						),
						'button-bg-hover-color'     => array(
							'type'   => 'color-picker',
							'label'  => __( 'Background Hover Color', 'cartflows' ),
							'name'   => 'wcf-submit-bg-hover-color',
							'value'  => $options['wcf-submit-bg-hover-color'],
							'withBg' => true,
						),
						'button-border-color'       => array(
							'type'   => 'color-picker',
							'label'  => __( 'Border Color', 'cartflows' ),
							'name'   => 'wcf-submit-border-color',
							'value'  => $options['wcf-submit-border-color'],
							'withBg' => true,
						),
						'button-border-hover-color' => array(
							'type'   => 'color-picker',
							'label'  => __( 'Border Hover Color', 'cartflows' ),
							'name'   => 'wcf-submit-border-hover-color',
							'value'  => $options['wcf-submit-border-hover-color'],
							'withBg' => true,
						),
					),

				),
			),
		);

		return $settings;
	}

	/**
	 * Get settings data.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_settings_fields( $step_id ) {

		$options = $this->get_data( $step_id );

		$settings = array(
			'settings' => array(
				'general'  => array(
					'title'    => __( 'General', 'cartflows' ),
					'slug'     => 'general',
					'priority' => 10,
					'fields'   => array(
						'slug'                    => array(
							'type'          => 'text',
							'name'          => 'post_name',
							'label'         => __( 'Step Slug', 'cartflows' ),
							'value'         => get_post_field( 'post_name', $step_id ),
							'display_align' => 'vertical',
						),
						'step-note'               => array(
							'type'          => 'textarea',
							'name'          => 'wcf-step-note',
							'label'         => __( 'Step Note', 'cartflows' ),
							'value'         => get_post_meta( $step_id, 'wcf-step-note', true ),
							'rows'          => 2,
							'cols'          => 38,
							'display_align' => 'vertical',
						),
						'wcf-optin-custom-script' => array(
							'type'          => 'textarea',
							'label'         => __( 'Custom Script', 'cartflows' ),
							'name'          => 'wcf-custom-script',
							'value'         => $options['wcf-custom-script'],
							'tooltip'       => __( 'Enter custom JS/CSS. Wrap your custom CSS in style tag.', 'cartflows' ),
							'display_align' => 'vertical',
						),
					),
				),

				'settings' => array(
					'title'    => __( 'Optin Settings', 'cartflows' ),
					'slug'     => 'fields_settings',
					'priority' => 20,
					'fields'   => array(
						'button-text'                => array(
							'type'          => 'text',
							'label'         => __( 'Button Text', 'cartflows' ),
							'name'          => 'wcf-submit-button-text',
							'value'         => $options['wcf-submit-button-text'],
							'placeholder'   => __( 'Submit', 'cartflows' ),
							'display_align' => 'vertical',
						),
						'optin-pass-fields'          => array(
							'type'         => 'toggle',
							'label'        => __( 'Pass Fields as URL Parameters', 'cartflows' ),
							'name'         => 'wcf-optin-pass-fields',
							'value'        => $options['wcf-optin-pass-fields'],
							'help'         => __( 'You can pass specific fields from the form to next step as URL query parameters.', 'cartflows' ),
							'is_fullwidth' => true,
						),
						'optin-pass-specific-fields' => array(
							'type'          => 'text',
							'label'         => __( 'Enter form field', 'cartflows' ),
							'name'          => 'wcf-optin-pass-specific-fields',
							'value'         => $options['wcf-optin-pass-specific-fields'],
							'help'          => __( 'Enter comma seprated field name. E.g. first_name, last_name', 'cartflows' ),
							'placeholder'   => __( 'Fields to pass, separated by commas', 'cartflows' ),
							/* translators: %s: link */
							'desc'          => sprintf( __( 'You can pass field value as a URL parameter to the next step. %1$sLearn More >>%2$s', 'cartflows' ), '<a href="https://cartflows.com/docs/pass-variable-as-query-parameters-to-url/" target="_blank">', '</a>' ),
							'display_align' => 'vertical',
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'wcf-optin-pass-fields',
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
	 * Fetch default width of checkout fields by key.
	 *
	 * @param string $field_key field key.
	 * @return int
	 */
	public function get_default_optin_field_width( $field_key ) {

		$default_width = 100;
		switch ( $field_key ) {
			case 'billing_first_name':
			case 'billing_last_name':
				$default_width = 50;
				break;
			default:
				$default_width = 100;
				break;
		}

		return $default_width;
	}

	/**
	 * Prepare HTML data for billing and shipping fields.
	 *
	 * @param string  $field checkout field key.
	 * @param array   $field_data checkout field object.
	 * @param integer $post_id chcekout post id.
	 * @param string  $type checkout field type.
	 * @return array
	 */
	public function prepare_field_arguments( $field, $field_data, $post_id, $type ) {

		$field_name = '';
		if ( isset( $field_data['label'] ) ) {
			$field_name = $field_data['label'];
		}

		if ( isset( $field_data['width'] ) ) {
			$width = $field_data['width'];
		} else {
			$width = $this->get_default_optin_field_width( $field );
		}

		if ( isset( $field_data['enabled'] ) ) {
			$is_enabled = true === $field_data['enabled'] ? 'yes' : 'no';
		} else {
			$is_enabled = 'yes';
		}

		$field_args = array(
			'type'              => ( isset( $field_data['type'] ) && ! empty( $field_data['type'] ) ) ? $field_data['type'] : '',
			'label'             => $field_name,
			'name'              => 'wcf-' . $field,
			'key'               => $field,
			'placeholder'       => isset( $field_data['placeholder'] ) ? $field_data['placeholder'] : '',
			'min'               => isset( $field_data['min'] ) ? $field_data['min'] : '',
			'max'               => isset( $field_data['max'] ) ? $field_data['max'] : '',
			'width'             => $width,
			'enabled'           => $is_enabled,
			'after'             => 'Enable',
			'section'           => $type,
			'custom'            => isset( $field_data['custom'] ) ? $field_data['custom'] : false,
			'custom_attributes' => isset( $field_data['custom_attributes'] ) ? wc_clean( $field_data['custom_attributes'] ) : array(),
			'default'           => isset( $field_data['default'] ) ? $field_data['default'] : '',
			'required'          => ( isset( $field_data['required'] ) && true == $field_data['required'] ) ? 'yes' : 'no',
			'optimized'         => ( isset( $field_data['optimized'] ) && true == $field_data['optimized'] ) ? 'yes' : 'no',
			'options'           => ( isset( $field_data['options'] ) && ! empty( $field_data['options'] ) ) ? implode( ',', $field_data['options'] ) : '',
			'show_in_email'     => ( isset( $field_data['show_in_email'] ) && true == $field_data['show_in_email'] ) ? 'yes' : 'no',
		);

		return $field_args;
	}

	/**
	 * Get data.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_data( $step_id ) {

		$optin_data = array();

		// Stored data.
		$stored_meta = get_post_meta( $step_id );

		// Default.
		$default_data = self::get_meta_option( $step_id );

		// Set stored and override defaults.
		foreach ( $default_data as $key => $value ) {
			if ( array_key_exists( $key, $stored_meta ) ) {
				$optin_data[ $key ] = ( isset( $stored_meta[ $key ][0] ) ) ? maybe_unserialize( $stored_meta[ $key ][0] ) : '';
			} else {
				$optin_data[ $key ] = ( isset( $default_data[ $key ]['default'] ) ) ? $default_data[ $key ]['default'] : '';
			}
		}

		return $optin_data;

	}

	/**
	 * Get meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function get_meta_option( $post_id ) {

		$meta_option = wcf()->options->get_optin_fields( $post_id );

		return $meta_option;

	}

}

/**
 * Kicking this off by calling 'get_instance()' method.
 */
Cartflows_Optin_Meta_Data::get_instance();

