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
 * Class Steps.
 */
class AbSteps extends AjaxBase {

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
			'hide_archive_ab_test_variation',
			'permanent_delete_archive_ab_test_variation',
			'save_ab_test_setting',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Register ajax events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function hide_archive_ab_test_variation() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_hide_archive_ab_test_variation', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t create a variation for this step - %s, Invalid Step ID.', 'cartflows' ), $step_id ),
		);

		if ( ! $step_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		update_post_meta( $step_id, 'wcf-hide-step', true );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step successfully hidden - %s', 'cartflows' ), $step_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Register ajax events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function permanent_delete_archive_ab_test_variation() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_permanent_delete_archive_ab_test_variation', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$step_id    = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$flow_id    = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$control_id = isset( $_POST['control_id'] ) ? intval( $_POST['control_id'] ) : 0;

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t delete a variation for this step - %s, Invalid Step Id or Funnel Id.', 'cartflows' ), $step_id ),
		);

		if ( ! $step_id || ! $flow_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
			wp_send_json( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $flow_steps ) ) {
			wp_send_json( $result );
		}

		if ( is_array( $flow_steps ) ) {

			foreach ( $flow_steps as $step => $control_step ) {

				if ( $control_step['id'] === $control_id ) {

					$archived_variation = $control_step['ab-test-archived-variations'];

					foreach ( $archived_variation as $variations => $variation ) {

						if ( $variation['id'] === $step_id ) {
							unset( $archived_variation[ $variations ] );

							$flow_steps[ $step ]['ab-test-archived-variations'] = array_values( $archived_variation );
							break;
						}
					}
					break;
				}
			}
		}

		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step deleted - %s', 'cartflows' ), $step_id ),
		);

		wp_send_json( $result );
	}


	/**
	 * Save Ab test settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_ab_test_setting() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_ab_test_setting', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$flow_id = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t create a variation for this step - %s', 'cartflows' ), $step_id ),
		);

		if ( ! $step_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
			wp_send_json( $result );
		}

		if ( isset( $_POST['wcf_ab_settings'] ) ) {
			// We are sanitizing form data using our function. Hence ignoring sanitization rule.
			$form_data = wcf_clean( wp_unslash( $_POST['wcf_ab_settings'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$traffic = isset( $form_data['traffic'] ) ? $form_data['traffic'] : array();

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( is_array( $flow_steps ) ) {

				foreach ( $flow_steps as $index => $step_data ) {

					if ( $step_data['id'] === $step_id ) {

						if ( isset( $step_data['ab-test-variations'] ) ) {

							$all_variations = $step_data['ab-test-variations'];

							foreach ( $all_variations as $var_key => $var_data ) {

								$var_id = intval( $var_data['id'] );

								if ( isset( $traffic[ $var_id ] ) && isset( $traffic[ $var_id ]['value'] ) ) {
									$all_variations[ $var_key ]['traffic'] = intval( $traffic[ $var_id ]['value'] );
								}
							}

							$flow_steps[ $index ]['ab-test-variations'] = $all_variations;
						}
					}
				}
			}
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			do_action(
				'cartflows_save_ab_test_setting',
				array(
					'flow_id' => $flow_id,
					'step_id' => $step_id,
				)
			);

			$result = array(
				'status' => true,
				/* translators: %s step id */
				'text'   => sprintf( __( 'A/B test settings updated for this step - %s', 'cartflows' ), $step_id ),
			);

		}
		wp_send_json( $result );
	}
}
