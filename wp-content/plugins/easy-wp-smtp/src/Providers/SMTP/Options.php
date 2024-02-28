<?php

namespace EasyWPSMTP\Providers\SMTP;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class SMTP.
 *
 * @since 2.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * SMTP constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct(
			[
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/smtp.svg',
				'slug'        => 'smtp',
				'title'       => esc_html__( 'Other SMTP', 'easy-wp-smtp' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %s - URL to SMTP documentation. */
						__( '<p>By selecting the Other SMTP option, you can connect your site to an SMTP server you have access to instead of sending emails through a 3rd party provider. In some cases, this may be more convenient than setting up an account with one of the other mailer options provided. However, the Other SMTP option is less secure than the other mailers. Additionally, your provider may not allow you to send large volumes of emails. For these reasons, we recommend choosing one of our compatible mailers.</p><p>To get started, <a href="%s" target="_blank" rel="noopener noreferrer">see our documentation for the Other SMTP mailer</a>.</p>', 'easy-wp-smtp' ),
						[
							'p' => [],
							'a' => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-other-smtp-mailer/', 'Other SMTP documentation' ) )
				),
			],
			$connection
		);
	}
}
