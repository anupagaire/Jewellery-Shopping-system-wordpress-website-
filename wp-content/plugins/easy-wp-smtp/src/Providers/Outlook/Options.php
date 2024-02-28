<?php

namespace EasyWPSMTP\Providers\Outlook;

use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 2.1.0
 */
class Options extends OptionsAbstract {

	/**
	 * Outlook Options constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url' => easy_wp_smtp()->assets_url . '/images/providers/microsoft.svg',
				'slug'     => 'outlook',
				'title'    => esc_html__( '365 / Outlook', 'easy-wp-smtp' ),
				'disabled' => true,
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {

		?>
		<div class="easy-wp-smtp-setting-row easy-wp-smtp-setting-row-content easy-wp-smtp-clear section-heading">
			<p>
				<?php esc_html_e( 'Sorry, but the Microsoft Outlook mailer isn’t available in the lite version. Please upgrade to PRO to unlock this mailer and much more.', 'easy-wp-smtp' ); ?>
			</p>
		</div>
		<?php
	}
}
