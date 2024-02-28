<?php
/**
 * Image Importer
 *
 * => How to use?
 *
 *  $image = array(
 *      'url' => '<image-url>',
 *      'id'  => '<image-id>',
 *  );
 *
 *  $downloaded_image = CartFlows_Import_Image::get_instance()->import( $image );
 *
 * @package CartFlows
 * @since 1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CartFlows_Import_Image' ) ) :

	/**
	 * CartFlows Importer
	 *
	 * @since 1.1.1
	 */
	class CartFlows_Import_Image {

		/**
		 * Instance
		 *
		 * @since 1.1.1
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Images IDs
		 *
		 * @var array   The Array of already image IDs.
		 * @since 1.1.1
		 */
		private $already_imported_ids = array();

		/**
		 * Initiator
		 *
		 * @since 1.1.1
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.1.1
		 */
		public function __construct() {

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();
		}

		/**
		 * Process Image Download
		 *
		 * @since 1.1.1
		 * @param  array $attachments Attachment array.
		 * @return array              Attachment array.
		 */
		public function process( $attachments ) {

			$downloaded_images = array();

			foreach ( $attachments as $key => $attachment ) {
				$downloaded_images[] = $this->import( $attachment );
			}

			return $downloaded_images;
		}

		/**
		 * Get Hash Image.
		 *
		 * @since 1.1.1
		 * @param  string $attachment_url Attachment URL.
		 * @return string                 Hash string.
		 */
		private function get_hash_image( $attachment_url ) {
			return sha1( $attachment_url );
		}

		/**
		 * Get Saved Image.
		 *
		 * @since 1.1.1
		 * @param  string $attachment   Attachment Data.
		 * @return string                 Hash string.
		 */
		private function get_saved_image( $attachment ) {

			if ( apply_filters( 'cartflows_image_importer_skip_image', false, $attachment ) ) {
				wcf()->logger->import_log( 'IMAGE - SKIP Image - {from filter} - ' . $attachment['url'] . ' - Filter name `cartflows_image_importer_skip_image`.' );
				return array(
					'status'     => true,
					'attachment' => $attachment,
				);
			}

			global $wpdb;

			// 1. Is already imported in Batch Import Process?
			$post_id = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
						WHERE `meta_key` = \'_cartflows_image_hash\'
							AND `meta_value` = %s
					;',
					$this->get_hash_image( $attachment['url'] )
				)
			);

			// 2. Is image already imported though XML?
			if ( empty( $post_id ) ) {

				// Get file name without extension.
				// To check it exist in attachment.
				$filename = basename( $attachment['url'] );

				$post_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_id FROM {$wpdb->postmeta}
						WHERE meta_key = '_wp_attached_file'
						AND meta_value LIKE %s",
						'%/' . $filename . '%'
					)
				);

				wcf()->logger->import_log( 'IMAGE - SKIP Image {already imported from xml} - ' . $attachment['url'] );
			}

			if ( $post_id ) {
				$new_attachment               = array(
					'id'  => $post_id,
					'url' => wp_get_attachment_url( $post_id ),
				);
				$this->already_imported_ids[] = $post_id;

				return array(
					'status'     => true,
					'attachment' => $new_attachment,
				);
			}

			return array(
				'status'     => false,
				'attachment' => $attachment,
			);
		}

		/**
		 * Import Image
		 *
		 * @since 1.1.1
		 * @param  array $attachment Attachment array.
		 * @return array              Attachment array.
		 */
		public function import( $attachment ) {

			wcf()->logger->import_log( 'Source - ' . $attachment['url'] );
			$saved_image = $this->get_saved_image( $attachment );
			wcf()->logger->import_log( 'Log - ' . wp_json_encode( $saved_image['attachment'] ) );

			if ( $saved_image['status'] ) {
				return $saved_image['attachment'];
			}

			$file_content = wp_remote_retrieve_body(
				wp_safe_remote_get(
					$attachment['url'],
					array(
						'timeout'   => '60', //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
						'sslverify' => false,
					)
				)
			);

			// Empty file content?
			if ( empty( $file_content ) ) {

				wcf()->logger->import_log( 'IMAGE - FAIL Image {Error: Failed wp_remote_retrieve_body} - ' . $attachment['url'] );
				return $attachment;
			}

			// Extract the file name and extension from the URL.
			$filename = basename( $attachment['url'] );

			$upload = wp_upload_bits(
				$filename,
				null,
				$file_content
			);

			wcf()->logger->import_log( $filename );
			wcf()->logger->import_log( wp_json_encode( $upload ) );

			$post = array(
				'post_title' => $filename,
				'guid'       => $upload['url'],
			);
			wcf()->logger->import_log( wp_json_encode( $post ) );

			$info = wp_check_filetype( $upload['file'] );
			if ( $info ) {
				$post['post_mime_type'] = $info['type'];
			} else {
				// For now just return the origin attachment.
				return $attachment;
			}

			$post_id = wp_insert_attachment( $post, $upload['file'] );
			wp_update_attachment_metadata(
				$post_id,
				wp_generate_attachment_metadata( $post_id, $upload['file'] )
			);
			update_post_meta( $post_id, '_cartflows_image_hash', $this->get_hash_image( $attachment['url'] ) );

			$new_attachment = array(
				'id'  => $post_id,
				'url' => $upload['url'],
			);

			wcf()->logger->import_log( 'IMAGE - SUCCESS Image {Imported} - ' . $new_attachment['url'] );

			$this->already_imported_ids[] = $post_id;

			return $new_attachment;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	CartFlows_Import_Image::get_instance();

endif;
