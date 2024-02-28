<?php

namespace EasyWPSMTP\Providers\Gmail;

use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 2.2.0
 */
class Options extends OptionsAbstract {

	/**
	 * Outlook Options constructor.
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'logo_url' => easy_wp_smtp()->assets_url . '/images/providers/google.svg',
				'slug'     => 'gmail',
				'title'    => esc_html__( 'Google / Gmail', 'easy-wp-smtp' ),
				'disabled' => true,
			]
		);
	}

	/**
	 * Output the mailer provider options.
	 *
	 * @since 2.2.0
	 */
	public function display_options() {

		?>
		<div class="easy-wp-smtp-setting-row easy-wp-smtp-setting-row-content easy-wp-smtp-clear section-heading">
			<p>
				<?php esc_html_e( 'Sorry, but the Gmail mailer isn’t available in the lite version. Please upgrade to PRO to unlock this mailer and much more.', 'easy-wp-smtp' ); ?>
			</p>
		</div>
		<?php
	}
}
