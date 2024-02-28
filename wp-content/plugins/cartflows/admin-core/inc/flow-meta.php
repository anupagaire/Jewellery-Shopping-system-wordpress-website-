<?php
/**
 * CartFlows flow Meta Helper.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class flowMeta.
 */
class FlowMeta {


	/**
	 * Get flow meta options.
	 *
	 * @param int $flow_id flow id.
	 */
	public static function get_meta_settings( $flow_id ) {

		$settings      = self::get_settings_fields( $flow_id );
		$settings_data = array(
			'settings' => $settings,
		);
		return $settings_data;
	}


	/**
	 * Page Header Tabs.
	 *
	 * @param int $flow_id id.
	 */
	public static function get_settings_fields( $flow_id ) {

		$settings = array(
			'general'                 => array(
				'title'    => __( 'General ', 'cartflows' ),
				'slug'     => 'general',
				'fields'   => array(
					'flow_slug'     => array(
						'type'          => 'text',
						'name'          => 'post_name',
						'label'         => __( 'Funnel Slug', 'cartflows' ),
						'value'         => get_post_field( 'post_name', $flow_id ),
						'display_align' => 'vertical',
					),
					'sandbox_mode'  => array(
						'type'         => 'toggle',
						'label'        => __( 'Enable Test Mode', 'cartflows' ),
						'name'         => 'wcf-testing',
						'value'        => get_post_meta( $flow_id, 'wcf-testing', true ),
						'tooltip'      => __( 'Test mode will add random products in your funnel if products are not selected in checkout settings, so you can preview it easily while testing.', 'cartflows' ),
						'is_fullwidth' => true,
					),
					'flow_indexing' => array(
						'type'          => 'select',
						'name'          => 'wcf-flow-indexing',
						'label'         => __( 'Disallow indexing', 'cartflows' ),
						'tooltip'       => __( 'It will overwrite the global setting option. To use the global option, set it to default.', 'cartflows' ),
						'display_align' => 'vertical',
						'options'       => array(
							array(
								'value' => '',
								'label' => __( 'Default', 'cartflows' ),
							),
							array(
								'value' => 'disallow',
								'label' => __( 'Yes', 'cartflows' ),
							),
							array(
								'value' => 'allow',
								'label' => __( 'No', 'cartflows' ),
							),
						),
						'value'         => get_post_meta( $flow_id, 'wcf-flow-indexing', true ),
					),
					'script_option' => array(
						'type'          => 'textarea',
						'label'         => __( 'Funnel Custom Script', 'cartflows' ),
						'name'          => 'wcf-flow-custom-script',
						'value'         => get_post_meta( $flow_id, 'wcf-flow-custom-script', true ),
						'tooltip'       => __( 'This custom script will execute on all steps of this funnel.', 'cartflows' ),
						'display_align' => 'vertical',
					),
				),
				'priority' => 10,
			),
			'funnel-advanced-options' => array(
				'title'    => __( 'Global Styling', 'cartflows' ),
				'slug'     => 'funnel_advanced_options',
				'fields'   => array(
					'gcp-enable-option'      => array(
						'type'         => 'toggle',
						'label'        => __( 'Enable Global Styling', 'cartflows' ),
						'name'         => 'wcf-enable-gcp-styling',
						'value'        => get_post_meta( $flow_id, 'wcf-enable-gcp-styling', true ),
						'is_fullwidth' => true,
					),
					'gcp-primary-color'      => array(
						'type'       => 'color-picker',
						'name'       => 'wcf-gcp-primary-color',
						'label'      => __( 'Primary Color', 'cartflows' ),
						'value'      => wcf()->options->get_flow_meta_value( $flow_id, 'wcf-gcp-primary-color' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => 'wcf-enable-gcp-styling',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'gcp-secondary-color'    => array(
						'type'       => 'color-picker',
						'name'       => 'wcf-gcp-secondary-color',
						'label'      => __( 'Secondary Color', 'cartflows' ),
						'value'      => wcf()->options->get_flow_meta_value( $flow_id, 'wcf-gcp-secondary-color' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => 'wcf-enable-gcp-styling',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'gcp-primary-text-color' => array(
						'type'       => 'color-picker',
						'name'       => 'wcf-gcp-text-color',
						'label'      => __( 'Text Color', 'cartflows' ),
						'value'      => wcf()->options->get_flow_meta_value( $flow_id, 'wcf-gcp-text-color' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => 'wcf-enable-gcp-styling',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'gcp-accent-color'       => array(
						'type'       => 'color-picker',
						'name'       => 'wcf-gcp-accent-color',
						'label'      => __( 'Heading/Accent Color', 'cartflows' ),
						'value'      => wcf()->options->get_flow_meta_value( $flow_id, 'wcf-gcp-accent-color' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => 'wcf-enable-gcp-styling',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
				),
				'priority' => 40,
			),
		);

		return $settings;
	}
}
