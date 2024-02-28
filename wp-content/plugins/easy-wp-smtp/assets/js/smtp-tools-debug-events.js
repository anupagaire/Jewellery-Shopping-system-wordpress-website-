/* global easy_wp_smtp_tools_debug_events, ajaxurl, flatpickr */
/**
 * EasyWPSMTP Debug Events functionality.
 *
 * @since 2.0.0
 */

'use strict';

var EasyWPSMTPDebugEvents = window.EasyWPSMTPDebugEvents || ( function( document, window, $ ) {

	/**
	 * Elements.
	 *
	 * @since 2.0.0
	 *
	 * @type {object}
	 */
	var el = {
		$debugEventsPage: $( '.easy-wp-smtp-tab-tools-debug-events' ),
		$dateFlatpickr: $( '.easy-wp-smtp-filter-date-selector' ),
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 2.0.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 2.0.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 2.0.0
		 */
		ready: function() {

			app.initDateRange();
			app.events();

			// Open debug event popup from the query string.
			var searchParams = new URLSearchParams( location.search );

			if ( searchParams.has( 'debug_event_id' ) ) {
				app.openDebugEventPopup( searchParams.get( 'debug_event_id' ) );
			}
		},

		/**
		 * Register JS events.
		 *
		 * @since 2.0.0
		 */
		events: function() {

			el.$debugEventsPage.on( 'click', '#easy-wp-smtp-reset-filter .reset', app.resetFilter );
			el.$debugEventsPage.on( 'click', '#easy-wp-smtp-delete-all-debug-events-button', app.deleteAllDebugEvents );
			el.$debugEventsPage.on( 'click', '.js-easy-wp-smtp-debug-event-preview', app.eventClicked );
		},

		/**
		 * Init Flatpickr at Date Range field.
		 *
		 * @since 2.0.0
		 */
		initDateRange: function() {

			var langCode = easy_wp_smtp_tools_debug_events.lang_code,
				flatpickrLocale = {
					rangeSeparator: ' - ',
				};

			if (
				flatpickr !== 'undefined' &&
				Object.prototype.hasOwnProperty.call( flatpickr, 'l10ns' ) &&
				Object.prototype.hasOwnProperty.call( flatpickr.l10ns, langCode )
			) {
				flatpickrLocale = flatpickr.l10ns[ langCode ];
				flatpickrLocale.rangeSeparator = ' - ';
			}

			el.$dateFlatpickr.flatpickr( {
				altInput  : true,
				altFormat : 'M j, Y',
				dateFormat: 'Y-m-d',
				locale    : flatpickrLocale,
				mode      : 'range'
			} );
		},

		/**
		 * Reset filter handler.
		 *
		 * @since 2.0.0
		 */
		resetFilter: function() {

			var $form = $( this ).parents( 'form' );

			$form.find( $( this ).data( 'scope' ) ).find( 'input,select' ).each( function() {

				var $this = $( this );
				if ( app.isIgnoredForResetInput( $this ) ) {
					return;
				}
				app.resetInput( $this );
			} );

			// Submit the form.
			$form.submit();
		},

		/**
		 * Reset input.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} $input Input element.
		 */
		resetInput: function( $input ) {

			switch ( $input.prop( 'tagName' ).toLowerCase() ) {
				case 'input':
					$input.val( '' );
					break;
				case 'select':
					$input.val( $input.find( 'option' ).first().val() );
					break;
			}
		},

		/**
		 * Input is ignored for reset.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} $input Input element.
		 *
		 * @returns {boolean} Is ignored.
		 */
		isIgnoredForResetInput: function( $input ) {

			return [ 'submit', 'hidden' ].indexOf( ($input.attr( 'type' ) || '').toLowerCase() ) !== -1 &&
				! $input.hasClass( 'flatpickr-input' );
		},

		/**
		 * Process the click on the delete all debug events button.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} event jQuery event.
		 */
		deleteAllDebugEvents: function( event ) {

			event.preventDefault();

			var $btn = $( event.target );

			$.confirm( {
				backgroundDismiss: false,
				escapeKey: true,
				animationBounce: 1,
				closeIcon: true,
				type: 'orange',
				icon: EasyWPSMTP.Admin.Settings.getModalIcon( 'exclamation-triangle-orange' ),
				title: easy_wp_smtp_tools_debug_events.texts.notice_title,
				content: easy_wp_smtp_tools_debug_events.texts.delete_all_notice,
				buttons: {
					confirm: {
						text: easy_wp_smtp_tools_debug_events.texts.yes,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action: function() {
							app.executeAllDebugEventsDeletion( $btn );
						}
					},
					cancel: {
						text: easy_wp_smtp_tools_debug_events.texts.cancel,
						btnClass: 'btn-cancel',
					}
				}
			} );
		},

		/**
		 * Process the click on the event item.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} event jQuery event.
		 */
		eventClicked: function( event ) {

			event.preventDefault();

			app.openDebugEventPopup( $( this ).data( 'event-id' ) );
		},

		/**
		 * Open debug event popup.
		 *
		 * @since 2.0.0
		 *
		 * @param {int} eventId Debug event ID.
		 */
		openDebugEventPopup: function( eventId ) {

			var data = {
				action: 'easy_wp_smtp_debug_event_preview',
				id: eventId,
				nonce: $( '#easy-wp-smtp-debug-events-nonce', el.$debugEventsPage ).val()
			};

			var popup = $.alert( {
				backgroundDismiss: true,
				escapeKey: true,
				animationBounce: 1,
				type: 'blue',
				icon: '',
				title: false,
				content: easy_wp_smtp_tools_debug_events.loader,
				boxWidth: '550px',
				buttons: {
					confirm: {
						text: easy_wp_smtp_tools_debug_events.texts.close,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ]
					}
				},
				onOpenBefore: function() {
					this.$contentPane.addClass( 'no-scroll' );
				}
			} );

			$.post( ajaxurl, data, function( response ) {
				if ( response.success ) {
					popup.setTitle( response.data.title );
					popup.setContent( response.data.content );
				} else {
					popup.setContent( response.data );
				}
			} ).fail( function() {
				popup.setContent( easy_wp_smtp_tools_debug_events.texts.error_occurred );
			} );
		},

		/**
		 * AJAX call for deleting all debug events.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} $btn jQuery object of the clicked button.
		 */
		executeAllDebugEventsDeletion: function( $btn ) {

			$btn.prop( 'disabled', true );

			var data = {
				action: 'easy_wp_smtp_delete_all_debug_events',
				nonce: $( '#easy-wp-smtp-debug-events-nonce', el.$debugEventsPage ).val()
			};

			$.post( ajaxurl, data, function( response ) {
				var message = response.data,
					icon,
					type,
					callback;

				if ( response.success ) {
					icon = 'check-circle-green';
					type = 'green';
					callback = function() {
						location.reload();
						return false;
					};
				} else {
					icon = 'times-circle-red';
					type = 'red';
				}

				EasyWPSMTP.Admin.Settings.displayAlertModal( message, icon, type, callback );
				$btn.prop( 'disabled', false );
			} ).fail( function() {
				EasyWPSMTP.Admin.Settings.displayAlertModal( easy_wp_smtp_tools_debug_events.texts.error_occurred, 'times-circle-red', 'red' );
				$btn.prop( 'disabled', false );
			} );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
EasyWPSMTPDebugEvents.init();
