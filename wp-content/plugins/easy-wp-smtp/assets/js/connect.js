/* globals easy_wp_smtp_connect */

/**
 * Connect functionality - Upgrade plugin from Lite to Pro version.
 *
 * @since 2.1.0
 */

'use strict';

var EasyWPSMTPConnect = window.EasyWPSMTPConnect || ( function( document, window, $ ) {

	/**
	 * Elements reference.
	 *
	 * @since 2.1.0
	 *
	 * @type {object}
	 */
	var el = {
		$connectBtn: $( '#easy-wp-smtp-setting-upgrade-license-button' ),
		$connectKey: $( '#easy-wp-smtp-setting-upgrade-license-key' )
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 2.1.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 2.1.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 2.1.0
		 */
		ready: function() {

			app.events();
		},

		/**
		 * Register JS events.
		 *
		 * @since 2.1.0
		 */
		events: function() {

			app.connectBtnClick();
		},

		/**
		 * Register connect button event.
		 *
		 * @since 2.1.0
		 */
		connectBtnClick: function() {

			el.$connectBtn.on( 'click', function() {
				app.gotoUpgradeUrl();
			} );
		},

		/**
		 * Get the alert arguments in case of Pro already installed.
		 *
		 * @since 2.1.0
		 *
		 * @param {object} res Ajax query result object.
		 *
		 * @returns {object} Alert arguments.
		 */
		proAlreadyInstalled: function( res ) {

			return {
				title: easy_wp_smtp_connect.text.almost_done,
				content: res.data.message,
				icon: EasyWPSMTP.Admin.Settings.getModalIcon( 'check-circle-green' ),
				type: 'green',
				buttons: {
					confirm: {
						text: easy_wp_smtp_connect.text.plugin_activate_btn,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action: function() {
							window.location.reload();
						},
					},
				},
			};
		},

		/**
		 * Go to upgrade url.
		 *
		 * @since 2.1.0
		 */
		gotoUpgradeUrl: function() {

			var data = {
				action: 'easy_wp_smtp_connect_url',
				key:  el.$connectKey.val(),
				nonce: easy_wp_smtp_connect.nonce,
			};

			el.$connectBtn.addClass( 'easy-wp-smtp-btn--loading' );

			$.post( easy_wp_smtp_connect.ajax_url, data )
				.done( function( res ) {
					if ( res.success ) {
						if ( res.data.reload ) {
							$.alert( app.proAlreadyInstalled( res ) );
							return;
						}
						window.location.href = res.data.url;
						return;
					}
					$.alert( {
						title: easy_wp_smtp_connect.text.oops,
						content: res.data.message,
						icon: EasyWPSMTP.Admin.Settings.getModalIcon( 'exclamation-triangle-orange' ),
						type: 'orange',
						buttons: {
							confirm: {
								text: easy_wp_smtp_connect.text.ok,
								btnClass: 'btn-confirm',
								keys: [ 'enter' ],
							},
						},
					} );
				} )
				.fail( function( xhr ) {
					app.failAlert( xhr );
				} ).always( function() {
					el.$connectBtn.removeClass( 'easy-wp-smtp-btn--loading' );
				} );
		},

		/**
		 * Alert in case of server error.
		 *
		 * @since 2.1.0
		 *
		 * @param {object} xhr XHR object.
		 */
		failAlert: function( xhr ) {

			$.alert( {
				title: easy_wp_smtp_connect.text.oops,
				content: easy_wp_smtp_connect.text.server_error + '<br>' + xhr.status + ' ' + xhr.statusText + ' ' + xhr.responseText,
				icon: EasyWPSMTP.Admin.Settings.getModalIcon( 'exclamation-circle-regular-red' ),
				type: 'red',
				buttons: {
					confirm: {
						text: easy_wp_smtp_connect.text.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
EasyWPSMTPConnect.init();
