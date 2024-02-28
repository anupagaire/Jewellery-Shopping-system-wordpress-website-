<?php
/**
 * CartFlows Common Settings Data Query.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Api\ApiBase;
use CartflowsAdmin\AdminCore\Inc\AdminHelper;
use CartflowsAdmin\AdminCore\Inc\GlobalSettings;

/**
 * Class Admin_Query.
 */
class CommonSettings extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/commonsettings/';

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
		 * Constructor
		 */
	public function __construct() {

		add_filter( 'cartflows_admin_global_data_options', array( $this, 'add_other_option_data' ), 10, 1 );
	}

	/**
	 * Add flow meta options.
	 *
	 * @param array $options options.
	 * @return array.
	 */
	public function add_other_option_data( $options ) {

		$options['cartflows_delete_plugin_data']     = get_option( 'cartflows_delete_plugin_data' );
		$options['cartflows_stats_report_emails']    = get_option( 'cartflows_stats_report_emails', 'enable' );
		$options['cartflows_stats_report_email_ids'] = get_option( 'cartflows_stats_report_email_ids', get_option( 'admin_email' ) );

		return $options;
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {

		$namespace = $this->get_api_namespace();

		register_rest_route(
			$namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_common_settings' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get common settings.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 */
	public function get_common_settings( $request ) {

		$settings = GlobalSettings::get_global_settings_fields();
		$options  = AdminHelper::get_options();

		$global_settings = array(
			'settings' => $settings,
			'options'  => $options,
		);

		return $global_settings;
	}

	/**
	 * Check whether a given request has permission to read notes.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return new \WP_Error( 'cartflows_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cartflows' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
