<?php
/**
 * Plugin Update Notices
 *
 *
 * @package CartFlows Notices
 * @since 1.11.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * CartFlows_Plugin_Update_Notifications
 *
 * @since 1.11.8
 */
class Cartflows_Plugin_Update_Notifications {

		/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.11.8
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.11.8
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
	 * @since 1.11.8
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'in_plugin_update_message-' . CARTFLOWS_BASE, array( $this, 'dynamic_plugin_update_notification' ), 10, 2 );
	}

	/**
	 * Show customized plugin update notification message.
	 *
	 * @since 1.11.8
	 *
	 * @param array $data plugin data.
	 * @param array $response response.
	 *
	 * @return void
	 */
	public function dynamic_plugin_update_notification( $data, $response ) {

		if( isset( $data['upgrade_notice'] ) && ! empty( $data['upgrade_notice'] ) ) { ?>
			<hr class="wcf-plugin-update-notification__separator" />
			<div class="wcf-plugin-update-notification">
				<div class="wcf-plugin-update-notification__icon">
					<span class="dashicons dashicons-info"></span>
				</div>
				<div>
					<div class="wcf-plugin-update-notification__title">
						<?php echo esc_html__( 'Heads up!', 'cartflows' ); ?>
					</div>
					<div class="wcf-plugin-update-notification__message">
						<?php
							printf(
								// translators: %s upgrade notice message.
								__( '%s', 'cartflows' ),
								esc_html( $data['upgrade_notice'] )
							);
						?>
					</div>
				</div>
			</div> <?php
		}
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 1.11.8
	 * @return void
	 */
	public function enqueue_styles(){
		wp_enqueue_style( 'wcf-notifications', CARTFLOWS_URL . 'libraries/cartflows-plugin-update-notifications/update-notifications.css', array(), CARTFLOWS_VER );
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Plugin_Update_Notifications::get_instance();


