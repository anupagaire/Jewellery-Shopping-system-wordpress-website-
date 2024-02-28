<?php
/**
 * Landing post meta box
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
class Cartflows_Landing_Meta_Data extends Cartflows_Step_Meta_Base {


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
	 * Page Header Tabs.
	 *
	 * @param  int   $step_id Post ID.
	 * @param  array $options Post meta.
	 */
	public function get_settings( $step_id, $options = array() ) {

		$this->step_id = $step_id;
		$this->options = $options;

		$tabs = array(
			'settings' => array(
				'title'    => __( 'Settings', 'cartflows' ),
				'id'       => 'settings',
				'class'    => '',
				'icon'     => 'dashicons-info',
				'priority' => 20,
			),

		);

		$settings = $this->get_settings_fields( $step_id );

		$settings_data = array(
			'tabs'     => $tabs,
			'settings' => $settings,
		);

		return $settings_data;
	}

	/**
	 * Page Header Tabs.
	 *
	 * @param int $step_id Step ID.
	 */
	public function get_settings_fields( $step_id ) {

		$next_step_link = wcf()->utils->get_linking_url(
			array( 'class' => 'wcf-next-step' )
		);

		$options  = $this->get_data( $step_id );
		$settings = array(
			'settings' => array(
				'shortcode' => array(
					'title'    => __( 'Shortcode', 'cartflows' ),
					'slug'     => 'shortcode',
					'priority' => 20,
					'fields'   => array(
						'landing-shortcode' => array(
							'type'          => 'text',
							'name'          => 'thankyou-shortcode',
							'label'         => __( 'Next Step Link', 'cartflows' ),
							'value'         => $next_step_link,
							'readonly'      => true,
							'display_align' => 'vertical',
						),
					),
				),
				'general'   => array(
					'title'    => __( 'General', 'cartflows' ),
					'slug'     => 'general',
					'priority' => 10,
					'fields'   => array(
						'slug'                      => array(
							'type'          => 'text',
							'name'          => 'post_name',
							'label'         => __( 'Step Slug', 'cartflows' ),
							'value'         => get_post_field( 'post_name', $step_id ),
							'display_align' => 'vertical',
						),
						'step-note'                 => array(
							'type'          => 'textarea',
							'name'          => 'wcf-step-note',
							'label'         => __( 'Step Note', 'cartflows' ),
							'value'         => get_post_meta( $step_id, 'wcf-step-note', true ),
							'rows'          => 2,
							'cols'          => 38,
							'display_align' => 'vertical',
						),
						'wcf-landing-custom-script' => array(
							'type'          => 'textarea',
							'label'         => __( 'Custom Script', 'cartflows' ),
							'name'          => 'wcf-custom-script',
							'value'         => $options['wcf-custom-script'],
							'display_align' => 'vertical',
							'tooltip'       => __( 'Enter custom JS/CSS. Wrap your custom CSS in style tag.', 'cartflows' ),
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

		$meta_option = wcf()->options->get_landing_fields( $post_id );

		return $meta_option;

	}
}

/**
 * Kicking this off by calling 'get_instance()' method.
 */
Cartflows_Landing_Meta_Data::get_instance();
