<?php
/**
 * Importer
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Inc\AdminMenu;
use CartflowsAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsAdmin\AdminCore\Inc\AdminHelper;

/**
 * Importer.
 */
class Importer extends AjaxBase {

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
	 * Register AJAX Events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_events() {

		$ajax_events = array(
			'create_flow',
			'import_flow',

			'create_step',

			'import_step',

			'activate_plugin',

			'sync_library',
			'request_count',
			'import_sites',
			'update_library_complete',
			'export_flow',

			'get_flows_list',

			'import_json_flow',
			'export_all_flows',
			'update_step',
		);

		$this->init_ajax_events( $ajax_events );

		add_action( 'admin_footer', array( $this, 'json_importer_popup_wrapper' ) );
	}

	/**
	 * Export Flows.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function export_all_flows() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! check_ajax_referer( 'cartflows_export_all_flows', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$export = \CartFlows_Importer::get_instance();
		$flows  = $export->get_all_flow_export_data();

		if ( ! empty( $flows ) && is_array( $flows ) && count( $flows ) > 0 ) {

			$response_data = array(
				'message' => __( 'Funnel exported successfully', 'cartflows' ),
				'flows'   => $flows,
				'export'  => true,
			);

		} else {
			$response_data = array(
				'message' => __( 'No Funnels to export', 'cartflows' ),
				'flows'   => $flows,
				'export'  => false,
			);
		}

		wp_send_json_success( $response_data );

	}

	/**
	 * Import the Flow.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function import_json_flow() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_import_json_flow', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		// $_POST['flow_data'] is the JSON, There is nothing to sanitize JSON as it is data format not data type.
		$flow_data = ( isset( $_POST['flow_data'] ) ) ? json_decode( stripslashes( $_POST['flow_data'] ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$response_data = array(
			'message'      => 'Error occured. Funnel not imported.',
			'flow_data'    => $flow_data,
			'redirect_url' => admin_url( 'admin.php?page=' . CARTFLOWS_SLUG ),
		);

		if ( is_array( $flow_data ) ) {
			$imported_flow = \CartFlows_Importer::get_instance()->import_from_json_data( $flow_data );

			$response_data['message']      = 'Funnel Imported successfully';
			$response_data['redirect_url'] = admin_url( 'admin.php?page=' . CARTFLOWS_SLUG . '&path=flows' );

		}

		wp_send_json_success( $response_data );
	}

	/**
	 * Import Wrapper.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function json_importer_popup_wrapper() {
		echo '<div id="wcf-json-importer"></div>';
	}

	/**
	 * Export Step
	 */
	public function export_flow() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_export_flow', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}
		$flow_id = ( isset( $_POST['flow_id'] ) ) ? absint( $_POST['flow_id'] ) : '';

		if ( ! $flow_id ) {
			$response_data = array( 'message' => __( 'Invalid flow ID.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$flows[] = \CartFlows_Importer::get_instance()->get_flow_export_data( $flow_id );

		$response_data = array(
			'message'   => __( 'Funnel exported successfully', 'cartflows' ),
			'flow_name' => sanitize_title( get_the_title( $flow_id ) ),
			'flows'     => wp_json_encode( $flows ),
		);
		wp_send_json_success( $response_data );

	}

	/**
	 * Update library complete
	 */
	public function update_library_complete() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_update_library_complete', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$templates = ! empty( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';

		\CartFlows_Batch_Process::get_instance()->update_latest_checksums( $templates );

		update_site_option( 'cartflows-batch-is-complete', 'no' );
		update_site_option( 'cartflows-manual-sync-complete', 'yes' );

		$response_data = array( 'message' => 'SUCCESS: cartflows_import_sites' );
		wp_send_json_success( $response_data );
	}

	/**
	 * Import Sites
	 */
	public function import_sites() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_import_sites', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$page_no  = isset( $_POST['page_no'] ) ? absint( $_POST['page_no'] ) : '';
		$template = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';
		if ( $page_no ) {
			$sites_and_pages = \Cartflows_Batch_Processing_Sync_Library::get_instance()->import_sites( $page_no, $template );
			wp_send_json_success(
				array(
					'message'         => 'SUCCESS: cartflows_import_sites',
					'sites_and_pages' => $sites_and_pages,
				)
			);
		}

		wp_send_json_error(
			array(
				'message' => 'SUCCESS: cartflows_import_sites',
			)
		);
	}

	/**
	 * Sync Library
	 */
	public function sync_library() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_sync_library', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$templates = ! empty( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';

		/**
		 * LOGIC
		 */
		if ( 'no' === \CartFlows_Batch_Process::get_instance()->get_last_export_checksums( $templates ) ) {
			wp_send_json_success( 'updated' );
		}

		$status = \CartFlows_Batch_Process::get_instance()->test_cron();
		if ( is_wp_error( $status ) ) {
			$import_with = 'ajax';
		} else {
			$import_with = 'batch';
			// Process import.
			\CartFlows_Batch_Process::get_instance()->process_batch( $templates );
		}

		$response_data = array(
			'message' => 'SUCCESS: cartflows_sync_library',
			'status'  => $import_with,
		);

		wp_send_json_success( $response_data );
	}

	/**
	 * Request Count
	 */
	public function request_count() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_request_count', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$templates = ! empty( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';

		$total_requests = \CartFlows_Batch_Process::get_instance()->get_total_requests( '', $templates );
		if ( $total_requests ) {
			wp_send_json_success(
				array(
					'message' => 'SUCCESS: cartflows_request_count',
					'count'   => $total_requests,
				)
			);
		}

		wp_send_json_error(
			array(
				'message' => 'ERROR: cartflows_request_count',
				'count'   => $total_requests,
			)
		);
	}

	/**
	 * Create Step
	 */
	public function create_step() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_create_step', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		wcf()->logger->import_log( 'STARTED! Importing Step' );

		$flow_id = ( isset( $_POST['flow_id'] ) ) ? absint( $_POST['flow_id'] ) : 0;

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
			wp_send_json_error(
				array(
					array(
						'status'  => false,
						'message' => __( 'Invalid Funnel Id has been provided.', 'cartflows' ),
					),
				)
			);
		}

		$step_type  = ( isset( $_POST['step_type'] ) ) ? sanitize_text_field( $_POST['step_type'] ) : '';
		$step_title = ( isset( $_POST['step_title'] ) ) ? sanitize_text_field( $_POST['step_title'] ) : '';
		$step_title = isset( $_POST['step_name'] ) && ! empty( $_POST['step_name'] ) ? sanitize_text_field( wp_unslash( $_POST['step_name'] ) ) : $step_title;

		// Create new step.
		$new_step_id = \CartFlows_Importer::get_instance()->create_step( $flow_id, $step_type, $step_title );

		if ( empty( $new_step_id ) ) {
			/* translators: %s: step ID */
			wp_send_json_error( sprintf( __( 'Invalid step id %1$s.', 'cartflows' ), $new_step_id ) );
		}

		/**
		 * Redirect to the new flow edit screen
		 */
		$response_data = array(
			'message'      => __( 'Successfully created the step!', 'cartflows' ),
			'redirect_url' => admin_url( 'post.php?action=edit&post=' . $new_step_id ),
		);
		wp_send_json_success( $response_data );

	}

	/**
	 * Active Plugin
	 */
	public function activate_plugin() {
		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_activate_plugin', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		\wp_clean_plugins_cache();

		$plugin_init = ( isset( $_POST['init'] ) ) ? sanitize_text_field( $_POST['init'] ) : '';

		$do_sliently = true;

		$exclude_do_silently = array(
			'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php',
		);

		if ( in_array( $plugin_init, $exclude_do_silently, true ) ) {
			$do_sliently = false;
		}

		$activate = \activate_plugin( $plugin_init, '', false, $do_sliently );

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
				'message' => 'Plugin activated successfully.',
			)
		);
	}

	/**
	 * Create the Flow.
	 */
	public function create_flow() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_create_flow', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		// Create post object.
		$new_flow_post = array(
			'post_title'   => isset( $_POST['flow_name'] ) ? sanitize_text_field( wp_unslash( $_POST['flow_name'] ) ) : '',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
		);

		// Insert the post into the database.
		$flow_id = wp_insert_post( $new_flow_post );

		if ( is_wp_error( $flow_id ) ) {
			wp_send_json_error( $flow_id->get_error_message() );
		}

		$store_checkout = isset( $_POST['store_checkout'] ) ? sanitize_text_field( wp_unslash( $_POST['store_checkout'] ) ) : '';

		// If is store checkout update store_checkout options.
		if ( 'true' === $store_checkout ) {
			update_option( '_cartflows_store_checkout', $flow_id );

			// reset global checkout on store checkout creation.
			$common_settings                             = \Cartflows_Helper::get_common_settings();
			$common_settings['global_checkout']          = '';
			$common_settings['override_global_checkout'] = 'disable';

			update_option( '_cartflows_common', $common_settings );
		}

		$flow_steps = array();

		if ( wcf()->is_woo_active ) {
			if ( 'true' === $store_checkout ) {
				$steps_data = array(
					'order-form'         => array(
						'title' => __( 'Checkout', 'cartflows' ),
						'type'  => 'checkout',
					),
					'order-confirmation' => array(
						'title' => __( 'Thank You', 'cartflows' ),
						'type'  => 'thankyou',
					),
				);
			} else {
				$steps_data = array(
					'sales'              => array(
						'title' => __( 'Sales Landing', 'cartflows' ),
						'type'  => 'landing',
					),
					'order-form'         => array(
						'title' => __( 'Checkout (Woo)', 'cartflows' ),
						'type'  => 'checkout',
					),
					'order-confirmation' => array(
						'title' => __( 'Thank You (Woo)', 'cartflows' ),
						'type'  => 'thankyou',
					),
				);
			}
		} else {
			$steps_data = array(
				'landing'  => array(
					'title' => __( 'Landing', 'cartflows' ),
					'type'  => 'landing',
				),
				'thankyou' => array(
					'title' => __( 'Thank You', 'cartflows' ),
					'type'  => 'landing',
				),
			);
		}

		foreach ( $steps_data as $slug => $data ) {

			$post_content = '';
			$step_type    = trim( $data['type'] );

			// Create new step.
			$step_id = wp_insert_post(
				array(
					'post_type'    => CARTFLOWS_STEP_POST_TYPE,
					'post_title'   => $data['title'],
					'post_content' => $post_content,
					'post_status'  => 'publish',
				)
			);

			// Return the error.
			if ( is_wp_error( $step_id ) ) {
				wp_send_json_error( $step_id->get_error_message() );
			}

			if ( $step_id ) {

				$flow_steps[] = array(
					'id'    => $step_id,
					'title' => $data['title'],
					'type'  => $step_type,
				);

				// Insert post meta.
				update_post_meta( $step_id, 'wcf-flow-id', $flow_id );
				update_post_meta( $step_id, 'wcf-step-type', $step_type );

				// Set taxonomies.
				wp_set_object_terms( $step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
				wp_set_object_terms( $step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

				update_post_meta( $step_id, '_wp_page_template', 'cartflows-default' );
			}
		}

		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		/**
		 * Redirect to the new flow edit screen
		 */
		$response_data = array(
			'message'      => __( 'Successfully created the Funnel!', 'cartflows' ),
			'redirect_url' => admin_url( 'post.php?action=edit&post=' . $flow_id ),
			'flow_id'      => $flow_id,
		);
		wp_send_json_success( $response_data );
	}

	/**
	 * Create the Flow.
	 */
	public function import_flow() {

		wcf()->logger->import_log( 'STARTED! Importing Flow' );

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_import_flow', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}
		// $_POST['flow'] is the JSON, There is nothing to sanitize JSON as it is data format not data type.
		$flow = isset( $_POST['flow'] ) ? json_decode( stripslashes( $_POST['flow'] ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_flow( $flow['ID'] );

		$is_error = AdminHelper::has_api_error( $response['data'] );

		if ( $is_error['error'] ) {

			wp_send_json_error(
				array(
					'error_code'     => $is_error['error_code'],
					'call_to_action' => $is_error['call_to_action'],
					'message'        => $is_error['error_message'],
					'data'           => $response,
				)
			);
		}

		$license_status = isset( $response['data']['licence_status'] ) ? $response['data']['licence_status'] : '';

		// If license is invalid then.
		if ( 'valid' !== $license_status ) {

			$cf_pro_status = AdminMenu::get_instance()->get_plugin_status( 'cartflows-pro/cartflows-pro.php' );

			$cta = '';
			$btn = '';
			if ( 'not-installed' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$btn = sprintf( __( 'CartFlows Pro Required! %1$sUpgrade to CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" href="https://cartflows.com/">', '</a>' );
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( 'To import the premium flow %1$supgrade to CartFlows Pro%2$s.', 'cartflows' ), '<a target="_blank" href="https://cartflows.com/">', '</a>' );
			} elseif ( 'inactive' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$btn = sprintf( __( 'Activate the CartFlows Pro to import the flow! %1$sActivate CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?plugin_status=search&paged=1&s=CartFlows+Pro' ) . '">', '</a>' );
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( 'To import the premium flow %1$sactivate Cartflows Pro%2$s and validate the license key.', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?plugin_status=search&paged=1&s=CartFlows+Pro' ) . '">', '</a>' );
			} elseif ( 'active' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$btn = sprintf( __( 'Invalid License Key! %1$sActivate CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?cartflows-license-popup' ) . '">', '</a>' );
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( 'To import the premium flow %1$sactivate CartFlows Pro%2$s.', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?cartflows-license-popup' ) . '">', '</a>' );
			}

			wp_send_json_error(
				array(
					'message'        => \ucfirst( $license_status ) . ' license key!',
					'call_to_action' => $btn,
					'data'           => $response,
				)
			);
		}

		if ( empty( $flow ) ) {
			$response_data = array( 'message' => __( 'Funnel data not found.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Create Flow
		 */
		$new_flow_post = array(
			'post_title'   => isset( $_POST['flow_name'] ) ? sanitize_text_field( wp_unslash( $_POST['flow_name'] ) ) : '',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
		);

		// Insert the post into the database.
		$new_flow_id = wp_insert_post( $new_flow_post );

		if ( is_wp_error( $new_flow_id ) ) {
			wp_send_json_error( $new_flow_id->get_error_message() );
		}

		$store_checkout = isset( $_POST['store_checkout'] ) ? sanitize_text_field( wp_unslash( $_POST['store_checkout'] ) ) : '';

		// If is global checkout update store_checkout options.
		if ( 'true' === $store_checkout ) {
			update_option( '_cartflows_store_checkout', $new_flow_id );

			// reset global checkout on store checkout creation.
			$common_settings                             = \Cartflows_Helper::get_common_settings();
			$common_settings['global_checkout']          = '';
			$common_settings['override_global_checkout'] = 'disable';

			update_option( '_cartflows_common', $common_settings );
		}

		wcf()->logger->import_log( '✓ Flow Created! Flow ID: ' . $new_flow_id . ' - Remote Flow ID - ' . $flow['ID'] );

		/**
		 * All Import Steps
		 */
		$steps = isset( $flow['steps'] ) ? $flow['steps'] : array();

		foreach ( $steps as $key => $step ) {

			if ( in_array( $step['type'], array( 'upsell', 'downsell' ), true ) && ( ! _is_cartflows_pro() || is_wcf_starter_plan() ) ) {
				continue;
			}

			$this->import_single_step(
				array(
					'step'              => array(
						'id'    => $step['ID'],
						'title' => $step['title'],
						'type'  => $step['type'],
					),
					'flow'              => array(
						'id' => $new_flow_id,
					),
					'is_store_checkout' => isset( $_POST['store_checkout'] ) ? sanitize_text_field( wp_unslash( $_POST['store_checkout'] ) ) : '',
				),
				'cartflows_import_flow'
			);
		}

		/**
		 * Redirect to the new flow edit screen
		 */
		$response_data = array(
			'message'      => __( 'Successfully imported the Flow!', 'cartflows' ),
			'items'        => $flow,
			'redirect_url' => admin_url( 'post.php?action=edit&post=' . $new_flow_id ),
			'new_flow_id'  => $new_flow_id,
		);

		wcf()->logger->import_log( 'COMPLETE! Importing Flow' );

		wp_send_json_success( $response_data );
	}

	/**
	 * Import Step
	 *
	 * @return void
	 */
	public function import_step() {

		wcf()->logger->import_log( 'STARTED! Importing Step' );

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_import_step', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		// $_POST['step'] is the JSON, There is nothing to sanitize JSON as it is data format not data type. Hence sanitizing decoded array below.
		$step = isset( $_POST['step'] ) ? json_decode( stripslashes( $_POST['step'] ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Return if step data not found in the import request.
		if ( empty( $step ) ) {
			wp_send_json_error( array( 'message' => __( 'Step data ID not found for import.', 'cartflows' ) ) );
		}

		// Sanitizing decoded array.
		$step = array_map( 'sanitize_text_field', $step );

		$flow_id = isset( $_POST['flow_id'] ) ? absint( $_POST['flow_id'] ) : 0;

		$remote_flow_id = isset( $_POST['remote_flow_id'] ) ? absint( $_POST['remote_flow_id'] ) : 0;

		// Return if the remote flow ID is blank or not found in the request.
		if ( empty( $remote_flow_id ) || empty( $flow_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Funnel ID not found in the request.', 'cartflows' ) ) );
		}

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_flow( $remote_flow_id );

		if ( is_wp_error( $response['data'] ) ) {
			/* translators: %1$s: html tag, %2$s: link html start %3$s: link html end */
			$btn = sprintf( __( 'Request timeout error. Please check if the firewall or any security plugin is blocking the outgoing HTTP/HTTPS requests to templates.cartflows.com or not. %1$1sTo resolve this issue, please check this %2$2sarticle%3$3s.', 'cartflows' ), '<br><br>', '<a target="_blank" href="https://cartflows.com/docs/request-timeout-error-while-importing-the-flow-step-templates/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs">', '</a>' );

			wp_send_json_error(
				array(
					'message'        => $response['data']->get_error_message(),
					'call_to_action' => $btn,
					'data'           => $response,
				)
			);
		}

		$license_status = isset( $response['data']['licence_status'] ) ? $response['data']['licence_status'] : '';

		// If license is invalid then.
		if ( 'valid' !== $license_status ) {

			$cf_pro_status = AdminMenu::get_instance()->get_plugin_status( 'cartflows-pro/cartflows-pro.php' );

			$msg = '';
			$cta = '';
			if ( 'not-installed' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( '%1$sUpgrade to CartFlows Pro.%2$s', 'cartflows' ), '<a target="_blanks" class="wcf-button wcf-primary-button" href="https://cartflows.com/">', '</a>' );
				$msg = __( 'To import the premium step, please upgrade to CartFlows Pro', 'cartflows' );
			} elseif ( 'inactive' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( '%1$sActivate CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" class="wcf-button wcf-primary-button" href="' . admin_url( 'plugins.php?plugin_status=search&paged=1&s=CartFlows+Pro' ) . '">', '</a>' );
				$msg = __( 'To import the premium step activate Cartflows Pro and validate the license key.', 'cartflows' );
			} elseif ( 'active' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( '%1$sActivate CartFlows Pro License %2$s', 'cartflows' ), '<a target="_blank" class="wcf-button wcf-primary-button" href="' . admin_url( 'plugins.php?cartflows-license-popup' ) . '">', '</a>' );
				$msg = __( 'To import the premium step activate the CartFlows Pro.', 'cartflows' );
			}

			wp_send_json_error(
				array(
					'message'        => \ucfirst( $license_status ) . ' license key! ' . $msg,
					'call_to_action' => $cta,
					'data'           => $response,
				)
			);
		}

		$step['title'] = isset( $_POST['step_name'] ) && ! empty( $_POST['step_name'] ) ? sanitize_text_field( wp_unslash( $_POST['step_name'] ) ) : $step['title'];
		// Create steps.
		$this->import_single_step(
			array(
				'step' => array(
					'id'    => $step['ID'],
					'title' => $step['title'],
					'type'  => $step['type'],
				),
				'flow' => array(
					'id' => $flow_id,
				),
			),
			'cartflows_import_step'
		);

		wcf()->logger->import_log( 'COMPLETE! Importing Step' );

		if ( empty( $step ) ) {
			$response_data = array( 'message' => __( 'Step data not found.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Redirect to the new step edit screen
		 */
		$response_data = array(
			'message' => __( 'Successfully imported the Step!', 'cartflows' ),
		);

		wcf()->logger->import_log( 'COMPLETE! Importing Step' );

		wp_send_json_success( $response_data );

	}

	/**
	 * Updates post content of chosen template.
	 * working only for Store Checkout.
	 *
	 * @return void
	 * @since X.X.X
	 */
	public function update_step() {

		wcf()->logger->import_log( 'STARTED! Updating Step' );

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_update_step', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		// $_POST['step'] is the JSON, There is nothing to sanitize JSON as it is data format not data type. Hence sanitizing decoded array below.
		$step = isset( $_POST['step'] ) ? json_decode( stripslashes( $_POST['step'] ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// Sanitizing decoded array.
		$step    = array_map( 'sanitize_text_field', $step );
		$flow_id = isset( $_POST['flow_id'] ) ? absint( $_POST['flow_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? absint( $_POST['step_id'] ) : 0;

		$remote_flow_id = isset( $_POST['remote_flow_id'] ) ? absint( $_POST['remote_flow_id'] ) : 0;

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_flow( $remote_flow_id );
		if ( is_wp_error( $response['data'] ) ) {
			/* translators: %1$s: html tag, %2$s: link html start %3$s: link html end */
			$btn = sprintf( __( 'Request timeout error. Please check if the firewall or any security plugin is blocking the outgoing HTTP/HTTPS requests to templates.cartflows.com or not. %1$1sTo resolve this issue, please check this %2$2sarticle%3$3s.', 'cartflows' ), '<br><br>', '<a target="_blank" href="https://cartflows.com/docs/request-timeout-error-while-importing-the-flow-step-templates/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs">', '</a>' );

			wp_send_json_error(
				array(
					'message'        => $response['data']->get_error_message(),
					'call_to_action' => $btn,
					'data'           => $response,
				)
			);
		}

		$license_status = isset( $response['data']['licence_status'] ) ? $response['data']['licence_status'] : '';

		// If license is invalid then.
		if ( 'valid' !== $license_status ) {

			$cf_pro_status = AdminMenu::get_instance()->get_plugin_status( 'cartflows-pro/cartflows-pro.php' );

			$cta = '';
			if ( 'not-installed' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( 'Upgrade to %1$sCartFlows Pro.%2$s', 'cartflows' ), '<a target="_blanks" href="https://cartflows.com/">', '</a>' );
			} elseif ( 'inactive' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( '%1$sActivate CartFlows Pro%2$s', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?plugin_status=search&paged=1&s=CartFlows+Pro' ) . '">', '</a>' );
			} elseif ( 'active' === $cf_pro_status ) {
				/* translators: %1$s: link html start, %2$s: link html end*/
				$cta = sprintf( __( 'CartFlows Pro license is not active. Activate %1$sCartFlows Pro License %2$s', 'cartflows' ), '<a target="_blank" href="' . admin_url( 'plugins.php?cartflows-license-popup' ) . '">', '</a>' );
			}

			wp_send_json_error(
				array(
					'message' => \ucfirst( $license_status ) . ' license key! ' . $cta,
					'data'    => $response,
				)
			);
		}

		if ( empty( $remote_flow_id ) ) {
			$response_data = array( 'message' => __( 'Funnel data not found.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}
		$step['title'] = isset( $_POST['step_name'] ) && ! empty( $_POST['step_name'] ) ? sanitize_text_field( wp_unslash( $_POST['step_name'] ) ) : $step['title'];
		// Create steps.
		$this->update_single_step(
			array(
				'step'         => array(
					'id'    => $step['ID'],
					'title' => $step['title'],
					'type'  => $step['type'],
				),
				'flow'         => array(
					'id' => $flow_id,
				),
				'current_step' => array(
					'id' => $step_id,
				),
			)
		);

		wcf()->logger->import_log( 'COMPLETE! Importing Step' );

		if ( empty( $step ) ) {
			$response_data = array( 'message' => __( 'Step data not found.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Redirect to the new step edit screen
		 */
		$response_data = array(
			'message' => __( 'Successfully imported the Step!', 'cartflows' ),
		);

		wcf()->logger->import_log( 'COMPLETE! Importing Step' );

		wp_send_json_success( $response_data );

	}

	/**
	 * Update Sinple Step
	 *
	 * @param array $args Rest API Arguments.
	 * @return void
	 */
	public function update_single_step( $args = array() ) {

		wcf()->logger->import_log( 'STARTED! Updating Step' );

		$step_id     = isset( $args['step']['id'] ) ? absint( $args['step']['id'] ) : 0;
		$new_step_id = isset( $args['current_step']['id'] ) ? absint( $args['current_step']['id'] ) : '';

		if ( empty( $step_id ) || empty( $new_step_id ) ) {
			/* translators: %s: step ID */
			wp_send_json_error( sprintf( __( 'Invalid step id %1$s or post id %2$s.', 'cartflows' ), $step_id, $new_step_id ) );
		}

		wcf()->logger->import_log( 'Remote Step ' . $step_id . ' for local flow "' . get_the_title( $new_step_id ) . '" [' . $new_step_id . ']' );

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_template( $step_id );
		wcf()->logger->import_log( wp_json_encode( $response ) );

		if ( 'divi' === \Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
			if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {

				update_post_meta( $new_step_id, 'divi_content', $response['data']['divi_content'] );

				wp_update_post(
					array(
						'ID'           => $new_step_id,
						'post_content' => $response['data']['divi_content'],
					)
				);
			}
		}

		if ( 'gutenberg' === \Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
			if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {
				wp_update_post(
					array(
						'ID'           => $new_step_id,
						'post_content' => $response['data']['divi_content'],
					)
				);
			}
		}

		// Import Post Meta.
		$this->import_post_meta( $new_step_id, $response );

		/* Imported Step */
		update_post_meta( $new_step_id, 'cartflows_imported_step', 'yes' );

		do_action( 'cartflows_import_complete' );

		// Batch Process.
		do_action( 'cartflows_after_template_import', $new_step_id, $response );

		wcf()->logger->import_log( 'COMPLETE! Importing Step' );

	}

	/**
	 * Create Simple Step
	 *
	 * @param array  $args Rest API Arguments.
	 * @param string $action action name to check nonce.
	 *
	 * @return void
	 */
	public function import_single_step( $args, $action ) {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( $action, 'security', false ) ) {
			$response_data = array( 'message' => __( 'Nonce verification failed.', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		wcf()->logger->import_log( 'STARTED! Importing Step' );

		$step_id           = isset( $args['step']['id'] ) ? absint( $args['step']['id'] ) : 0;
		$step_title        = isset( $args['step']['title'] ) ? $args['step']['title'] : '';
		$step_type         = isset( $args['step']['type'] ) ? $args['step']['type'] : '';
		$flow_id           = isset( $args['flow']['id'] ) ? absint( $args['flow']['id'] ) : '';
		$is_store_checkout = isset( $args['is_store_checkout'] ) ? $args['is_store_checkout'] : '';

		// create steps only for checkout and thankyou if store checkout.
		// This logic will be removed once we have store checkout templates on server.
		if ( 'true' === $is_store_checkout && ! in_array( $step_type, array( 'checkout', 'thankyou' ), true ) ) {
			return;
		}

		// Create new step.
		$new_step_id = \CartFlows_Importer::get_instance()->create_step( $flow_id, $step_type, $step_title );

		if ( empty( $step_id ) || empty( $new_step_id ) ) {
			/* translators: %s: step ID */
			wp_send_json_error( sprintf( __( 'Invalid step id %1$s or post id %2$s.', 'cartflows' ), $step_id, $new_step_id ) );
		}

		wcf()->logger->import_log( 'Remote Step ' . $step_id . ' for local flow "' . get_the_title( $new_step_id ) . '" [' . $new_step_id . ']' );

		// Get single step Rest API response.
		$response = \CartFlows_API::get_instance()->get_template( $step_id );

		wcf()->logger->import_log( wp_json_encode( $response ) );

		// Return if there is an error while importing the step template.
		if ( ! $response['success'] ) {
			$response_data = $response['data'];
			$error_code    = wp_remote_retrieve_response_code( $response_data );
			$error_msge    = json_decode( wp_remote_retrieve_body( $response_data ) );

			wp_send_json_error(
				array(
					'error_code' => $error_code,
					'message'    => $error_msge->message,
					'data'       => $response['data'],
					'success'    => $response['success'],
				)
			);
		}

		if ( 'divi' === \Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
			if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {

				update_post_meta( $new_step_id, 'divi_content', $response['data']['divi_content'] );

				wp_update_post(
					array(
						'ID'           => $new_step_id,
						'post_content' => $response['data']['divi_content'],
					)
				);
			}
		}

		if ( 'gutenberg' === \Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
			if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {
				wp_update_post(
					array(
						'ID'           => $new_step_id,
						'post_content' => $response['data']['divi_content'],
					)
				);
			}
		}

		// Import Post Meta.
		$this->import_post_meta( $new_step_id, $response );

		if ( 'checkout' === $step_type ) {

			$posted_data = array(
				'primary_color' => isset( $_POST['primary_color'] ) ? sanitize_text_field( wp_unslash( $_POST['primary_color'] ) ) : '',
				'site_logo'     => isset( $_POST['site_logo'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['site_logo'] ) ) : '',
			);

			$this->update_store_checkout_template_data( $new_step_id, $response, $posted_data );
		}

		/* Imported Step */
		update_post_meta( $new_step_id, 'cartflows_imported_step', 'yes' );

		do_action( 'cartflows_import_complete' );

		// Batch Process.
		do_action( 'cartflows_after_template_import', $new_step_id, $response );

		wcf()->logger->import_log( 'COMPLETE! Importing Step' );

	}

	/**
	 * Import Post Meta
	 *
	 * @since 1.0.0
	 *
	 * @param  integer $post_id  Post ID.
	 * @param  array   $response  Post meta.
	 * @return void
	 */
	public function import_post_meta( $post_id, $response ) {

		$metadata = apply_filters( 'cartflows_template_import_meta_data', (array) $response['post_meta'] );

		$exclude_meta_keys = \Cartflows_Helper::get_instance()->get_meta_keys_to_exclude_from_import( $post_id );

		foreach ( $metadata as $meta_key => $meta_value ) {

			if ( in_array( $meta_key, $exclude_meta_keys, true ) ) {
				continue;
			}

			$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';

			if ( $meta_value ) {

				if ( is_serialized( $meta_value, true ) ) {
					$raw_data = maybe_unserialize( stripslashes( $meta_value ) );
				} elseif ( is_array( $meta_value ) ) {
					$raw_data = json_decode( stripslashes( $meta_value ), true );
				} else {
					$raw_data = $meta_value;
				}

				if ( '_elementor_data' === $meta_key ) {

					if ( is_array( $raw_data ) ) {
						$raw_data = wp_slash( wp_json_encode( $raw_data ) );
					} else {
						$raw_data = wp_slash( $raw_data );
					}
				}

				if ( '_elementor_data' !== $meta_key && '_elementor_draft' !== $meta_key && '_fl_builder_data' !== $meta_key && '_fl_builder_draft' !== $meta_key ) {
					if ( is_array( $raw_data ) ) {
						wcf()->logger->import_log( '✓ Added post meta ' . $meta_key /* . ' | ' . wp_json_encode( $raw_data ) */ );
					} else {
						if ( ! is_object( $raw_data ) ) {
							wcf()->logger->import_log( '✓ Added post meta ' . $meta_key /* . ' | ' . $raw_data */ );
						}
					}
				}

				update_post_meta( $post_id, $meta_key, $raw_data );
			}
		}
	}

	/**
	 * Find the Checkout block and set the Primary color and site logo provided by the user.
	 *
	 * @since 1.10.0
	 *
	 * @param int   $post_id newly created steps ID.
	 * @param array $response data received from from the imported step.
	 * @param array $posted_data post data.
	 *
	 * @return void
	 */
	public function update_store_checkout_template_data( $post_id, $response, $posted_data ) {

		$store_checkout_id   = get_option( '_cartflows_store_checkout', false );
		$current_flow_id     = (int) wcf()->utils->get_flow_id_from_step_id( $post_id );
		$default_page_bulder = \Cartflows_Helper::get_common_setting( 'default_page_builder' );

		if ( empty( $posted_data['primary_color'] ) && empty( $posted_data['site_logo'] ) ) {
			return;
		}

		if ( $store_checkout_id !== $current_flow_id ) {
			return;
		}

		if ( 'elementor' === $default_page_bulder ) {

			$metadata = (array) $response['post_meta'];

			foreach ( $metadata as $meta_key => $meta_value ) {

				$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';

				if ( $meta_value ) {

					if ( is_serialized( $meta_value, true ) ) {
						$raw_data = maybe_unserialize( stripslashes( $meta_value ) );
					} elseif ( is_array( $meta_value ) ) {
						$raw_data = json_decode( stripslashes( $meta_value ), true );
					} else {
						$raw_data = $meta_value;
					}

					if ( '_elementor_data' === $meta_key ) {

						$raw_data = json_decode( $raw_data, true );

						// Find the checkout-form and update the primary color.
						$this->elementor_find_and_replace_template_data( $raw_data, $posted_data );

						if ( is_array( $raw_data ) ) {
							$raw_data = wp_slash( wp_json_encode( $raw_data ) );
						} else {
							$raw_data = wp_slash( $raw_data );
						}

						update_post_meta( $post_id, $meta_key, $raw_data );

					}
				}
			}
		} elseif ( 'gutenberg' === $default_page_bulder ) {

				$post   = get_post( $post_id );
				$blocks = parse_blocks( $post->post_content );

			if ( is_array( $blocks ) && ! empty( $blocks ) ) {

				$this->gutenberg_find_and_replace_template_data( $blocks, $posted_data );

				if ( ! empty( $blocks ) ) {
					$serialized_blocks = serialize_blocks( $blocks );

					wp_update_post(
						array(
							'ID'           => $post_id,
							'post_content' => $serialized_blocks,
						)
					);
				}
			}
		} elseif ( 'beaver-builder' === $default_page_bulder ) {
			$data = \FLBuilderModel::get_layout_data( 'published', $post_id );

			if ( ! empty( $data ) ) {

				$this->beaver_builder_find_and_replace_template_data( $data, $posted_data );

				// Update page builder data.
				update_post_meta( $post_id, '_fl_builder_data', $data );
				update_post_meta( $post_id, '_fl_builder_draft', $data );
			}
		}
	}


	/**
	 * Get flows list for preview
	 *
	 * @return void
	 */
	public function get_flows_list() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_get_flows_list', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$flows_list = \Cartflows_Helper::get_instance()->get_flows_and_steps();

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
	 * Get the elementor widget data.
	 *
	 * @param array $elements elements data.
	 * @param array $posted_data posted data.
	 */
	public function elementor_find_and_replace_template_data( &$elements, $posted_data ) {

		foreach ( $elements as &$element ) {

			if ( 'widget' === $element['elType'] && 'checkout-form' === $element['widgetType'] ) {
				$element['settings']['global_primary_color'] = ! empty( $posted_data['primary_color'] ) ? $posted_data['primary_color'] : $element['settings']['global_primary_color'];
			}

			if ( 'widget' === $element['elType'] && 'image' === $element['widgetType'] && isset( $element['settings']['_css_classes'] ) ) {

				if ( str_contains( $element['settings']['_css_classes'], 'cartflows-store-checkout-logo-field' ) ) {
					$element['settings']['image']['url'] = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['url'] : $element['settings']['image']['url'];
					$element['settings']['image']['id']  = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['id'] : $element['settings']['image']['id'];
				}
			}

			if ( ! empty( $element['elements'] ) ) {
				$this->elementor_find_and_replace_template_data( $element['elements'], $posted_data );
			}
		}

	}

	/**
	 * Get the block data.
	 *
	 * @param array $elements elements data.
	 * @param array $posted_data posted data.
	 */
	public function gutenberg_find_and_replace_template_data( &$elements, $posted_data ) {

		foreach ( $elements as &$element ) {
			if ( 'wcfb/checkout-form' === $element['blockName'] ) {
				// Update the element with the data.
				$element['attrs']['globalbgColor'] = ! empty( $posted_data['primary_color'] ) ? $posted_data['primary_color'] : $element['attrs']['globalbgColor'];
			}

			if ( 'uagb/info-box' === $element['blockName'] && isset( $element['attrs']['className'] ) ) {

				if ( str_contains( $element['attrs']['className'], 'cartflows-store-checkout-logo-field' ) ) {
					$element['attrs']['iconImage']['id']                   = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['id'] : $element['attrs']['iconImage']['id'];
					$element['attrs']['iconImage']['url']                  = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['url'] : $element['attrs']['iconImage']['url'];
					$element['attrs']['iconImage']['link']                 = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['url'] : $element['attrs']['iconImage']['link'];
					$element['attrs']['iconImage']['sizes']['full']['url'] = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['url'] : $element['attrs']['iconImage']['link'];
				}
			}

			if ( ! empty( $element['innerBlocks'] ) ) {
				$this->gutenberg_find_and_replace_template_data( $element['innerBlocks'], $posted_data );
			}
		}

	}

	/**
	 * Replace the logo and color in the BB template while importing.
	 *
	 * @param array $elements elements data.
	 * @param array $posted_data posted data.
	 */
	public function beaver_builder_find_and_replace_template_data( &$elements, $posted_data ) {

		foreach ( $elements as $node => &$element ) {
			if ( ! empty( $element->type ) && 'module' === $element->type ) {

				if ( ! empty( $element->settings->type ) && 'cartflows-bb-checkout-form' === $element->settings->type ) {
					// Update the logo in the template.
					$element->settings->global_primary_color = ! empty( $posted_data['primary_color'] ) ? $posted_data['primary_color'] : $element->settings->global_primary_color;
				}

				if ( ! empty( $element->settings->type ) && 'photo' === $element->settings->type && ! empty( $element->settings->class ) && 'cartflows-store-checkout-logo-field' === $element->settings->class ) {
					$module_setting = $element->settings;
					// Update the logo in the template.
					$element->settings->photo_src = ! empty( $posted_data['site_logo'] ) ? $posted_data['site_logo']['url'] : $element->settings->photo_src;

				}
			}
		}
	}
}
