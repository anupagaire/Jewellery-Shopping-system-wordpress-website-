<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\Wizard\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\Wizard\Ajax\AjaxBase;
use CartflowsAdmin\AdminCore\Ajax\Importer;
use CartflowsAdmin\Wizard\Inc\WizardCore;

/**
 * Class Steps.
 */
class Wizard extends AjaxBase {

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
	 * Register ajax events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_events() {

		$ajax_events = array(
			'page_builder_save_option',
			'user_onboarding',
			'wizard_activate_plugin',
			'get_global_flow_list',
			'get_single_store_checkout_flow',
			'import_store_checkout',
			'onboarding_completed',
			'onboarding_exit',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Update option.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function onboarding_exit() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_onboarding_exit', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$step = isset( $_POST['current_step'] ) ? sanitize_text_field( wp_unslash( $_POST['current_step'] ) ) : '';

		update_option( 'wcf_setup_skipped', true );
		update_option( 'wcf_exit_setup_step', $step );

		wp_send_json_success();
	}

	/**
	 * Update option.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function onboarding_completed() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_onboarding_completed', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		update_option( 'wcf_setup_complete', true );
		delete_option( 'wcf_exit_setup_step' );
		delete_option( 'wcf_setup_skipped' );

		wp_send_json_success();
	}

	/**
	 * Save page builder.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function page_builder_save_option() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_page_builder_save_option', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$page_builder = isset( $_POST['page_builder'] ) ? sanitize_text_field( wp_unslash( $_POST['page_builder'] ) ) : '';

		$wcf_settings = get_option( '_cartflows_common', array() );

		if ( false !== strpos( $page_builder, 'beaver-builder' ) ) {
			$page_builder = 'beaver-builder';
		}

		if ( false !== strpos( $page_builder, 'gutenberg' ) ) {
			$page_builder = 'gutenberg';
		}

		$wcf_settings['default_page_builder'] = $page_builder;

		update_option( '_cartflows_common', $wcf_settings );

		wp_send_json_success(
			array( 'plugin' => $page_builder )
		);
	}

	/**
	 * Optin step.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function user_onboarding() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_user_onboarding', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$target_url    = CARTFLOWS_TEMPLATES_URL . 'wp-json/cartflows-server/v1/add-subscriber';
		$response_body = array();

		$email     = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
		$user_name = isset( $_POST['user_fname'] ) ? sanitize_text_field( wp_unslash( $_POST['user_fname'] ) ) : '';

		if ( empty( $email ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Please enter your email ID.', 'cartflows' ),
				)
			);
		}

		$api_args = array(
			'timeout' => 90, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			'body'    => array(
				'user_email'     => $email,
				'user_fname'     => $user_name,
				'source'         => 'cartflows',
				'add-subscriber' => true,
			),

		);

		$response = wp_remote_post( $target_url, $api_args );

		$has_errors = $this->is_api_error( $response );

		if ( $has_errors['error'] ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => $has_errors['error_message'],
				)
			);
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Store in option to send the weekly emails.
		update_site_option( 'cartflows_stats_report_email_ids', $email );

		wp_send_json_success(
			array(
				'success' => $response_body['success'],
				'message' => $response_body['message'] ? $response_body['message'] : '',
			)
		);
	}

	/**
	 * Check is error in the received response.
	 *
	 * @param object $response Received API Response.
	 * @return array $result Error result.
	 */
	public function is_api_error( $response ) {

		$result = array(
			'error'         => false,
			'error_message' => __( 'Oops! Something went wrong. Please refresh the page and try again.', 'cartflows' ),
			'error_code'    => 0,
		);

		if ( is_wp_error( $response ) ) {
			$result['error']         = true;
			$result['error_message'] = $response->get_error_message();
			$result['error_code']    = $response->get_error_code();
		} elseif ( ! empty( wp_remote_retrieve_response_code( $response ) ) && ! in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201, 204 ), true ) ) {
			$result['error']         = true;
			$result['error_message'] = wp_remote_retrieve_response_message( $response );
			$result['error_code']    = wp_remote_retrieve_response_code( $response );
		}

		return $result;
	}

	/**
	 * Update step title.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wizard_activate_plugin() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_wizard_activate_plugin', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$plugin_init = isset( $_POST['plugin_init'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_init'] ) ) : '';
		$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';

		$do_sliently = true;

		$exclude_do_silently = array(
			'woo-cart-abandonment-recovery',
		);

		if ( in_array( $plugin_slug, $exclude_do_silently, true ) ) {
			$do_sliently = false;
		}

		if ( false !== strpos( $plugin_slug, 'beaver-builder' ) ) {
			$plugin_init = $plugin_slug . '/fl-builder.php';
		}

		$activate = activate_plugin( $plugin_init, '', false, $do_sliently );

		if ( is_wp_error( $activate ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => $activate->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'success' => true,
				'message' => $plugin_slug,
			)
		);
	}

	/**
	 * Fetch all the available flows for global checkout.
	 *
	 * @since 1.10.0
	 */
	public function get_global_flow_list() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_get_global_flow_list', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$page_builder = isset( $_POST['page_builder'] ) ? sanitize_text_field( wp_unslash( $_POST['page_builder'] ) ) : '';

		$page       = 1;
		$flows_list = array();

		if ( empty( $page_builder ) ) {
			$response_data = array( 'message' => __( 'Please select any of the page builder to display the ready templates.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		// Check is the template library updated or not.
		if ( 'no' === \CartFlows_Batch_Process::get_instance()->get_last_export_checksums( 'store-checkout' ) ) {
			// Get the cached flows as no changes are made in the template library.
			$cached_flows = get_site_option( 'cartflows-store-checkout-' . $page_builder . '-flows-and-steps-' . $page );

			$flows_list = ! empty( $cached_flows ) ? $cached_flows : array();
		}

		// If the cached flow list is empty then make a new request and fetch the templates.
		if ( empty( $flows_list ) ) {
			// Fetch new list of templates.
			$flows_list = $this->import_templates( $page_builder, $page );

			// Update the cheksum after importing the latest templates.
			\CartFlows_Batch_Process::get_instance()->update_latest_checksums( 'store-checkout' );
		}

		/**
		 * Redirect to the new step edit screen
		 */
		$response_data = array(
			'message' => __( 'Successful!', 'cartflows' ),
			'flows'   => $flows_list,
		);

		wp_send_json_success( $response_data );
	}

	/**
	 * Import
	 *
	 * @since 1.0.14
	 * @since 1.6.15 Added page no.
	 * @param  string  $page_builder page builder slug.
	 * @param  integer $page Page number.
	 * @return array
	 */
	public function import_templates( $page_builder, $page = 1 ) {

		$api_args = array(
			'timeout' => 30, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
		);

		$sites_and_pages = array();
		$site_url        = wcf()->get_site_url();

		$query_args = array(
			'per_page'      => 100,
			'page'          => $page,
			'flow_category' => array(
				'include_terms' => array( 'store-checkout' ),
			),
		);

		$api_url = add_query_arg( $query_args, $site_url . 'wp-json/cartflows-server/v1/flows-and-steps/' );

		$response = wp_remote_get( $api_url, $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {

			$sites_and_pages = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $sites_and_pages['code'] ) ) {
				$message = isset( $sites_and_pages['message'] ) ? $sites_and_pages['message'] : '';
				if ( ! empty( $message ) ) {
					wcf()->logger->sync_log( 'HTTP Request Error: ' . $message );
				} else {
					wcf()->logger->sync_log( 'HTTP Request Error!' );
				}
			} else {
				$option_name = 'cartflows-store-checkout-' . $page_builder . '-flows-and-steps-' . $page;

				update_site_option( $option_name, $sites_and_pages['flows'] );
			}
		} else {
			wcf()->logger->sync_log( 'API Error: ' . $response->get_error_message() );
		}

		return $sites_and_pages['flows'];
	}

	/**
	 * Get store flow.
	 */
	public function get_single_store_checkout_flow() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_get_single_store_checkout_flow', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$flow_id = isset( $_POST['flow_id'] ) ? absint( $_POST['flow_id'] ) : 0;

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_flow( $flow_id );

		wp_send_json_success( $response['data'] );
	}

	/**
	 * Import the flow template for the store checkout.
	 * Once the import is done, then set it's checkout page as a Global Checkout.
	 *
	 * @since 1.10.0
	 * @return void
	 */
	public function import_store_checkout() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_import_store_checkout', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}
		// $_POST['flow'] is the JSON, There is nothing to sanitize JSON as it is data format not data type.
		$flow = isset( $_POST['flow'] ) ? json_decode( stripslashes( $_POST['flow'] ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $flow ) ) {
			$response_data = array( 'message' => __( 'No flow ID found. Please select atleast one flow to import.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_flow( $flow['ID'] );

		if ( is_wp_error( $response['data'] ) ) {

			/* translators: %1$s: html tag, %2$s: link html start %3$s: link html end */
			$btn = sprintf( __( 'Request timeout error. Please check if the firewall or any security plugin is blocking the outgoing HTTP/HTTPS requests to templates.cartflows.com or not. %1$sTo resolve this issue, please check this %2$sarticle%3$s.', 'cartflows' ), '<br><br>', '<a target="_blank" href="https://cartflows.com/docs/request-timeout-error-while-importing-the-flow-step-templates/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs">', '</a>' );

			wp_send_json_error(
				array(
					'message'        => $response['data']->get_error_message(),
					'call_to_action' => $btn,
					'data'           => $response,
				)
			);
		}

		// Do license validation if showing Pro flows.
		$license_status = isset( $response['data']['licence_status'] ) ? $response['data']['licence_status'] : '';

		// If license is invalid then.
		if ( 'valid' !== $license_status ) {

			$cf_pro_status = WizardCore::get_instance()->get_plugin_status( 'cartflows-pro/cartflows-pro.php' );

			$title = '';
			$msg   = '';
			if ( 'not-installed' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$msg   = sprintf( __( 'To import this template, CartFlows Pro Required! %1$sUpgrade to CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" href="https://cartflows.com/">', '</a>' );
				$title = __( 'CartFlows Pro Required', 'cartflows' );
			} elseif ( 'inactive' === $cf_pro_status ) {
				$title = __( 'CartFlows Pro Required', 'cartflows' );
				/* translators: %1$s: link html start, %2$s: link html end*/
				$msg = sprintf( __( 'Activate the CartFlows Pro to import the flow! %1$sActivate CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?plugin_status=search&paged=1&s=CartFlows+Pro' ) . '">', '</a>' );
			} elseif ( 'active' === $cf_pro_status ) {
				$title = __( 'Invalid License Key', 'cartflows' );
				/* translators: %1$s: link html start, %2$s: link html end*/
				$msg = sprintf( __( 'No valid license key found! %1$sActivate license%2$s', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?cartflows-license-popup' ) . '">', '</a>' );
			}

			wp_send_json_error(
				array(
					'message'        => $title,
					'call_to_action' => $msg,
					'data'           => $response,
				)
			);
		}
		// Do license validation if showing pro flows.

		/**
		 * Create Flow
		 */
		$new_flow_post = array(
			'post_title'   => isset( $flow['title'] ) ? sanitize_text_field( wp_unslash( $flow['title'] ) ) : '',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
		);

		// Insert the post into the database.
		$new_flow_id = wp_insert_post( $new_flow_post );

		if ( is_wp_error( $new_flow_id ) ) {
			wp_send_json_error( $new_flow_id->get_error_message() );
		}

		wcf()->logger->import_log( '✓ Flow Created! Flow ID: ' . $new_flow_id . ' - Remote Flow ID - ' . $flow['ID'] );

		/**
		 * Update Store Checkout flow meta
		 */

		// Set newly created flow as store checkout.
		update_option( '_cartflows_store_checkout', $new_flow_id );

		// Remove old checkout key if available.
		delete_option( '_cartflows_old_global_checkout' );

		// reset global checkout on store checkout creation.
		$common_settings                             = \Cartflows_Helper::get_common_settings();
		$common_settings['global_checkout']          = '';
		$common_settings['override_global_checkout'] = 'disable';
		update_option( '_cartflows_common', $common_settings );

		/**
		 * Import All Steps
		 */
		$steps = isset( $flow['steps'] ) ? $flow['steps'] : array();

		$is_checkout_available = false;
		$flow_steps            = array();

		// Get importer function instance to call the import steps function.
		$importer = new Importer();

		foreach ( $steps as $key => $step ) {
			// Separate out the Checkout step to set it as Global Checkout.
			if ( 'checkout' === $step['type'] ) {
				$is_checkout_available = true;
			}

			if ( in_array( $step['type'], array( 'upsell', 'downsell' ), true ) && ( ! _is_cartflows_pro() || is_wcf_starter_plan() ) ) {
				continue;
			}

			$importer->import_single_step(
				array(
					'step' => array(
						'id'    => $step['ID'],
						'title' => $step['title'],
						'type'  => $step['type'],
					),
					'flow' => array(
						'id' => $new_flow_id,
					),
				),
				'cartflows_import_store_checkout'
			);
		}

		/**
		 * Redirect to the new flow edit screen
		 */
		$response_data = array(
			'success'      => true,
			'message'      => __( 'Successfully imported the Flow!', 'cartflows' ),
			'items'        => $flow,
			'redirect_url' => admin_url( 'post.php?action=edit&post=' . $new_flow_id ),
			'new_flow_id'  => $new_flow_id,
		);

		wcf()->logger->import_log( 'COMPLETE! Importing Flow' );

		wp_send_json_success( $response_data );
	}

}
