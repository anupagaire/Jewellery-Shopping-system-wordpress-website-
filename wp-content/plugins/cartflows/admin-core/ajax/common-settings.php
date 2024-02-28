<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsAdmin\AdminCore\Inc\AdminHelper;

/**
 * Class Flows.
 */
class CommonSettings extends AjaxBase {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events() {

		if ( current_user_can( 'cartflows_manage_settings' ) ) {

			$ajax_events = array(
				'save_global_settings',
				'regenerate_css_for_steps',
			);
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Delete the post meta key for dynamic css to regenerate the it.
	 */
	public function regenerate_css_for_steps() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_regenerate_css_for_steps', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		// Update cartflows asset version to regenerate the dynamic css. We are using the time() function to add the random number.
		update_option( 'cartflows-assets-version', time() );

		$response_data = array(
			'messsage' => __( 'Successfully deleted the dynamic CSS keys!', 'cartflows' ),
		);
		wp_send_json_success( $response_data );

	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_global_settings() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}


		$setting_tab = isset( $_POST['setting_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_tab'] ) ) : '';

		switch ( $setting_tab ) {

			case 'general':
				$this->save_general_settings();
				break;

			case 'permalink':
				$this->save_permalink_settings();
				break;

			case 'other':
				$this->save_other_settings();
				break;

			case 'user_role_manager':
				$this->save_user_roles_management_settings();
				break;

			case 'integrations':
				$this->save_integration_settings();
				break;

			default:
				$this->save_general_settings();

		}

		do_action( 'cartflows_admin_save_global_settings', $setting_tab, 'cartflows_save_global_settings' );



		$response_data = array(
			'messsage' => __( 'Successfully saved data!', 'cartflows' ),
		);
		wp_send_json_success( $response_data );
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_integration_settings() {

		$new_settings = array();

		if ( isset( $_POST['_cartflows_facebook'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_facebook'] ) ); //phpcs:ignore
			$this->update_admin_settings_option( '_cartflows_facebook', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_google_analytics'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_analytics'] ) ); //phpcs:ignore
			$this->update_admin_settings_option( '_cartflows_google_analytics', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_google_auto_address'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_auto_address'] ) ); //phpcs:ignore
			$this->update_admin_settings_option( '_cartflows_google_auto_address', $new_settings, false );
		}
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_other_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = '';

		if ( isset( $_POST['cartflows_delete_plugin_data'] ) ) {
			$new_settings = sanitize_text_field( $_POST['cartflows_delete_plugin_data'] );

		}

		$this->update_admin_settings_option( 'cartflows_delete_plugin_data', $new_settings, false );

		if ( _is_cartflows_pro() ) {
			$this->update_admin_settings_option( 'cartflows_pro_delete_plugin_data', $new_settings, false );
		}

		if ( isset( $_POST['cartflows_stats_report_emails'] ) ) {
			$enable_report_emails = sanitize_text_field( $_POST['cartflows_stats_report_emails'] );
			$this->update_admin_settings_option( 'cartflows_stats_report_emails', $enable_report_emails, false );
		}

		if ( isset( $_POST['cartflows_stats_report_email_ids'] ) ) {

			if ( ! empty( $_POST['cartflows_stats_report_email_ids'] ) ) {
				// Ignoring the sanitization rule as we are sanitizing each email before saving it.
				$emails           = preg_split( "/[\f\r\n]+/", $_POST['cartflows_stats_report_email_ids'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$validated_emails = array();

				foreach ( $emails as $email_id ) {

					if ( is_email( $email_id ) ) {
						array_push( $validated_emails, sanitize_email( $email_id ) );
					}
				}
				$validated_emails = implode( "\n", $validated_emails );
				$this->update_admin_settings_option( 'cartflows_stats_report_email_ids', $validated_emails, false );
			} else {
				$this->update_admin_settings_option( 'cartflows_stats_report_email_ids', '', false );
			}
		}

	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_general_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_common'] ) ) {
			// Ignoring the sanitization rule as we are sanitizing input using our function.
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_common'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$common_settings = get_option( '_cartflows_common', false );
		$new_settings    = wp_parse_args( $new_settings, $common_settings );

		$this->update_admin_settings_option( '_cartflows_common', $new_settings, false );

	}

	/**
	 * Remove cf caps.
	 *
	 * @param object $user_role_obj user role object.
	 *
	 * @return void
	 */
	public function remove_all_cf_cap( $user_role_obj ) {

		$cf_cap = array(
			'cartflows_manage_settings',
			'cartflows_manage_flows_steps',
		);

		foreach ( $cf_cap as $cap ) {
			$user_role_obj->remove_cap( $cap );
		}

	}

	/**
	 * Add cf caps.
	 *
	 * @param object $user_role_obj user role object.
	 * @param string $access_key access key.
	 *
	 * @return void
	 */
	public function add_selected_cf_cap( $user_role_obj, $access_key ) {

		switch ( $access_key ) {

			case 'access_to_cartflows':
				$user_role_obj->add_cap( 'cartflows_manage_settings' );
				$user_role_obj->add_cap( 'cartflows_manage_flows_steps' );
				break;

			case 'access_to_flows_and_step':
				$user_role_obj->add_cap( 'cartflows_manage_flows_steps' );
				break;

			default:
				$user_role_obj->add_cap( '' );
				break;

		}
	}

	/**
	 * Add / Remove custom capability to the user role.
	 *
	 * @param array $new_settings Array of user role capability settings.
	 * @param array $old_settings Array of old user role capability settings.
	 *
	 * @return void
	 */
	public function user_role_management( $new_settings, $old_settings ) {

		foreach ( $new_settings as $user_role => $access_key ) {

			if ( $old_settings[ $user_role ] !== $access_key ) {

				$user_role_obj = get_role( $user_role );

				if ( $user_role_obj ) {

					$this->remove_all_cf_cap( $user_role_obj );

					$this->add_selected_cf_cap( $user_role_obj, $access_key );

				}
			}
		}
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_fb_pixel_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_facebook'] ) ) {
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_facebook'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$this->update_admin_settings_option( '_cartflows_facebook', $new_settings, false );

	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_user_roles_management_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_roles'] ) ) {
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_roles'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$old_settings = AdminHelper::get_admin_settings_option( '_cartflows_roles' );

		$new_settings = wp_parse_args( $new_settings, $old_settings );

		$this->update_admin_settings_option( '_cartflows_roles', $new_settings, false );

		// Add/Remove capability.
		$this->user_role_management( $new_settings, $old_settings );
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_google_analytics_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_google_analytics'] ) ) {
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_analytics'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$this->update_admin_settings_option( '_cartflows_google_analytics', $new_settings, true );
	}

	/**
	 * Save Auto Fields settings.
	 *
	 * @return void
	 */
	public function save_address_autocomplete_setting() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_google_auto_address'] ) ) {
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_auto_address'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$this->update_admin_settings_option( '_cartflows_google_auto_address', $new_settings, true );
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_permalink_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		if ( isset( $_POST['reset'] ) ) {
			$_POST['_cartflows_permalink'] = array(
				'permalink'           => CARTFLOWS_STEP_PERMALINK_SLUG,
				'permalink_flow_base' => CARTFLOWS_FLOW_PERMALINK_SLUG,
				'permalink_structure' => '',
			);

		}
		$new_settings = array();
		if ( isset( $_POST['_cartflows_permalink'] ) ) {
			$cartflows_permalink_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_permalink'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( empty( $cartflows_permalink_settings['permalink'] ) ) {
				$new_settings['permalink'] = CARTFLOWS_STEP_PERMALINK_SLUG;
			} else {
				$new_settings['permalink'] = $cartflows_permalink_settings['permalink'];
			}

			if ( empty( $cartflows_permalink_settings['permalink_flow_base'] ) ) {
				$new_settings['permalink_flow_base'] = CARTFLOWS_FLOW_PERMALINK_SLUG;
			} else {
				$new_settings['permalink_flow_base'] = $cartflows_permalink_settings['permalink_flow_base'];
			}

			$new_settings['permalink_structure'] = $cartflows_permalink_settings['permalink_structure'];

		}

		$this->update_admin_settings_option( '_cartflows_permalink', $new_settings, false );

		update_option( 'cartflows_permalink_refresh', true );
	}

	/**
	 * Update admin settings.
	 *
	 * @param string $key key.
	 * @param bool   $value key.
	 * @param bool   $network network.
	 */
	public function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}

	/**
	 * Save settings.
	 *
	 * @param array $input_settings settimg data.
	 */
	public function sanitize_form_inputs( $input_settings = array() ) {
		$new_settings = array();
		foreach ( $input_settings as $key => $val ) {

			if ( is_array( $val ) ) {
				foreach ( $val as $k => $v ) {
					$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
				}
			} else {
				$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
			}
		}
		return $new_settings;
	}
}
