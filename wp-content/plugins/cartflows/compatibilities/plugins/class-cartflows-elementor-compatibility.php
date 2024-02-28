<?php
/**
 * Elementor page builder compatibility
 *
 * @package CartFlows
 */

namespace Elementor\Modules\PageTemplates;

use Elementor\Core\Base\Document;
use Elementor\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for elementor page builder compatibility
 */
class Cartflows_Elementor_Compatibility {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_page_template', array( $this, 'get_page_template' ) );

		if ( wcf()->is_woo_active ) {

			// On Editor - Register WooCommerce frontend hooks before the Editor init.
			// Priority = 5, in order to allow plugins remove/add their wc hooks on init.
			if ( ! empty( $_REQUEST['action'] ) && 'elementor' === $_REQUEST['action'] && is_admin() ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_action( 'init', array( $this, 'register_wc_hooks' ), 5 );
			}

			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'maybe_init_cart' ) );
		}
	}

	/**
	 * Get page template filter callback for elementor preview mode
	 *
	 * @param string $template page template.
	 * @return string
	 */
	public function get_page_template( $template ) {

		if ( is_singular() ) {
			$document = Plugin::$instance->documents->get_doc_for_frontend( get_the_ID() );

			if ( $document ) {
				$template = $document->get_meta( '_wp_page_template' );
			}
		}

		return $template;
	}

	/**
	 * Rgister wc hookes for elementor preview mode
	 */
	public function register_wc_hooks() {
		wc()->frontend_includes();
	}

	/**
	 * Init cart in elementor preview mode
	 */
	public function maybe_init_cart() {

		$has_cart = is_a( WC()->cart, 'WC_Cart' );

		if ( ! $has_cart ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
			WC()->session  = new $session_class();
			WC()->session->init();
			WC()->cart     = new \WC_Cart();
			WC()->customer = new \WC_Customer( get_current_user_id(), true );
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Elementor_Compatibility::get_instance();
