<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\WP;

/**
 * Class ParentPageAbstract.
 *
 * @since 2.0.0
 */
abstract class ParentPageAbstract implements PageInterface {

	/**
	 * Slug of a page.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Page tabs.
	 *
	 * @since 2.0.0
	 *
	 * @var PageAbstract[]
	 */
	protected $tabs = [];

	/**
	 * Page default tab slug.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $default_tab = '';

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param array $tabs Page tabs.
	 */
	public function __construct( $tabs = [] ) {

		/**
		 * Filters parent page tabs.
		 *
		 * @since 2.0.0
		 *
		 * @param string[] $tabs Parent page tabs.
		 */
		$tabs = apply_filters( 'easy_wp_smtp_admin_page_' . $this->slug . '_tabs', $tabs );

		if ( easy_wp_smtp()->get_admin()->is_admin_page( $this->slug ) ) {
			$this->init_tabs( $tabs );
			$this->hooks();
		}

		if ( WP::is_doing_self_ajax() ) {
			$this->init_ajax( $tabs );
		}
	}

	/**
	 * Hooks.
	 *
	 * @since 2.0.0
	 */
	protected function hooks() {

		add_action( 'admin_init', [ $this, 'process_actions' ] );

		// Register tab related hooks.
		if ( isset( $this->tabs[ $this->get_current_tab() ] ) ) {
			$this->tabs[ $this->get_current_tab() ]->hooks();
		}
	}

	/**
	 * Initialize ajax actions.
	 *
	 * @since 2.0.0
	 *
	 * @param array $tabs Page tabs.
	 */
	private function init_ajax( $tabs ) {

		foreach ( $tabs as $tab ) {
			if ( $this->is_valid_tab( $tab ) ) {
				( new $tab( $this ) )->ajax();
			}
		}
	}

	/**
	 * Get the page slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_slug() {

		return $this->slug;
	}

	/**
	 * Get the page tabs.
	 *
	 * @since 2.0.0
	 *
	 * @return PageAbstract[]
	 */
	public function get_tabs() {

		return $this->tabs;
	}

	/**
	 * Get the page tabs slugs.
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public function get_tabs_slugs() {

		return array_map(
			function ( $tab ) {
				return $tab->get_slug();
			},
			$this->tabs
		);
	}

	/**
	 * Get the page/tab link.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tab Tab to generate a link to.
	 *
	 * @return string
	 */
	public function get_link( $tab = '' ) {

		return add_query_arg(
			'tab',
			$this->get_defined_tab( $tab ),
			WP::admin_url( 'admin.php?page=' . Area::SLUG . '-' . $this->slug )
		);
	}

	/**
	 * Get the current tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_current_tab() {

		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return $this->get_defined_tab( $tab );
	}

	/**
	 * Get the tab label.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tab Tab key.
	 *
	 * @return string
	 */
	public function get_tab_label( $tab ) {

		$tabs = $this->get_tabs();

		return isset( $tabs[ $tab ] ) ? $tabs[ $tab ]->get_label() : '';
	}

	/**
	 * Get the tab title.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tab Tab key.
	 *
	 * @return string
	 */
	public function get_tab_title( $tab ) {

		$tabs = $this->get_tabs();

		return isset( $tabs[ $tab ] ) ? $tabs[ $tab ]->get_title() : '';
	}

	/**
	 * Get the defined or default tab.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tab Tab to check.
	 *
	 * @return string Defined tab. Fallback to default one if it doesn't exist.
	 */
	protected function get_defined_tab( $tab ) {

		$tab = sanitize_key( $tab );

		return in_array( $tab, $this->get_tabs_slugs(), true ) ? $tab : $this->default_tab;
	}

	/**
	 * Initialize tabs.
	 *
	 * @since 2.0.0
	 *
	 * @param array $tabs Page tabs.
	 */
	public function init_tabs( $tabs ) {

		foreach ( $tabs as $key => $tab ) {
			if ( ! $this->is_valid_tab( $tab ) ) {
				continue;
			}

			$this->tabs[ $key ] = new $tab( $this );
		}

		// Sort tabs by priority.
		$this->sort_tabs();
	}

	/**
	 * All possible plugin forms manipulation and hooks registration will be done here.
	 *
	 * @since 2.0.0
	 */
	public function process_actions() {

		$tabs = $this->get_tabs_slugs();

		// Allow to process only own tabs.
		if ( ! array_key_exists( $this->get_current_tab(), $tabs ) ) {
			return;
		}

		// Process POST only if it exists.
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( ! empty( $_POST ) && isset( $_POST['easy-wp-smtp-post'] ) ) {
			if ( ! empty( $_POST['easy-wp-smtp'] ) ) {
				$post = $_POST['easy-wp-smtp'];
			} else {
				$post = [];
			}

			$this->tabs[ $this->get_current_tab() ]->process_post( $post );
		}
		// phpcs:enable

		// This won't do anything for most pages.
		// Works for plugin page only, when GET params are allowed.
		$this->tabs[ $this->get_current_tab() ]->process_auth();
	}

	/**
	 * Display page content based on the current tab.
	 *
	 * @since 2.0.0
	 */
	public function display() {

		$current_tab = $this->get_current_tab();
		$page_slug   = $this->slug;
		?>
		<?php if ( count( $this->tabs ) <= 1 ) : ?>
			<div class="easy-wp-smtp-subheader">
				<div class="easy-wp-smtp-subheader__inner easy-wp-smtp-container">
					<div class="easy-wp-smtp-page-title">
						<?php echo esc_html( $this->get_tab_title( $current_tab ) ); ?>
					</div>
					<?php $this->display_after_page_title(); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="easy-wp-smtp-container">
			<?php if ( count( $this->tabs ) > 1 ) : ?>
				<div class="easy-wp-smtp-nav-menu">
					<div class="easy-wp-smtp-nav-menu__inner">
						<?php foreach ( $this->tabs as $tab ) : ?>
							<a href="<?php echo esc_url( $tab->get_link() ); ?>"
								 class="easy-wp-smtp-nav-menu__item <?php echo $current_tab === $tab->get_slug() ? 'easy-wp-smtp-nav-menu__item--active' : ''; ?>">
								<?php echo esc_html( $tab->get_label() ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="easy-wp-smtp-page-content">
				<?php
				foreach ( $this->tabs as $tab ) {
					if ( $tab->get_slug() === $current_tab ) {

						printf( '<h1 class="screen-reader-text">%s</h1>', esc_html( $tab->get_title() ) );

						/**
						 * Fires before tab content.
						 *
						 * @since 2.0.0
						 *
						 * @param PageAbstract $tab Current tab.
						 */
						do_action( 'easy_wp_smtp_admin_pages_before_content', $tab );

						/**
						 * Fires before tab content.
						 *
						 * @since 2.0.0
						 *
						 * @param PageAbstract $tab Current tab.
						 */
						do_action( "easy_wp_smtp_admin_page_{$page_slug}_{$current_tab}_display_before", $tab );

						$tab->display();

						/**
						 * Fires after tab content.
						 *
						 * @since 2.0.0
						 *
						 * @param PageAbstract $tab Current tab.
						 */
						do_action( "easy_wp_smtp_admin_page_{$page_slug}_{$current_tab}_display_after", $tab );

						break;
					}
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display some elements after page title.
	 *
	 * @since 2.1.0
	 */
	private function display_after_page_title() {

		if ( easy_wp_smtp()->is_pro() || ! in_array( $this->get_current_tab(), [ 'reports' ], true ) ) {
			return;
		}

		$button_upgrade_link = easy_wp_smtp()->get_upgrade_link(
			[
				'medium'  => 'email-reports',
				'content' => 'upgrade-to-easy-wp-smtp-pro-button-link',
			]
		);

		?>
		<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="easy-wp-smtp-btn easy-wp-smtp-btn--sm easy-wp-smtp-btn--green">
			<?php esc_html_e( 'Upgrade to Pro', 'easy-wp-smtp' ); ?>
		</a>
		<?php
	}

	/**
	 * Sort tabs by priority.
	 *
	 * @since 2.0.0
	 */
	protected function sort_tabs() {

		uasort(
			$this->tabs,
			function ( $a, $b ) {

				return ( $a->get_priority() < $b->get_priority() ) ? - 1 : 1;
			}
		);
	}

	/**
	 * Whether tab is valid.
	 *
	 * @since 2.0.0
	 *
	 * @param array $tab Page tab.
	 *
	 * @return bool
	 */
	private function is_valid_tab( $tab ) {

		return is_subclass_of( $tab, '\EasyWPSMTP\Admin\PageAbstract' );
	}
}
