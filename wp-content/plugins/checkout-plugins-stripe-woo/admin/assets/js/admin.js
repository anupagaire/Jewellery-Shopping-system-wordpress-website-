( function( $ ) {
	if ( cpsw_ajax_object.is_manually_connected === 'manually' ) {
		HideShowKeys( true );
		$( '#cpsw_mode' ).closest( 'tr' ).hide();
		$( '#cpsw_debug_log' ).closest( 'tr' ).hide();
	} else {
		HideShowKeys( false );
		$( '.cpsw_inline_notice' ).hide();
	}

	$( 'a[href="' + cpsw_ajax_object.site_url + '&tab=checkout&section=cpsw_api_settings"]' ).attr( 'href', cpsw_ajax_object.site_url + '&tab=cpsw_api_settings' );

	$( 'a[href="' + cpsw_ajax_object.site_url + '&tab=checkout&section="]' ).closest( 'li' ).remove();

	if ( $( 'a[href="' + cpsw_ajax_object.site_url + '&tab=cpsw_api_settings"]' ).hasClass( 'nav-tab-active' ) ) {
		$( 'a[href="' + cpsw_ajax_object.site_url + '&tab=checkout"]' ).addClass( 'nav-tab-active' );
	}

	if ( cpsw_ajax_object.cpsw_mode === 'live' ) {
		$( '#cpsw_test_webhook_secret' ).closest( 'tr' ).hide();
	}

	if ( cpsw_ajax_object.cpsw_mode === 'test' ) {
		$( '#cpsw_live_webhook_secret' ).closest( 'tr' ).hide();
	}

	if ( cpsw_ajax_object.is_connected === '' && 'cpsw_api_settings' === cpsw_ajax_object.cpsw_admin_settings_tab ) {
		$( '.woocommerce-save-button' ).hide();
	}

	$( document ).on( 'click', '.cpsw_show', function() {
		$( this ).hide();
		$( '.cpsw_hide' ).show();
		HideShowKeys( true );
	} );

	$( document ).on( 'click', '.cpsw_hide', function() {
		$( this ).hide();
		$( '.cpsw_show' ).show();
		HideShowKeys( false );
	} );

	function HideShowKeys( cond = '' ) {
		if ( cond === true ) {
			$( '#cpsw_test_pub_key' ).closest( 'tr' ).show();
			$( '#cpsw_test_secret_key' ).closest( 'tr' ).show();
			$( '#cpsw_pub_key' ).closest( 'tr' ).show();
			$( '#cpsw_secret_key' ).closest( 'tr' ).show();
			if ( cpsw_ajax_object.is_connected === '' ) {
				const connectButton = '<button name="connect" class="button-primary" type="button" id="cpsw_test_connection" data-mode="manual">' + cpsw_ajax_object.test_btn_label + '</button>';
				$( '.woocommerce .submit' ).append( connectButton );
				$( '.woocommerce-save-button' ).hide();
			}
		}
		if ( cond === false ) {
			$( '#cpsw_test_pub_key' ).closest( 'tr' ).hide();
			$( '#cpsw_test_secret_key' ).closest( 'tr' ).hide();
			$( '#cpsw_pub_key' ).closest( 'tr' ).hide();
			$( '#cpsw_secret_key' ).closest( 'tr' ).hide();
			if ( cpsw_ajax_object.is_connected === '' ) {
				$( '#cpsw_test_connection' ).remove();
			}
		}
	}

	$( document ).on( 'click', '#cpsw_test_connection', function( e ) {
		e.preventDefault();
		const cpswTestSecretKey = $( '#cpsw_test_secret_key' ).val();
		const cpswSecretKey = $( '#cpsw_secret_key' ).val();
		const cpswTestPubKey = $( '#cpsw_test_pub_key' ).val();
		const cpswPubKey = $( '#cpsw_pub_key' ).val();

		const messages = [];

		if ( ( '' !== cpswTestSecretKey && '' !== cpswTestPubKey ) || ( '' !== cpswSecretKey && '' !== cpswPubKey ) ) {
			$.blockUI( { message: '' } );
			const mode = ( 'undefined' === typeof $( this ).data( 'mode' ) ) ? '' : $( this ).data( 'mode' );
			$.ajax( {
				type: 'GET',
				dataType: 'json',
				url: cpsw_ajax_object.ajax_url,
				data: { action: 'cpsw_test_stripe_connection', _security: cpsw_ajax_object.admin_nonce, cpsw_test_sec_key: cpswTestSecretKey, cpsw_secret_key: cpswSecretKey },
				beforeSend: () => {
					$( 'body' ).css( 'cursor', 'progress' );
				},
				success( response ) {
					const res = response.data.data;
					let br = '';
					let icon = '❌';
					if ( res.live.status !== 'invalid' ) {
						if ( res.live.status === 'success' ) {
							icon = '✔';
						} else {
							$( '#cpsw_secret_key' ).val( '' );
							$( '#cpsw_pub_key' ).val( '' );
						}
						messages.push( res.live.mode + ' ' + icon + '\n' + res.live.message );
						br = '----\n';
					} else {
						if ( 'manual' !== mode ) {
							messages.push( res.live.mode + ' ' + icon + '\n' + cpsw_ajax_object.stripe_key_unavailable );
							br = '----\n';
						}
						$( '#cpsw_secret_key' ).val( '' );
						$( '#cpsw_pub_key' ).val( '' );
					}
					icon = '❌';
					if ( res.test.status !== 'invalid' ) {
						if ( res.test.status === 'success' ) {
							icon = '✔';
						} else {
							$( '#cpsw_test_secret_key' ).val( '' );
							$( '#cpsw_test_pub_key' ).val( '' );
						}
						messages.push( br + res.test.mode + ' ' + icon + '\n' + res.test.message );
					} else {
						if ( 'manual' !== mode ) {
							messages.push( br + res.test.mode + ' ' + icon + '\n' + cpsw_ajax_object.stripe_key_unavailable );
						}
						$( '#cpsw_test_secret_key' ).val( '' );
						$( '#cpsw_test_pub_key' ).val( '' );
					}
					$.unblockUI();
					alert( messages.join( '\n' ) );
					$( 'body' ).css( 'cursor', 'default' );
					if ( 'manual' === mode && ( 'success' === res.live.status || 'success' === res.test.status ) ) {
						$( '.woocommerce-save-button' ).trigger( 'click' );
					}
				},
				error() {
					$( 'body' ).css( 'cursor', 'default' );
					$.unblockUI();
					alert( cpsw_ajax_object.stripe_key_error + cpsw_ajax_object.cpsw_mode );
				},
			} );
		} else {
			alert( cpsw_ajax_object.stripe_key_notice );
		}
	} );

	$( document ).on( 'click', '#cpsw_delete_webhook_key_test, #cpsw_delete_webhook_key_live', function( e ) {
		e.preventDefault();
		$.ajax( {
			type: 'GET',
			dataType: 'json',
			url: cpsw_ajax_object.ajax_url,
			data: { action: 'cpsw_delete_webhook', _security: cpsw_ajax_object.admin_nonce, webhook_key: $( this ).data( 'webhook-secret-key' ), mode: $( this ).data( 'mode' ) },
			beforeSend: () => {
				$.blockUI( { message: '' } );
				$( 'body' ).css( 'cursor', 'progress' );
			},
			success( response ) {
				if ( response.success === true ) {
					alert( cpsw_ajax_object.delete_webhook );
					window.location.href = cpsw_ajax_object.dashboard_url;
				} else if ( response.success === false ) {
					alert( response.data.message );
				}
				$( 'body' ).css( 'cursor', 'default' );
				$.unblockUI();
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				$.unblockUI();
				alert( cpsw_ajax_object.generic_error );
			},
		} );
	} );

	$( document ).on( 'click', '#cpsw_create_webhook_key_test, #cpsw_create_webhook_key_live', function( e ) {
		e.preventDefault();
		$.ajax( {
			type: 'GET',
			dataType: 'json',
			url: cpsw_ajax_object.ajax_url,
			data: { action: 'cpsw_create_webhook', _security: cpsw_ajax_object.admin_nonce, mode: $( this ).data( 'mode' ) },
			beforeSend: () => {
				$.blockUI( { message: '' } );
				$( 'body' ).css( 'cursor', 'progress' );
			},
			success( response ) {
				if ( response.success === true ) {
					alert( cpsw_ajax_object.create_webhook );
					window.location.href = cpsw_ajax_object.dashboard_url;
				} else if ( response.success === false ) {
					alert( response.data.message );
				}
				$( 'body' ).css( 'cursor', 'default' );
				$.unblockUI();
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				$.unblockUI();
				alert( cpsw_ajax_object.generic_error );
			},
		} );
	} );

	$( document ).on( 'click', '#cpsw_disconnect_acc', function( e ) {
		e.preventDefault();
		$.ajax( {
			type: 'GET',
			dataType: 'json',
			url: cpsw_ajax_object.ajax_url,
			data: { action: 'cpsw_disconnect_account', _security: cpsw_ajax_object.admin_nonce },
			beforeSend: () => {
				$( 'body' ).css( 'cursor', 'progress' );
				$.blockUI( { message: '' } );
			},
			success( response ) {
				if ( response.success === true ) {
					const icon = '✔';
					alert( response.data.message + ' ' + icon );
					window.location.href = cpsw_ajax_object.dashboard_url;
				} else if ( response.success === false ) {
					alert( response.data.message );
				}
				$( 'body' ).css( 'cursor', 'default' );
				$.unblockUI();
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				$.unblockUI();
				alert( cpsw_ajax_object.generic_error );
			},
		} );
	} );

	$( document ).on( 'click', '#cpsw_connect_other_acc', function( e ) {
		e.preventDefault();
		$.ajax( {
			type: 'GET',
			dataType: 'json',
			url: cpsw_ajax_object.ajax_url,
			data: { action: 'cpsw_disconnect_account', _security: cpsw_ajax_object.admin_nonce },
			beforeSend: () => {
				$( 'body' ).css( 'cursor', 'progress' );
			},
			success( response ) {
				if ( response.success === true ) {
					alert( cpsw_ajax_object.stripe_connect_other_acc );
					window.location.href = cpsw_ajax_object.dashboard_url;
				} else if ( response.success === false ) {
					alert( response.data.message );
				}
				$( 'body' ).css( 'cursor', 'default' );
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				alert( cpsw_ajax_object.generic_error );
			},
		} );
	} );
	$( document ).ready( function() {
		$( '.cpsw_select_woo' ).selectWoo();
	} );

	const CPSWAdminPaymentSettings = {
		init() {
			$( '[name^="woocommerce_' + cpsw_ajax_object.cpsw_admin_current_page + '_allowed_countries"]' ).on( 'change', this.toggle_select_country_sections );
			$( '[name^="cpsw_mode"]' ).on( 'change', this.show_hide_webhook_secret );

			this.toggle_select_country_sections();
			this.show_hide_webhook_secret();
		},

		/**
		 * Show hide webhook secret
		 */
		show_hide_webhook_secret() {
			const mode = $( '#cpsw_mode' ).val();

			if ( 'test' === mode ) {
				$( '#cpsw_test_webhook_secret' ).parents( 'tr' ).show();
				$( '#cpsw_live_webhook_secret' ).parents( 'tr' ).hide();
				$( '#cpsw_create_webhook_key_test, #cpsw_delete_webhook_key_test' ).parents( 'tr' ).show();
				$( '#cpsw_create_webhook_key_live, #cpsw_delete_webhook_key_live' ).parents( 'tr' ).hide();
			} else if ( 'live' === mode ) {
				$( '#cpsw_test_webhook_secret' ).parents( 'tr' ).hide();
				$( '#cpsw_live_webhook_secret' ).parents( 'tr' ).show();
				$( '#cpsw_create_webhook_key_test, #cpsw_delete_webhook_key_test' ).parents( 'tr' ).hide();
				$( '#cpsw_create_webhook_key_live, #cpsw_delete_webhook_key_live' ).parents( 'tr' ).show();
			}
		},

		/**
		 * Show hide country dorpdown
		 */
		toggle_select_country_sections() {
			const getOption = $( ' [name^="woocommerce_' + cpsw_ajax_object.cpsw_admin_current_page + '_allowed_countries"] ' ).val();
			const exceptCountries = $( '[name^="woocommerce_' + cpsw_ajax_object.cpsw_admin_current_page + '_except_countries[]"]' ).parents( 'tr' );
			const specificCountries = $( '[name^="woocommerce_' + cpsw_ajax_object.cpsw_admin_current_page + '_specific_countries[]"]' ).parents( 'tr' );

			if ( getOption === 'all_except' ) {
				exceptCountries.show();
				specificCountries.hide();
			} else if ( getOption === 'specific' ) {
				exceptCountries.hide();
				specificCountries.show();
			} else {
				exceptCountries.hide();
				specificCountries.hide();
			}
		},
	};

	$( function() {
		CPSWAdminPaymentSettings.init();
	} );
}( jQuery ) );
