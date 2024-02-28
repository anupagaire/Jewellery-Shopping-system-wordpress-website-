( function ( $ ) {
	/* It will redirect if anyone clicked on link before ready */
	$( document ).on( 'click', 'a[href*="wcf-next-step"]', function ( e ) {
		e.preventDefault();

		if (
			'undefined' !== typeof cartflows.is_pb_preview &&
			'1' === cartflows.is_pb_preview
		) {
			e.stopPropagation();
			return;
		}

		window.location.href = cartflows.next_step;

		return false;
	} );

	/* Once the link is ready this will work to stop conditional propogation*/
	$( document ).on( 'click', '.wcf-next-step-link', function ( e ) {
		if (
			'undefined' !== typeof cartflows.is_pb_preview &&
			'1' === cartflows.is_pb_preview
		) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	} );

	// Remove css when oceanwp theme is enabled.
	const remove_oceanwp_custom_style = function () {
		if (
			'OceanWP' === cartflows.current_theme &&
			'default' !== cartflows.page_template
		) {
			const style = document.getElementById( 'oceanwp-style-css' );
			if ( null !== style ) {
				style.remove();
			}
		}
	};

	const trigger_facebook_events = function () {
		if (
			'enable' === cartflows.fb_setting.facebook_pixel_tracking &&
			cartflows.fb_setting.facebook_pixel_id !== ''
		) {
			const add_payment_info_event =
				cartflows.fb_setting.facebook_pixel_add_payment_info;
			if (
				'enable' === add_payment_info_event &&
				cartflows.is_checkout_page
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						fbq(
							'track',
							'AddPaymentInfo',
							JSON.parse( cartflows.fb_add_payment_info_data )
						);
					}
				);
			} else if (
				cartflows.is_optin &&
				'enable' === cartflows.fb_setting.facebook_pixel_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						fbq( 'track', 'Lead', { plugin: 'CartFlows' } );
					}
				);
			}
		}
	};

	const trigger_google_events = function () {
		if ( cartflows.ga_setting.enable_google_analytics === 'enable' ) {
			if (
				cartflows.is_checkout_page &&
				'enable' === cartflows.ga_setting.enable_add_payment_info
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						gtag(
							'event',
							'add_payment_info',
							JSON.parse( cartflows.add_payment_info_data )
						);
					}
				);
			} else if (
				cartflows.is_optin &&
				'enable' === cartflows.ga_setting.enable_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						gtag( 'event', 'Lead', { plugin: 'CartFlows' } );
					}
				);
			}
		}
	};

	/**
	 *
	 * @param {string} next_step_url
	 * @return {string} next_step_url Modified string if query string is present.
	 */
	const may_be_append_query_string = function ( next_step_url ) {
		// Get the URL parameters.
		const urlParams = new URLSearchParams( window.location.search );

		// Return the URL if no query string is present.
		if ( urlParams.length <= 0 ) {
			return next_step_url;
		}

		// Get all URL parameter keys.
		const keys = urlParams.keys();

		// Store all parameters temporary.
		const params = {};

		// Loop through all parameter as keys to make key-value pair array/object.
		for ( const key of keys ) {
			params[ key ] = urlParams.get( key );
		}

		// Convert the object to a query string.
		const queryString = new URLSearchParams( params ).toString();

		/**
		 * Append the query string to the URL
		 * If: No question mark is in the URL then: add the question mark before query string.
		 * Else: Question mark is found then: add '&' before query string..
		 */
		if ( next_step_url.indexOf( '?' ) === -1 ) {
			next_step_url = next_step_url + '?' + queryString;
		} else {
			next_step_url = next_step_url + '&' + queryString;
		}

		return next_step_url;
	};

	/**
	 * Assign the class & link to specific button
	 */
	const setup_next_step_url = function () {
		const next_links = $( 'a[href*="wcf-next-step"]' );

		if (
			next_links.length > 0 &&
			'undefined' !== typeof cartflows.next_step
		) {
			next_links.addClass( 'wcf-next-step-link' );
			next_links.attr(
				'href',
				may_be_append_query_string( cartflows.next_step )
			);
		}
	};

	$( function () {
		setup_next_step_url();
		remove_oceanwp_custom_style();
		if ( '1' !== cartflows.is_pb_preview ) {
			trigger_facebook_events();
			trigger_google_events();
		}
	} );
} )( jQuery );
