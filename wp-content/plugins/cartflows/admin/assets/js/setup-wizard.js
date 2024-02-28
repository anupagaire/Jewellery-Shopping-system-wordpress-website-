/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since x.x.x
 */

const CartFlowsAjaxQueue = ( function () {
	let requests = [];

	return {
		/**
		 * Add AJAX request
		 *
		 * @param {string} opt selected opt.
		 * @since x.x.x
		 */
		add( opt ) {
			requests.push( opt );
		},

		/**
		 * Remove AJAX request
		 *
		 * @param {string} opt selected opt.
		 * @since x.x.x
		 */
		remove( opt ) {
			if ( jQuery.inArray( opt, requests ) > -1 ) {
				requests.splice( jQuery.inArray( opt, requests ), 1 );
			}
		},

		/**
		 * Run / Process AJAX request
		 *
		 * @since x.x.x
		 */
		run() {
			const self = this;
			let oriSuc;

			if ( requests.length ) {
				oriSuc = requests[ 0 ].complete;

				requests[ 0 ].complete = function () {
					if ( typeof oriSuc === 'function' ) {
						oriSuc();
					}
					requests.shift();
					self.run.apply( self, [] );
				};

				jQuery.ajax( requests[ 0 ] );
			} else {
				self.tid = setTimeout( function () {
					self.run.apply( self, [] );
				}, 1000 );
			}
		},

		/**
		 * Stop AJAX request
		 *
		 * @since x.x.x
		 */
		stop() {
			requests = [];
			clearTimeout( this.tid );
		},
	};
} )();

( function ( $ ) {
	const CartFlowsWizard = {
		remaining_install_plugins: 0,
		remaining_active_plugins: 0,

		init() {
			this._bind();
		},

		/**
		 * Bind
		 */
		_bind() {
			//Page builder installation & save option
			$( document )
				.on(
					'click',
					'.wcf-install-plugins',
					CartFlowsWizard._install_page_builder_plugin
				)
				.on(
					'click',
					'.wcf-usage-tracking',
					CartFlowsWizard._usage_tracking
				)
				.on(
					'change',
					'.page-builder-list',
					CartFlowsWizard._onChangePagebuilder
				)
				.on(
					'click',
					'.wcf-install-wc',
					CartFlowsWizard._install_required_plugins
				)
				.on( 'wp-plugin-installing', CartFlowsWizard._pluginInstalling )
				.on( 'wp-plugin-install-error', CartFlowsWizard._installError )
				.on(
					'wp-plugin-install-success',
					CartFlowsWizard._installSuccess
				)
				.on(
					'click',
					'.sendinblue-form-submit',
					CartFlowsWizard._onSendinblueSubmit
				);
		},

		/**
		 * Install Now
		 *
		 * @param {Object} event event data.
		 */
		_install_page_builder_plugin( event ) {
			event.preventDefault();

			const $button = $( this ),
				// Selected page builder
				plugin_slug =
					$( '.page-builder-list option:selected' ).data( 'slug' ) ||
					'',
				is_installed =
					$( '.page-builder-list option:selected' ).data(
						'install'
					) || 'no',
				is_activeted =
					$( '.page-builder-list option:selected' ).data(
						'active'
					) || 'no',
				plugin_init =
					$( '.page-builder-list option:selected' ).data( 'init' ) ||
					'',
				redirect_link =
					$( '.wcf-redirect-link' ).data( 'redirect-link' ) || '';

			if (
				$button.hasClass( 'updating-message' ) ||
				$button.hasClass( 'button-disabled' )
			) {
				return;
			}
			$button.addClass( 'updating-message' );
			//Save page builder option in global settings.
			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'page_builder_save_option',
					page_builder: plugin_slug,
				},
			} )
				.success( function () {
					console.log( 'Option Saved Successfully.' );
				} )

				.fail( function () {
					console.log( 'error' );
				} );

			if ( 'yes' === is_installed && 'no' === is_activeted ) {
				CartFlowsWizard._activatePlugin(
					plugin_init,
					plugin_slug,
					true
				);
			} else if ( 'no' === is_installed ) {
				CartFlowsWizard._installPlugin( plugin_slug );
			} else {
				window.location = redirect_link;
			}
		},

		_install_required_plugins( event ) {
			event.preventDefault();

			const $button = $( this );

			if (
				$button.hasClass( 'updating-message' ) ||
				$button.hasClass( 'button-disabled' )
			) {
				return;
			}

			$button.addClass( 'updating-message' );
			const redirect_link =
				$( '.wcf-redirect-link' ).data( 'redirect-link' ) || '';

			console.log( cartflows_setup_vars.plugin_status );

			const page_builder_plugins = cartflows_setup_vars.plugins;

			$.each( page_builder_plugins, function ( plugin, status ) {
				if ( 'not-installed' === status ) {
					CartFlowsWizard.remaining_install_plugins++;
				}
				if ( 'inactive' === status ) {
					CartFlowsWizard.remaining_active_plugins++;
				}
			} );

			// Have any plugin for install?
			if ( CartFlowsWizard.remaining_install_plugins ) {
				CartFlowsWizard._install_all_plugins();
			} else if ( CartFlowsWizard.remaining_active_plugins ) {
				CartFlowsWizard._activate_all_plugins();
			} else if (
				! CartFlowsWizard.remaining_active_plugins &&
				! CartFlowsWizard.remaining_install_plugins
			) {
				window.location = redirect_link;
			}
		},

		_usage_tracking() {
			let allow_usage_tracking = document.getElementById(
				'cartflows-usage-tracking-option'
			);

			if ( allow_usage_tracking && allow_usage_tracking.checked ) {
				allow_usage_tracking = true;
			} else {
				allow_usage_tracking = false;
			}

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'usage_tracking_option',
					allow_usage_tracking,
					security:
						cartflows_setup_vars.wcf_usage_tracking_option_nonce,
				},
			} )
				.done( function ( response ) {
					if ( response.success ) {
						console.log( 'Option Updated.' );
					}
				} )
				.fail( function () {
					console.log( 'error' );
				} )
				.always( function () {
					console.log( 'complete' );
				} );
		},

		_onSendinblueSubmit( event ) {
			event.preventDefault();
			event.stopPropagation();

			const form = $( this ).closest( 'form' ),
				name_field = form.find( '#cartflows_onboarding_name' ).val()
					? form.find( '#cartflows_onboarding_name' ).val()
					: '',
				email_field = form.find( '#cartflows_onboarding_email' ),
				reg =
					/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,14})$/;

			if ( reg.test( email_field.val() ) === false ) {
				email_field.addClass( 'wcf-error' );
				return false;
			}
			email_field.removeClass( 'wcf-error' );

			const submit_button = $( this );
			submit_button.attr( 'disabled', 'disabled' );

			const nonce = form.find( '#wcf_user_onboarding_nonce' ).val();
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'wcf_user_onboarding',
					user_email: email_field.val(),
					user_fname: name_field,
					security: nonce,
				},
				// async: false,
				success( response ) {
					console.log( 'in success' );

					if ( response.data.success ) {
						console.log( response.data.message );
						const redirect_link =
							$( '.wcf-redirect-link' ).data( 'redirect-link' ) ||
							'';
						window.location = redirect_link;
					} else {
						const error_field = form.find( '.onboarding-error' );
						console.log( response.data.message );
						error_field.text( response.data.message );
						submit_button.removeAttr( 'disabled' );
						return false;
					}
				},
				error( response ) {
					console.log( 'in error' );
					console.log( response );
				},
			} );

			/* Do not execute anything here */
		},

		_onChangePagebuilder() {
			let plugin_slug =
				$( '.page-builder-list option:selected' ).data( 'slug' ) || '';
			const new_url = 'https://wordpress.org/plugins/' + plugin_slug;

			$( '.cartflows-setup-extra-notice' ).show();
			if ( 'other' === plugin_slug || 'divi' === plugin_slug ) {
				$( '.cartflows-setup-extra-notice' ).hide();
				return;
			}

			plugin_slug = plugin_slug.replace( /-/gi, ' ' );
			$( '#wcf-page-builder' ).attr( 'href', new_url );
			$( '#wcf-page-builder' ).html( plugin_slug );
		},

		_installPlugin( plugin_slug ) {
			if (
				wp.updates.shouldRequestFilesystemCredentials &&
				! wp.updates.ajaxLocked
			) {
				wp.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', function () {
					const $message = $( '.install-now.updating-message' );

					$message
						.removeClass( 'updating-message' )
						.text( wp.updates.l10n.installNow );

					wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
				} );
			}

			wp.updates.queue.push( {
				action: 'install-plugin', // Required action.
				data: {
					slug: plugin_slug,
				},
			} );

			// Required to set queue.
			wp.updates.queueChecker();
		},

		_activatePlugin( plugin_init, plugin_slug, redirect = false ) {
			const redirect_link =
				$( '.wcf-redirect-link' ).data( 'redirect-link' ) || '';

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'wcf_activate_plugin',
					plugin_slug,
					plugin_init,
					security: cartflows_setup_vars.wcf_activate_plugin_nonce,
				},
			} )
				.done( function ( response ) {
					if ( response.success && redirect ) {
						console.log( plugin_slug + ' activated' );
						window.location = redirect_link;
					}
				} )
				.fail( function () {
					console.log( 'activation error' );
				} );
		},

		/**
		 * Installing Plugin
		 *
		 * @param {Object} event event data.
		 */
		_pluginInstalling( event ) {
			event.preventDefault();
			console.log( 'Installing..' );
		},

		/**
		 * Install Error
		 *
		 * @param {Object} event event data.
		 */
		_installError( event ) {
			event.preventDefault();
			console.log( 'Install Error!' );

			const redirect_link =
				$( '.wcf-redirect-link' ).data( 'redirect-link' ) || '';
			console.log( redirect_link );
			if ( '' !== redirect_link ) {
				window.location = redirect_link;
				console.log( 'redirecting..' );
			}
		},

		/**
		 * Install Success
		 *
		 * @param {Object} event event data.
		 * @param {Array}  args  args data.
		 */
		_installSuccess( event, args ) {
			event.preventDefault();
			const plugin_init = args.slug + '/' + args.slug + '.php';
			const plugin_slug = args.slug;

			// WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
			setTimeout( function () {
				CartFlowsWizard._activatePlugin( plugin_init, plugin_slug );

				if ( CartFlowsWizard.remaining_install_plugins > 0 ) {
					CartFlowsWizard.remaining_install_plugins--;
				}

				if ( ! CartFlowsWizard.remaining_install_plugins ) {
					CartFlowsWizard._activate_all_plugins();
				}
			}, 1500 );
		},

		_install_all_plugins() {
			const page_builder_plugins = cartflows_setup_vars.plugins;

			$.each( page_builder_plugins, function ( plugin, status ) {
				if ( 'not-installed' === status ) {
					CartFlowsWizard._installPlugin( plugin );
				}
			} );
		},

		_activate_all_plugins() {
			const redirect_link =
				$( '.wcf-redirect-link' ).data( 'redirect-link' ) || '';
			if (
				! CartFlowsWizard.remaining_active_plugins &&
				! CartFlowsWizard.remaining_install_plugins
			) {
				window.location = redirect_link;
			}

			const page_builder_plugins = cartflows_setup_vars.plugins;

			// Activate All Plugins.
			CartFlowsAjaxQueue.stop();
			CartFlowsAjaxQueue.run();

			$.each( page_builder_plugins, function ( plugin, status ) {
				const plugin_init = plugin + '/' + plugin + '.php';
				const plugin_slug = plugin;

				if ( 'inactive' === status ) {
					CartFlowsAjaxQueue.add( {
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wcf_activate_plugin',
							plugin_slug,
							plugin_init,
							security:
								cartflows_setup_vars.wcf_activate_plugin_nonce,
						},
						success() {
							CartFlowsWizard.remaining_active_plugins--;

							if (
								! CartFlowsWizard.remaining_active_plugins &&
								! CartFlowsWizard.remaining_install_plugins
							) {
								window.location = redirect_link;
							}
						},
					} );
				}
			} );
		},
	};

	$( function () {
		CartFlowsWizard.init();
	} );
} )( jQuery );
