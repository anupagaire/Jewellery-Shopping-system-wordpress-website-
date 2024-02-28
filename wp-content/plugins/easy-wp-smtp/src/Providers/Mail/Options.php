<?php

namespace EasyWPSMTP\Providers\Mail;

use EasyWPSMTP\Admin\SetupWizard;
use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Option.
 *
 * @since 2.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mail constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$description = sprintf(
			wp_kses( /* translators: %1$s - URL to all mailer doc page. %2$s - URL to the setup wizard. */
				__( 'You currently have the <strong>Default (none)</strong> mailer selected, which won\'t improve email deliverability. Please select <a href="%1$s" target="_blank" rel="noopener noreferrer">any other email provider</a> and use the easy <a href="%2$s">Setup Wizard</a> to configure it.', 'easy-wp-smtp' ),
				[
					'strong' => [],
					'a'      => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/a-complete-guide-to-easy-wp-smtp-mailers/', 'Default mailer - any other email provider' ) ),
			esc_url( SetupWizard::get_site_url() )
		);

		parent::__construct(
			array(
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/php.svg',
				'slug'        => 'mail',
				'title'       => esc_html__( 'Default (none)', 'easy-wp-smtp' ),
				'description' => $description,
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {}
}
