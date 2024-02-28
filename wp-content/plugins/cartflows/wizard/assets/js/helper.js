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
					'.wcf-start-setup',
					CartFlowsWizard._redirect_next_step
				)
				.on(
					'click',
					'.install-page-builder-plugins',
					CartFlowsWizard._install_page_builder_plugin
				)
				.on(
					'click',
					'.wcf-import-global-flow',
					CartFlowsWizard._import_store_checkout_template
				)
				.on(
					'click',
					'.install-required-plugins',
					CartFlowsWizard._install_required_plugins
				)
				.on( 'wp-plugin-installing', CartFlowsWizard._pluginInstalling )
				.on( 'wp-plugin-install-error', CartFlowsWizard._installError )
				.on(
					'wp-plugin-install-success',
					CartFlowsWizard._installSuccess
				);
		},

		/**
		 * Dispatch the event to trigger the step redirect.
		 */
		_redirect_next_step() {
			// Rediret to Page builder step from home page.
			const redirect_page_builder_step_event = new Event(
				'wcf-redirect-page-builder-step'
			);
			document.dispatchEvent( redirect_page_builder_step_event );
		},

		/**
		 * Install Now
		 *
		 * @param {Object} event event data.
		 */
		_install_page_builder_plugin( event ) {
			event.preventDefault();

			let plugin_slug = '';

			document.dispatchEvent(
				new Event( 'wcf-page-builder-plugins-install-processing' )
			);

			// Selected page builder
			const plugin_key =
					$( '#wcf-selected-page-builder' ).attr(
						'data-selected-pb'
					) || '',
				plugin_data = cartflows_wizard.page_builders[ plugin_key ],
				is_installed = plugin_data.install,
				is_activeted = plugin_data.active,
				plugin_init = plugin_data.init;
			plugin_slug = plugin_data.slug;

			// Check plugin status first before saving the page builder option.
			if ( 'yes' === is_installed && 'no' === is_activeted ) {
				CartFlowsWizard._activatePlugin(
					plugin_init,
					plugin_slug,
					true
				);
			} else if ( 'no' === is_installed ) {
				CartFlowsWizard._installPlugin( plugin_slug );
			} else {
				// Save page builder option and continue.
				save_page_builder_option( plugin_slug );
			}
		},

		_install_required_plugins( event ) {
			event.preventDefault();

			// Fire an event to change the button state to processing.
			document.dispatchEvent(
				new Event( 'wcf-install-require-plugins-processing' )
			);

			const required_plugins = $( 'form.wcf-install-plugin-form' ).find(
				'input[type=checkbox]'
			);

			$.each( required_plugins, function ( index, plugin ) {
				const is_checked = $( plugin ).is( ':checked' ),
					status = plugin.getAttribute( 'data-status' );

				if ( is_checked ) {
					if ( 'not-installed' === status ) {
						CartFlowsWizard.remaining_install_plugins++;
					}
					if ( 'inactive' === status ) {
						CartFlowsWizard.remaining_active_plugins++;
					}
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
				trigger_event();
			}
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

		_activatePlugin( plugin_init, plugin_slug ) {
			const page_builder_slugs = [
				'elementor',
				'beaver-builder-lite-version',
				'divi',
				'ultimate-addons-for-gutenberg',
			];

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'cartflows_wizard_activate_plugin',
					plugin_slug,
					plugin_init,
					security: cartflows_wizard.wizard_activate_plugin_nonce,
				},
			} )
				.done( function ( response ) {
					if ( response.success ) {
						console.log( plugin_slug + ' activated' );
						// trigger_event();

						if (
							jQuery.inArray( plugin_slug, page_builder_slugs ) >
							-1
						) {
							save_page_builder_option( plugin_slug );
						}
					} else {
						console.log(
							'Error: ' + response.data && response.data.message
								? response.data.message
								: 'Plugin not activated'
						);
					}
				} )
				.fail( function () {
					console.log( 'activation error' );
				} );
		},

		/**
		 * Import the store checkout template.
		 *
		 * @param {Object} event
		 */
		_import_store_checkout_template( event ) {
			event.preventDefault();
			// Selected Template's ID.
			const store_template_flow =
					$( '#wcf-selected-store-checkout-template' ).attr(
						'data-selected-flow-info'
					) || '',
				primary_color = $( 'input[name=primary_color]' ).val(),
				selected_site_logo = $( '.wcf-selected-image' ).data(
					'logo-data'
				);

			// Send the event to react for processing when clicked on the footer button.
			document.dispatchEvent(
				new Event( 'wcf-store-checkout-import-text-processing' )
			);

			const has_error = new CustomEvent(
				'wcf-store-checkout-import-error',
				{
					detail: {
						is_error: false,
						errorMsg: '',
						callToAction: '',
					},
				}
			);

			//Import the requested template via ajax call.
			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'cartflows_import_store_checkout',
					security: cartflows_wizard.import_store_checkout_nonce,
					flow: store_template_flow,
					primary_color,
					site_logo: selected_site_logo,
				},
			} )
				.success( function ( res ) {
					if ( res.success && true === res.data.success ) {
						console.log( 'Funnels imported successfully.' );

						document.dispatchEvent(
							new Event( 'wcf-store-checkout-import-success' )
						);
					} else {
						has_error.detail.is_error = true;
						has_error.detail.errorMsg = res.data.message;
						has_error.detail.callToAction = res.data.call_to_action;

						document.dispatchEvent( has_error );
					}
				} )
				.fail( function () {
					has_error.detail.is_error = true;
					has_error.detail.errorMsg =
						cartflows_wizard.template_import_errors.api.title;
					has_error.detail.callToAction =
						cartflows_wizard.template_import_errors.api.msg;
					document.dispatchEvent( has_error );
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

			const redirect_link = $( '.wcf-redirect-link' ).attr( 'value' );
			if ( '' !== redirect_link ) {
				trigger_event();
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
			const required_plugins = $( 'form.wcf-install-plugin-form' ).find(
				'input[type=checkbox]'
			);

			$.each( required_plugins, function ( index, plugin ) {
				const pluginSlug = plugin.getAttribute( 'data-slug' ),
					status = plugin.getAttribute( 'data-status' ),
					is_checked = $( plugin ).is( ':checked' );

				if ( is_checked && 'not-installed' === status ) {
					CartFlowsWizard._installPlugin( pluginSlug );
				}
			} );
		},

		_activate_all_plugins() {
			if (
				! CartFlowsWizard.remaining_active_plugins &&
				! CartFlowsWizard.remaining_install_plugins
			) {
				trigger_event();
			}

			const required_plugins = $( 'form.wcf-install-plugin-form' ).find(
				'input[type=checkbox]'
			);

			// Activate All Plugins.
			CartFlowsAjaxQueue.stop();
			CartFlowsAjaxQueue.run();

			$.each( required_plugins, function ( index, plugin ) {
				const pluginSlug = plugin.getAttribute( 'data-slug' ),
					status = plugin.getAttribute( 'data-status' ),
					is_checked = $( plugin ).is( ':checked' ),
					plugin_init = pluginSlug + '/' + pluginSlug + '.php';

				if ( is_checked && 'inactive' === status ) {
					CartFlowsAjaxQueue.add( {
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'cartflows_wizard_activate_plugin',
							plugin_slug: pluginSlug,
							plugin_init,
							security:
								cartflows_wizard.wizard_activate_plugin_nonce,
						},
						success() {
							CartFlowsWizard.remaining_active_plugins--;

							if (
								! CartFlowsWizard.remaining_active_plugins &&
								! CartFlowsWizard.remaining_install_plugins
							) {
								trigger_event();
							}
						},
					} );
				}
			} );
		},
	};

	function trigger_event() {
		const custom_event = new Event( 'wcf-plugins-install-success' );
		document.dispatchEvent( custom_event );
	}

	function save_page_builder_option( plugin_slug ) {
		//Save page builder option in global settings.
		$.ajax( {
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cartflows_page_builder_save_option',
				security: cartflows_wizard.page_builder_save_option_nonce,
				page_builder: plugin_slug,
			},
		} )
			.success( function () {
				console.log( 'Option Saved Successfully.' );

				document.dispatchEvent(
					new Event( 'wcf-page-builder-plugins-install-success' )
				);
			} )
			.fail( function () {
				console.log( 'error' );
			} );
	}

	$( function () {
		CartFlowsWizard.init();
	} );
} )( jQuery );
