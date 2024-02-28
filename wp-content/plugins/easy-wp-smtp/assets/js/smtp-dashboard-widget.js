/* global easy_wp_smtp_dashboard_widget, ajaxurl, moment, ApexCharts */
/**
 * Easy WP SMTP Dashboard Widget function.
 *
 * @since 2.1.0
 */

'use strict';

var EasyWPSMTPDashboardWidget = window.EasyWPSMTPDashboardWidget || ( function( document, window, $ ) {

	/**
	 * Elements reference.
	 *
	 * @since 2.1.0
	 *
	 * @type {object}
	 */
	var el = {
		$chart                          : $( '#easy-wp-smtp-dash-widget-chart' ),
		$dismissBtn                     : $( '.easy-wp-smtp-dash-widget-dismiss-chart-upgrade' ),
		$summaryReportEmailBlock        : $( '.easy-wp-smtp-dash-widget-summary-report-email-block' ),
		$summaryReportEmailDismissBtn   : $( '.easy-wp-smtp-dash-widget-summary-report-email-dismiss' ),
		$summaryReportEmailEnableInput  : $( '#easy-wp-smtp-dash-widget-summary-report-email-enable' ),
	};

	/**
	 * ApexCharts functions and properties.
	 *
	 * @since 2.1.0
	 *
	 * @type {object}
	 */
	var chart = {

		/**
		 * ApexCharts instance.
		 *
		 * @since 2.1.0
		 */
		instance: null,

		/**
		 * ApexCharts settings.
		 *
		 * @since 2.1.0
		 */
		settings: {
			chart: {
				type: 'area',
				toolbar: {
					show: false,
				},
				foreColor: '#50575E',
				height: 285,
			},
			stroke: {
				curve: 'smooth',
				width: 2
			},
			dataLabels: {
				enabled: false
			},
			series: [
				{ data: [] },
				{ data: [] }
			],
			colors: [ '#211FA6', '#0F8A56' ],
			xaxis: {
				type: 'datetime',
				categories: [],
				tickPlacement: 'on',
				labels: {
					style: {
						colors: '#6F6F84',
						fontSize: '13px',
					},
					format: 'dd',
					datetimeUTC: false,
				},
				axisBorder: {
					show: false,
				},
				axisTicks: {
					show: true,
					colors: '#DADADF',
					height: 9
				}
			},
			yaxis: {
				labels: {
					style: {
						colors: '#6F6F84',
						fontSize: '13px',
					},
					formatter: function( val ) {

						// Make sure the tick value has no decimals.
						if ( isNaN( val ) ) {
							return '';
						}

						return val.toFixed( 0 );
					}
				},
				axisTicks: {
					show: true,
					colors: '#DADADF',
					width: 9,
					offsetY: 0.5
				}
			},
			grid: {
				show: true,
				borderColor: '#DADADF',
				xaxis: {
					lines: {
						show: true
					}
				},
				yaxis: {
					lines: {
						show: true
					}
				},
			},
			legend: {
				show: false,
			},
		},

		/**
		 * Init ApexCharts.
		 *
		 * @since 2.1.0
		 */
		init: function() {

			if ( ! el.$chart.length ) {
				return;
			}

			chart.updateWithDummyData();

			chart.instance = new ApexCharts( el.$chart[ 0 ], chart.settings );

			chart.instance.render();
		},

		/**
		 * Update ApexCharts settings with dummy data.
		 *
		 * @since 2.1.0
		 */
		updateWithDummyData: function() {

			var end = moment().startOf( 'day' ),
				days = 7,
				data1 = [ 70, 75, 55, 45, 34, 25, 20 ],
				data2 = [ 25, 30, 25, 20, 15, 10, 5 ],
				date,
				i;

			for ( i = 1; i <= days; i++ ) {
				date = end.clone().subtract( i, 'days' );

				chart.settings.xaxis.categories.push( date );
				chart.settings.series[ 0 ].data.push( {
					x: date,
					y: data1[ i - 1 ],
				} );
				chart.settings.series[ 1 ].data.push( {
					x: date,
					y: data2[ i - 1 ],
				} );
			}
		},
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
		 * Publicly accessible ApexCharts functions and properties.
		 *
		 * @since 2.1.0
		 */
		chart: chart,

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

			el.$dismissBtn.on( 'click', function( event ) {
				event.preventDefault();

				app.saveWidgetMeta( 'hide_graph', 1 );
				$( this ).closest( '.easy-wp-smtp-dash-widget-chart-block-container' ).remove();
				$( '#easy-wp-smtp-dash-widget-upgrade-footer' ).show();
			} );

			// Hide summary report email block on dismiss icon click.
			el.$summaryReportEmailDismissBtn.on( 'click', function( event ) {
				event.preventDefault();

				app.saveWidgetMeta( 'hide_summary_report_email_block', 1 );
				el.$summaryReportEmailBlock.slideUp();
			} );

			// Enable summary report email on checkbox enable.
			el.$summaryReportEmailEnableInput.on( 'change', function( event ) {
				event.preventDefault();

				var $self = $( this ),
					$loader = $self.next( 'i' );

				$self.hide();
				$loader.show();

				var data = {
					_wpnonce: easy_wp_smtp_dashboard_widget.nonce,
					action  : 'easy_wp_smtp_' + easy_wp_smtp_dashboard_widget.slug + '_enable_summary_report_email'
				};

				$.post( ajaxurl, data )
					.done( function() {
						el.$summaryReportEmailBlock.find( '.easy-wp-smtp-dash-widget-summary-report-email-block-setting' )
							.addClass( 'hidden' );
						el.$summaryReportEmailBlock.find( '.easy-wp-smtp-dash-widget-summary-report-email-block-applied' )
							.removeClass( 'hidden' );
					} )
					.fail( function() {
						$self.show();
						$loader.hide();
					} );
			} );

			chart.init();
			app.removeOverlay( el.$chart );
		},

		/**
		 * Save dashboard widget meta in backend.
		 *
		 * @since 2.1.0
		 *
		 * @param {string} meta Meta name to save.
		 * @param {number} value Value to save.
		 */
		saveWidgetMeta: function( meta, value ) {

			var data = {
				_wpnonce: easy_wp_smtp_dashboard_widget.nonce,
				action  : 'easy_wp_smtp_' + easy_wp_smtp_dashboard_widget.slug + '_save_widget_meta',
				meta    : meta,
				value   : value,
			};

			$.post( ajaxurl, data );
		},

		/**
		 * Remove an overlay from a widget block containing $el.
		 *
		 * @since 2.1.0
		 *
		 * @param {object} $el jQuery element inside a widget block.
		 */
		removeOverlay: function( $el ) {
			$el.siblings( '.easy-wp-smtp-dash-widget-overlay' ).remove();
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
EasyWPSMTPDashboardWidget.init();
