( function ( $ ) {
	const ignore_gb_notice = function () {
		$( '.wcf_notice_gutenberg_plugin button.notice-dismiss' ).on(
			'click',
			function ( e ) {
				e.preventDefault();

				const data = {
					action: 'cartflows_ignore_gutenberg_notice',
					security: cartflows_notices.ignore_gb_notice,
				};

				$.ajax( {
					type: 'POST',
					url: ajaxurl,
					data,

					success( response ) {
						if ( response.success ) {
							console.log( 'Gutenberg Notice Ignored.' );
						}
					},
				} );
			}
		);
	};

	const dismiss_weekly_report_email_notice = function () {
		$(
			'.weekly-report-email-notice.wcf-dismissible-notice button.notice-dismiss'
		).on( 'click', function ( e ) {
			e.preventDefault();

			const data = {
				action: 'cartflows_disable_weekly_report_email_notice',
				security: cartflows_notices.dismiss_weekly_report_email_notice,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						console.log( 'Weekly Report Email Notice Ignored.' );
					}
				},
			} );
		} );
	};

	$( function () {
		ignore_gb_notice();
		dismiss_weekly_report_email_notice();
	} );
} )( jQuery );
