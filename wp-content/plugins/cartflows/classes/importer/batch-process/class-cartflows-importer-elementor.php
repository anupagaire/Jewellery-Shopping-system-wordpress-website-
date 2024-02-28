<?php
/**
 * Elementor Importer
 *
 * @package CARTFLOWS
 */

namespace Elementor\TemplateLibrary;

use Elementor\Core\Base\Document;
use Elementor\DB;
use Elementor\Core\Settings\Page\Manager as PageSettingsManager;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Core\Settings\Page\Model;
use Elementor\Editor;
use Elementor\Plugin;
use Elementor\Settings;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */
class CartFlows_Importer_Elementor extends Source_Local {

	/**
	 *  Import single template
	 *
	 * @param int $post_id post ID.
	 */
	public function import_single_template( $post_id ) {

		do_action( 'cartflows_before_elementor_import_single_template' );

		$rest_content = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $rest_content ) ) {
			wcf()->logger->import_log( __( '(✕) Empty content.', 'cartflows' ) );
			return;
		}

		if ( empty( $rest_content ) ) {
			wcf()->logger->import_log( __( '(✕) Invalid content.', 'cartflows' ) );
		}

		if ( is_array( $rest_content ) ) {
			$content = $rest_content;
		} else {
			$rest_content = add_magic_quotes( $rest_content );
			$content      = json_decode( $rest_content, true );
		}

		if ( ! is_array( $content ) ) {
			$data = __( 'Invalid content. Expected an array.', 'cartflows' );
			wcf()->logger->import_log( '(✕) ' . $data );
			wcf()->logger->import_log( $content );
		} else {

			wcf()->logger->import_log( '(✓) Processing Request..' );

			// Import the data.
			$content = $this->process_export_import_content( $content, 'on_import' );

			// Serialize the array if found.
			if ( is_array( $content ) ) {
				$content = wp_slash( wp_json_encode( $content ) );
			}

			// Update content.
			update_metadata( 'post', $post_id, '_elementor_data', $content );

			$this->clear_cache();

			wcf()->logger->import_log( '(✓) Process Complete' );
		}
	}

	/**
	 * Clear Cache.
	 *
	 * @since 1.0.0
	 */
	public function clear_cache() {
		// Clear 'Elementor' file cache.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}
}
