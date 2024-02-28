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
use CartflowsAdmin\AdminCore\Inc\MetaOps;

/**
 * Class Steps.
 */
class Steps extends AjaxBase {

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
			'clone_step',
			'delete_step',
			'save_meta_settings',
			'update_step_title',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Update step title.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_step_title() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_update_step_title', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$step_id        = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$new_step_title = isset( $_POST['new_step_title'] ) ? sanitize_text_field( $_POST['new_step_title'] ) : '';

		$result = array(
			'status' => false,
			'text'   => __( 'Can\'t update the step title', 'cartflows' ),
		);

		/* Check if CartFlows Post type */
		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json( $result );
		}

		if ( empty( $step_id ) || empty( $new_step_title ) ) {
			wp_send_json( $result );
		}

		$new_step_data = array(
			'ID'         => $step_id,
			'post_title' => $new_step_title,
		);
		wp_update_post( $new_step_data );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step title updated - %s', 'cartflows' ), $step_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Clone step with its meta.
	 */
	public function clone_step() {

		global $wpdb;

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_clone_step', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['post_id'] );
			$step_id = intval( $_POST['step_id'] );
		}

		$result = array(
			'status' => false,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t clone this step - %1$s. Flow - %2$s', 'cartflows' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		/* Check if CartFlows Post type */
		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json( $result );
		}

		/**
		 * And all the original post data then
		 */
		$post = get_post( $step_id );

		/**
		 * Assign current user to be the new post author
		 */
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		/**
		 * If post data exists, create the post duplicate
		 */
		if ( isset( $post ) && null !== $post ) {

			/**
			 * New post data array
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post->post_status,
				'post_title'     => $post->post_title . ' Clone',
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/**
			 * Insert the post
			 */
			$new_step_id = wp_insert_post( $args );

			/**
			 * Get all current post terms ad set them to the new post
			 */
			// returns array of taxonomy names for post type, ex array("category", "post_tag");.
			$taxonomies = get_object_taxonomies( $post->post_type );

			foreach ( $taxonomies as $taxonomy ) {

				$post_terms = wp_get_object_terms( $step_id, $taxonomy, array( 'fields' => 'slugs' ) );

				wp_set_object_terms( $new_step_id, $post_terms, $taxonomy, false );
			}

			/**
			 * Duplicate all post meta just in two SQL queries
			 */
			$post_meta_infos = $wpdb->get_results( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d", $step_id )
			);

			if ( ! empty( $post_meta_infos ) ) {

				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";

				$sql_query_sel = array();

				foreach ( $post_meta_infos as $meta_info ) {

					$meta_key = $meta_info->meta_key;

					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}

					$sql_query_sel[] = $wpdb->prepare( '( %d, %s, %s )', $new_step_id, $meta_key, $meta_info->meta_value );

				}

				$sql_query .= implode( ',', $sql_query_sel );

				$wpdb->query( $sql_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
			}

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );
			$step_type  = get_post_meta( $step_id, 'wcf-step-type', true );

			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => $new_step_id,
				'title' => $post->post_title,
				'type'  => $step_type,
			);

			$flow_steps = \Cartflows_Helper::get_instance()->maybe_update_flow_steps( $flow_id, $flow_steps );

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			/* Clear Page Builder Cache */
			$this->clear_cache();

			$result = array(
				'status' => true,
				'reload' => true,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'Step - %1$s cloned. Flow - %2$s', 'cartflows' ), $step_id, $flow_id ),
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Clear Page Builder Cache
	 */
	public function clear_cache() {

		// Clear 'Elementor' file cache.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/**
	 * Delete step for flow
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function delete_step() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_delete_step', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$flow_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		$result = array(
			'status' => false,
			'reload' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step not deleted for flow - %s', 'cartflows' ), $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		/* Check if CartFlows Post type */
		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $flow_steps ) ) {
			wp_send_json( $result );
		}

		$is_ab_test = get_post_meta( $step_id, 'wcf-ab-test', true );

		if ( ! $is_ab_test ) {

			foreach ( $flow_steps as $index => $data ) {

				if ( intval( $data['id'] ) === $step_id ) {
					unset( $flow_steps[ $index ] );
					break;
				}
			}

			/* Set index order properly */
			$flow_steps = array_merge( $flow_steps );

			/* Update latest data */
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			/* Delete step */
			wp_delete_post( $step_id, true );

			$result = array(
				'status' => true,
				'reload' => false,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'Step deleted for flow - %s', 'cartflows' ), $flow_id ),
			);

		} else {

			$result = array(
				'status' => false,
				'reload' => false,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'This step can not be deleted.', 'cartflows' ), $flow_id ),
			);
			/**
				Action do_action( 'cartflows_step_delete_ab_test', $step_id, $flow_id, $flow_steps );
			*/
		}

		wp_send_json( $result );
	}

	/**
	 * Save meta settings for steps.
	 */
	public function save_meta_settings() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_meta_settings', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$result = array(
			'status' => false,
			'reload' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Invalid Step Id has been provided.', 'cartflows' ) ),
		);

		if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['step_id'] ) ) {
			wp_send_json( $result );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$step_tile = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : get_the_title( $step_id );
		if ( '' === $step_tile ) {
			$step_tile = __( '(no title)', 'cartflows' );
		}
		$step_slug = isset( $_POST['post_name'] ) ? sanitize_text_field( wp_unslash( $_POST['post_name'] ) ) : get_post_field( 'post_name', $step_id );

		$step_data = array(
			'ID'         => $step_id,
			'post_title' => $step_tile,
			'post_name'  => $step_slug,
		);

		wp_update_post( $step_data );

		$post_meta = '';

		$step_type = wcf()->utils->get_step_type( $step_id );

		$post_meta = AdminHelper::get_step_default_meta( $step_type, $step_id );

		MetaOps::save_meta_fields( $step_id, $post_meta, 'cartflows_save_meta_settings' );

		// We are storing the step dynamic css in the post meta and deleting when any setting changes. Once deleted it will be regenerated with first page load.
		delete_post_meta( $step_id, 'wcf-dynamic-css' );

		do_action( 'cartflows_admin_save_step_meta', $step_id );

		$meta_options = AdminHelper::get_step_meta_options( $step_id );

		// Delete the Google font URL and generate it while loading the page first time.
		delete_post_meta( $step_id, 'wcf-field-google-font-url' );

		$result = array(
			'status'        => true,
			'reload'        => false,
			'options'       => $meta_options,
			'step_view_url' => get_the_permalink( $step_id ),
			/* translators: %s flow id */
			'text'          => sprintf( __( 'Data saved successfully for step id %s', 'cartflows' ), $step_id ),
		);

		wp_send_json( $result );

	}


}
