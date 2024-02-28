<?php
/**
 * CartFlows Admin Notices.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Cartflows_Admin_Notices.
 */
class Cartflows_Admin_Notices {

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

		add_action( 'admin_head', array( $this, 'show_admin_notices' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'notices_scripts' ) );

		add_action( 'wp_ajax_cartflows_ignore_gutenberg_notice', array( $this, 'ignore_gb_notice' ) );

		add_action( 'wp_ajax_cartflows_disable_weekly_report_email_notice', array( $this, 'disable_weekly_report_email_notice' ) );

		add_filter( 'woo_ca_plugin_review_url', array( $this, 'update_review_link' ), 10, 1 );
	}

	/**
	 * Update review link for cart abandonment.
	 *
	 * @param string $review_link review link.
	 *
	 * @return string URL.
	 */
	public function update_review_link( $review_link ) {

		return 'https://wordpress.org/support/plugin/cartflows/reviews/?filter=5#new-post';
	}


	/**
	 * Show the weekly email Notice
	 *
	 * @return void
	 */
	public function show_weekly_report_email_settings_notice() {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		$is_show_notice = get_option( 'cartflows_show_weekly_report_email_notice', 'no' );

		if ( 'yes' === $is_show_notice && current_user_can( 'manage_options' ) ) {

			$setting_url = admin_url( 'admin.php?page=cartflows&path=settings#other_settings' );

			/* translators: %1$s Software Title, %2$s Plugin, %3$s Anchor opening tag, %4$s Anchor closing tag, %5$s Software Title. */
			$message = sprintf( __( '%1$sCartFlows:%2$s We just introduced an awesome new feature, weekly store revenue reports via email. Now you can see how many revenue we are generating for your store each week, without having to log into your website. You can set the email address for these email from %3$shere.%4$s', 'cartflows' ), '<strong>', '</strong>', '<a class="wcf-redirect-to-settings" target="_blank" href=" ' . esc_url( $setting_url ) . ' ">', '</a>' );
			$output  = '<div class="wcf-notice weekly-report-email-notice wcf-dismissible-notice notice notice-info is-dismissible">';
			$output .= '<p>' . $message . '</p>';
			$output .= '</div>';

			echo wp_kses_post( $output );
		}

	}

	/**
	 * Disable the weekly email Notice
	 *
	 * @return void
	 */
	public function disable_weekly_report_email_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows-disable-weekly-report-email-notice', 'security' );
		delete_option( 'cartflows_show_weekly_report_email_notice' );
		wp_send_json_success();
	}

	/**
	 *  After save of permalinks.
	 */
	public function notices_scripts() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		wp_enqueue_style( 'cartflows-custom-notices', CARTFLOWS_URL . 'admin/assets/css/notices.css', array(), CARTFLOWS_VER );

		wp_enqueue_script( 'cartflows-notices', CARTFLOWS_URL . 'admin/assets/js/ui-notice.js', array( 'jquery' ), CARTFLOWS_VER, true );

		$localize_vars = array(
			'ignore_gb_notice'                   => wp_create_nonce( 'cartflows-ignore-gutenberg-notice' ),
			'dismiss_weekly_report_email_notice' => wp_create_nonce( 'cartflows-disable-weekly-report-email-notice' ),
		);

		wp_localize_script( 'cartflows-notices', 'cartflows_notices', $localize_vars );
	}

	/**
	 *  After save of permalinks.
	 */
	public function show_admin_notices() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		global  $wp_version;

		if ( version_compare( $wp_version, '5.0', '>=' ) && is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			add_action( 'admin_notices', array( $this, 'gutenberg_plugin_deactivate_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'show_weekly_report_email_settings_notice' ) );

		$image_path = esc_url( CARTFLOWS_URL . 'assets/images/cartflows-logo-small.jpg' );

		Astra_Notices::add_notice(
			array(
				'id'                   => 'cartflows-5-start-notice',
				'type'                 => 'info',
				'class'                => 'cartflows-5-star',
				'show_if'              => true,
				/* translators: %1$s white label plugin name and %2$s deactivation link */
				'message'              => sprintf(
					'<div class="notice-image" style="display: flex;">
                        <img src="%1$s" class="custom-logo" alt="CartFlows Icon" itemprop="logo" style="max-width: 90px; border-radius: 50px;"></div>
                        <div class="notice-content">
                            <div class="notice-heading">
                                %2$s
                            </div>
                            %3$s<br />
                            <div class="astra-review-notice-container">
                                <a href="%4$s" class="astra-notice-close astra-review-notice button-primary" target="_blank">
                                %5$s
                                </a>
                            <span class="dashicons dashicons-calendar"></span>
                                <a href="#" data-repeat-notice-after="%6$s" class="astra-notice-close astra-review-notice">
                                %7$s
                                </a>
                            <span class="dashicons dashicons-smiley"></span>
                                <a href="#" class="astra-notice-close astra-review-notice">
                                %8$s
                                </a>
                            </div>
                        </div>',
					$image_path,
					__( 'Hi there! You recently used CartFlows to build a sales funnel &mdash; Thanks a ton!', 'cartflows' ),
					__( 'It would be awesome if you give us a 5-star review and share your experience on WordPress. Your reviews pump us up and also help other WordPress users make a better decision when choosing CartFlows!', 'cartflows' ),
					'https://wordpress.org/support/plugin/cartflows/reviews/?filter=5#new-post',
					__( 'Ok, you deserve it', 'cartflows' ),
					MONTH_IN_SECONDS,
					__( 'Nope, maybe later', 'cartflows' ),
					__( 'I already did', 'cartflows' )
				),
				'repeat-notice-after'  => MONTH_IN_SECONDS,
				'display-notice-after' => ( 2 * WEEK_IN_SECONDS ), // Display notice after 2 weeks.
			)
		);
	}

	/**
	 * Show Deactivate gutenberg plugin notice.
	 *
	 * @since 1.1.19
	 *
	 * @return void
	 */
	public function gutenberg_plugin_deactivate_notice() {

		$ignore_notice = get_option( 'wcf_ignore_gutenberg_notice', false );

		if ( 'yes' !== $ignore_notice ) {
			printf(
				'<div class="notice notice-error wcf_notice_gutenberg_plugin is-dismissible"><p>%s</p>%s</div>',
				wp_kses_post(
					sprintf(
					/* translators: %1$s: HTML, %2$s: HTML */
						__( 'Heads up! The Gutenberg plugin is not recommended on production sites as it may contain non-final features that cause compatibility issues with CartFlows and other plugins. %1$s Please deactivate the Gutenberg plugin %2$s to ensure the proper functioning of your website.', 'cartflows' ),
						'<strong>',
						'</strong>'
					)
				),
				''
			);
		}
	}

	/**
	 * Ignore admin notice.
	 */
	public function ignore_gb_notice() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows-ignore-gutenberg-notice', 'security' );

		update_option( 'wcf_ignore_gutenberg_notice', 'yes' );
	}

	/**
	 * Check allowed screen for notices.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function allowed_screen_for_notices() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'toplevel_page_cartflows',
			'dashboard',
			'plugins',
		);

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}
}

Cartflows_Admin_Notices::get_instance();
