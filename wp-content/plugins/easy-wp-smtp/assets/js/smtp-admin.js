/* globals easy_wp_smtp, jconfirm, ajaxurl */
'use strict';

var EasyWPSMTP = window.EasyWPSMTP || {};
EasyWPSMTP.Admin = EasyWPSMTP.Admin || {};

/**
 * Easy WP SMTP Admin area module.
 *
 * @since 2.0.0
 */
EasyWPSMTP.Admin.Settings = EasyWPSMTP.Admin.Settings || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 2.0.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * State attribute showing if one of the plugin settings
		 * changed and was not yet saved.
		 *
		 * @since 2.0.0
		 *
		 * @type {boolean}
		 */
		pluginSettingsChanged: false,

		/**
		 * Start the engine. DOM is not ready yet, use only to init something.
		 *
		 * @since 2.0.0
		 */
		init: function() {

			// Do that when DOM is ready.
			$( app.ready );
		},

		/**
		 * DOM is fully loaded.
		 *
		 * @since 2.0.0
		 */
		ready: function() {

			app.pageHolder = $( '.easy-wp-smtp-tab-settings' );

			app.settingsForm = $( '.easy-wp-smtp-connection-settings-form' );

			// If there are screen options we have to move them.
			if( $( '#screen-meta-links' ).length > 0 ) {
				$( '#screen-meta-links, #screen-meta' ).prependTo( '#easy-wp-smtp-header-temp' );
				$( '#screen-meta-links' ).show();
			}

			app.bindActions();

			app.setJQueryConfirmDefaults();

			// Clear Debug Log handler.
			$( '#easy-wp-smtp-clean-debug-log' ).click( function( e ) {
				e.preventDefault();

				var $btn = $( this );

				$.confirm( {
					backgroundDismiss: false,
					escapeKey: true,
					animationBounce: 1,
					type: 'orange',
					icon: app.getModalIcon( 'exclamation-triangle-orange' ),
					title: easy_wp_smtp.heads_up_title,
					content: easy_wp_smtp.clear_debug_log,
					buttons: {
						confirm: {
							text: easy_wp_smtp.yes_text,
							btnClass: 'btn-confirm',
							keys: [ 'enter' ],
							action: function() {
								$btn.addClass( 'easy-wp-smtp-btn--loading' );

								jQuery.ajax( {
									url: ajaxurl,
									type: "post",
									data: {action: "swpsmtp_clear_log", nonce: easy_wp_smtp.nonce}
								} ).done( function( data ) {
									var message, icon, type;

									if ( data === '1' ) {
										message = easy_wp_smtp.debug_log_cleared;
										icon = 'check-circle-green';
										type = 'green';
									} else {
										message = easy_wp_smtp.error_occurred + ' ' + data;
										icon = 'times-circle-red';
										type = 'red';
									}

									app.displayAlertModal( message, icon, type );
									$btn.removeClass( 'easy-wp-smtp-btn--loading' );
								} );
							}
						},
						cancel: {
							text: easy_wp_smtp.cancel_text,
							btnClass: 'btn-cancel',
						}
					}
				} );
			} );
		},

		/**
		 * Process all generic actions/events, mostly custom that were fired by our API.
		 *
		 * @since 2.0.0
		 */
		bindActions: function() {

			app.mailers.smtp.bindActions();
			app.triggerExitNotice();
			app.beforeSaveChecks();

			// Open/close meta box.
			$( '.easy-wp-smtp-meta-box__header' ).on( 'click', function( e ) {

				// Prevent meta box close/open if link or button was clicked.
				if ( e.target.tagName === 'A' || e.target.tagName === 'BUTTON' ) {
					return;
				}

				$( this ).closest( '.easy-wp-smtp-meta-box' ).toggleClass( 'easy-wp-smtp-meta-box--closed' );
			} );

			// Hide all mailers options and display for a currently clicked one.
			$( '.easy-wp-smtp-mailers-picker__input', app.settingsForm ).on( 'change', function() {
				$( '.easy-wp-smtp-mailer-options', app.settingsForm ).removeClass( 'easy-wp-smtp-mailer-options--active' );
				$( '.easy-wp-smtp-mailer-options[data-mailer="' + $( this ).val() + '"]', app.settingsForm ).addClass( 'easy-wp-smtp-mailer-options--active' );
			} );

			// Display education modal for mailer if it's disabled.
			$( '.easy-wp-smtp-mailers-picker__mailer--disabled', app.settingsForm ).on( 'click', function() {
				var $input = $( this ).prev( '.easy-wp-smtp-mailers-picker__input' );

				if ( $input.hasClass( 'easy-wp-smtp-educate' ) ) {
					app.education.upgradeMailer( $input );
				}
			} );

			// Register change event to show/hide plugin supported settings for currently selected mailer.
			$( '.easy-wp-smtp-mailers-picker__input', app.settingsForm ).on( 'change', this.processMailerSettingsOnChange );

			// Show/hide advanced settings.
			$( '#easy-wp-smtp-setting-advanced', app.settingsForm ).on( 'change', function() {
				$( this ).closest( '.easy-wp-smtp-row' ).nextAll( '.easy-wp-smtp-row' )[ $( this ).is( ':checked' ) ? 'removeClass' : 'addClass' ]( 'easy-wp-smtp-hidden' );
			} );

			// Update custom test email fields.
			$( '#easy-wp-smtp-setting-test_email_custom' ).on( 'change', function() {
				// Show/hide custom test email fields.
				$( '#easy-wp-smtp-setting-row-test_email_subject, #easy-wp-smtp-setting-row-test_email_message' ).toggle( $( this ).is( ':checked' ) );

				// Add/remove custom test email fields required prop.
				$( '#easy-wp-smtp-setting-test_email_subject, #easy-wp-smtp-setting-test_email_message' ).prop( 'required', $( this ).is( ':checked' ) );

				$( '#easy-wp-smtp-setting-test_email_html' ).prop( 'disabled', $( this ).is( ':checked' ) );

				var $html_email = $( '#easy-wp-smtp-setting-test_email_html' );

				if ( $( this ).is( ':checked' ) ) {
					$html_email.data( 'value', $html_email.is( ':checked' ) );
					$html_email.prop( 'checked', false ).prop( 'disabled', true );
				} else {
					$html_email.prop( 'checked', $html_email.data( 'value' ) ).prop( 'disabled', false );
				}
			} );

			// Dismiss Pro banner at the bottom of the page.
			$( '.js-easy-wp-smtp-pro-banner-dismiss', app.pageHolder ).on( 'click', function(e) {
				e.preventDefault();

				$.ajax( {
					url: ajaxurl,
					dataType: 'json',
					type: 'POST',
					data: {
						action: 'easy_wp_smtp_ajax',
						task: 'pro_banner_dismiss',
						nonce: easy_wp_smtp.nonce
					}
				} )
					.always( function() {
						$( '.easy-wp-smtp-pro-banner', app.pageHolder ).fadeOut( 'fast' );
					} );
			} );

			// Dissmis educational notices for certain mailers.
			$( '.js-easy-wp-smtp-mailer-notice-dismiss', app.settingsForm ).on( 'click', function( e ) {
				e.preventDefault();

				var $btn = $( this ),
					$notice = $btn.parents( '.easy-wp-smtp-notice' );

				if ( $btn.hasClass( 'disabled' ) ) {
					return false;
				}

				$.ajax( {
					url: ajaxurl,
					dataType: 'json',
					type: 'POST',
					data: {
						action: 'easy_wp_smtp_ajax',
						nonce: easy_wp_smtp.nonce,
						task: 'notice_dismiss',
						notice: $notice.data( 'notice' ),
						mailer: $notice.data( 'mailer' )
					},
					beforeSend: function() {
						$btn.addClass( 'disabled' );
					}
				} )
					.always( function() {
						$notice.fadeOut( 'fast', function() {
							$btn.removeClass( 'disabled' );
						} );
					} );
			} );

			// Show/hide debug output.
			$( '.easy-wp-smtp-test-email-debug .easy-wp-smtp-error-log-toggle' ).on( 'click', function( e ) {
				e.preventDefault();

				$( '.easy-wp-smtp-test-email-debug .easy-wp-smtp-error-log' ).slideToggle();
			} );

			// Copy debug output to clipboard.
			$( '.easy-wp-smtp-test-email-debug .easy-wp-smtp-error-log-copy' ).on( 'click', function( e ) {
				e.preventDefault();

				var $self = $( this );

				// Get error log.
				var $content = $( '.easy-wp-smtp-test-email-debug .easy-wp-smtp-error-log' );

				// Copy to clipboard.
				if ( ! $content.is( ':visible' ) ) {
					$content.addClass( 'easy-wp-smtp-error-log-selection' );
				}
				var range = document.createRange();
				range.selectNode( $content[ 0 ] );
				window.getSelection().removeAllRanges();
				window.getSelection().addRange( range );
				document.execCommand( 'Copy' );
				window.getSelection().removeAllRanges();
				$content.removeClass( 'easy-wp-smtp-error-log-selection' );

				$self.addClass( 'easy-wp-smtp-error-log-copy-copied' );

				setTimeout(
					function() {
						$self.removeClass( 'easy-wp-smtp-error-log-copy-copied' );
					},
					1500
				);
			} );

			// Remove mailer connection.
			$( '.js-easy-wp-smtp-provider-remove', app.settingsForm ).on( 'click', function() {
				return confirm( easy_wp_smtp.text_provider_remove );
			} );

			// Copy input text to clipboard.
			$( '.easy-wp-smtp-setting-copy', app.settingsForm ).on( 'click', function( e ) {
				e.preventDefault();

				var target = $( '#' + $( this ).data( 'source_id' ) ).get( 0 );

				target.select();
				document.execCommand( 'Copy' );

				var $copyIcon = $( this ).find( 'svg:first-child' ),
					$checkIcon = $( this ).find( 'svg:last-child' );

				$copyIcon.hide();

				$checkIcon
					.show()
					.fadeOut( 1000, 'swing', function() {
						$copyIcon.fadeIn( 200 );
					} );
			} );

			// Disable multiple click on the Email Test tab submit button and display a loader icon.
			$( '.easy-wp-smtp-tab-tools-test #easy-wp-smtp-email-test-form' ).on( 'submit', function() {
				var $button = $( this ).find( '.easy-wp-smtp-btn' );

				$button.attr( 'disabled', true );
				$button.addClass( 'easy-wp-smtp-btn--loading' );
			} );

			// Enable/disable domain check sub options.
			$( '#easy-wp-smtp-setting-domain_check' ).on( 'change', function() {
				$( '#easy-wp-smtp-setting-domain_check_allowed_domains, #easy-wp-smtp-setting-domain_check_do_not_send' ).prop( 'disabled', ! $( this ).is( ':checked' ) )
			} );
		},

		education: {
			upgradeMailer: function( $input ) {

				$.alert( {
					backgroundDismiss: true,
					escapeKey: true,
					animationBounce: 1,
					type: 'blue',
					closeIcon: true,
					title: easy_wp_smtp.education.upgrade_title.replace( /%name%/g, $input.data('title') ),
					icon: '"></i>' + easy_wp_smtp.education.upgrade_icon_lock + '<i class="',
					content: easy_wp_smtp.education.upgrade_content.replace( /%name%/g, $input.data('title') ) + easy_wp_smtp.education.upgrade_bonus,
					boxWidth: '550px',
					onOpenBefore: function() {
						this.$btnc.after( '<div class="easy-wp-smtp-already-purchased">' + easy_wp_smtp.education.upgrade_doc + '</div>' );
						this.$body.addClass( 'easy-wp-smtp-upgrade-mailer-education-modal' );
					},
					buttons: {
						confirm: {
							text: easy_wp_smtp.education.upgrade_button,
							btnClass: 'easy-wp-smtp-btn easy-wp-smtp-btn--green',
							keys: [ 'enter' ],
							action: function() {
								var appendChar = /(\?)/.test( easy_wp_smtp.education.upgrade_url ) ? '&' : '?',
									upgradeURL = easy_wp_smtp.education.upgrade_url + appendChar + 'utm_content=' + encodeURIComponent( $input.val() );

								window.open( upgradeURL, '_blank' );
							}
						}
					}
				} );
			}
		},

		/**
		 * Individual mailers specific js code.
		 *
		 * @since 2.0.0
		 */
		mailers: {
			smtp: {
				bindActions: function() {

					// Hide SMTP-specific user/pass when Auth disabled.
					$( '#easy-wp-smtp-setting-smtp-auth' ).on( 'change', function() {
						$( '#easy-wp-smtp-setting-row-smtp-user, #easy-wp-smtp-setting-row-smtp-pass' ).toggleClass( 'easy-wp-smtp-hidden' );
					} );

					// Port default values based on encryption type.
					$( '#easy-wp-smtp-setting-row-smtp-encryption input' ).on( 'change', function() {

						var $input = $( this ),
							$smtpPort = $( '#easy-wp-smtp-setting-smtp-port', app.settingsForm );

						if ( 'tls' === $input.val() ) {
							$smtpPort.val( '587' );
							$( '#easy-wp-smtp-setting-row-smtp-autotls' ).addClass( 'easy-wp-smtp-hidden' );
						} else if ( 'ssl' === $input.val() ) {
							$smtpPort.val( '465' );
							$( '#easy-wp-smtp-setting-row-smtp-autotls' ).removeClass( 'easy-wp-smtp-hidden' );
						} else {
							$smtpPort.val( '25' );
							$( '#easy-wp-smtp-setting-row-smtp-autotls' ).removeClass( 'easy-wp-smtp-hidden' );
						}
					} );
				}
			}
		},

		/**
		 * Exit notice JS code when plugin settings are not saved.
		 *
		 * @since 2.0.0
		 */
		triggerExitNotice: function() {

			var $settingPages = $( '.easy-wp-smtp-page-general' );

			// Display an exit notice, if settings are not saved.
			$( window ).on( 'beforeunload', function() {
				if ( app.pluginSettingsChanged ) {
					return easy_wp_smtp.text_settings_not_saved;
				}
			} );

			// Set settings changed attribute, if any input was changed.
			$( ':input:not( #easy-wp-smtp-setting-license-key, .easy-wp-smtp-not-form-input )', $settingPages ).on( 'change', function() {
				app.pluginSettingsChanged = true;
			} );

			// Clear the settings changed attribute, if the settings are about to be saved.
			$( 'form', $settingPages ).on( 'submit', function() {
				app.pluginSettingsChanged = false;
			} );
		},

		/**
		 * Perform any checks before the settings are saved.
		 *
		 * Checks:
		 * - warn users if they try to save the settings with the default (PHP) mailer selected.
		 *
		 * @since 2.0.0
		 */
		beforeSaveChecks: function() {

			app.settingsForm.on( 'submit', function() {
				if ( $( '.easy-wp-smtp-mailers-picker__input:checked', app.settingsForm ).val() === 'mail' ) {
					var $thisForm = $( this );

					$.alert( {
						backgroundDismiss: false,
						escapeKey: false,
						animationBounce: 1,
						type: 'orange',
						icon: app.getModalIcon( 'exclamation-triangle-orange' ),
						title: easy_wp_smtp.default_mailer_notice.title,
						content: easy_wp_smtp.default_mailer_notice.content,
						boxWidth: '550px',
						buttons: {
							confirm: {
								text: easy_wp_smtp.default_mailer_notice.save_button,
								btnClass: 'btn-confirm',
								keys: [ 'enter' ],
								action: function() {
									$thisForm.off( 'submit' ).trigger( 'submit' );
								}
							},
							cancel: {
								text: easy_wp_smtp.default_mailer_notice.cancel_button,
								btnClass: 'btn-cancel',
							},
						}
					} );

					return false;
				}
			} );
		},

		/**
		 * On change callback for showing/hiding plugin supported settings for currently selected mailer.
		 *
		 * @since 2.0.0
		 */
		processMailerSettingsOnChange: function() {

			var mailerSupportedSettings = easy_wp_smtp.all_mailers_supports[ $( this ).val() ];

			for ( var setting in mailerSupportedSettings ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( mailerSupportedSettings.hasOwnProperty( setting ) ) {
					$( '.js-easy-wp-smtp-setting-' + setting, app.settingsForm ).toggle( mailerSupportedSettings[ setting ] );
				}
			}

			// Special case: "from email" (group settings).
			var $mainSettingInGroup = $( '.js-easy-wp-smtp-setting-from_email' );

			$mainSettingInGroup.closest( '.easy-wp-smtp-setting-row' ).toggle(
				mailerSupportedSettings[ 'from_email' ] || mailerSupportedSettings[ 'from_email_force' ]
			);

			// Special case: "from name" (group settings).
			$mainSettingInGroup = $( '.js-easy-wp-smtp-setting-from_name' );

			$mainSettingInGroup.closest( '.easy-wp-smtp-setting-row' ).toggle(
				mailerSupportedSettings[ 'from_name' ] || mailerSupportedSettings[ 'from_name_force' ]
			);
		},

		/**
		 * Set jQuery-Confirm default options.
		 *
		 * @since 2.0.0
		 */
		setJQueryConfirmDefaults: function() {

			jconfirm.defaults = {
				typeAnimated: false,
				draggable: false,
				animateFromElement: false,
				theme: 'modern',
				boxWidth: '450px',
				useBootstrap: false
			};
		},

		/**
		 * Display the modal with provided text and icon.
		 *
		 * @since 2.0.0
		 *
		 * @param {string}   message        The message to be displayed in the modal.
		 * @param {string}   icon           The icon name from /assets/images/icons/ to be used in modal.
		 * @param {string}   type           The type of the message (red, green, orange, blue, purple, dark).
		 * @param {Function} actionCallback The action callback function.
		 */
		displayAlertModal: function( message, icon, type, actionCallback = undefined ) {

			type = type || 'default';
			actionCallback = actionCallback || function() {
			};

			$.alert( {
				backgroundDismiss: true,
				escapeKey: true,
				animationBounce: 1,
				type: type,
				closeIcon: true,
				title: false,
				icon: icon ? app.getModalIcon( icon ) : '',
				content: message,
				buttons: {
					confirm: {
						text: easy_wp_smtp.ok_text,
						btnClass: 'easy-wp-smtp-btn easy-wp-smtp-btn-md',
						keys: [ 'enter' ],
						action: actionCallback
					}
				}
			} );
		},

		/**
		 * Returns prepared modal icon.
		 *
		 * @since 2.0.0
		 *
		 * @param {string} icon The icon name from /assets/images/icons/ to be used in modal.
		 *
		 * @returns {string} Modal icon HTML.
		 */
		getModalIcon: function( icon ) {

			return '"></i><img src="' + easy_wp_smtp.plugin_url + '/assets/images/icons/' + icon + '.svg" alt=""><i class="';
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
EasyWPSMTP.Admin.Settings.init();
