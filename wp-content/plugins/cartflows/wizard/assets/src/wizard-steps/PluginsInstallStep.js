import React, { useState, useEffect, useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import { useHistory } from 'react-router-dom';
import { useStateValue } from '../utils/StateProvider';

function PluginsInstallStep() {
	const [ processing, setProcessing ] = useState( {
		isProcessing: false,
		buttonText: __( 'Install & Activate', 'cartflows' ),
	} );

	const { buttonText } = processing;

	const [ { action_button }, dispatch ] = useStateValue();
	const history = useHistory();

	const required_plugins = cartflows_wizard.plugins;
	let installed_plugins_count = 0;

	/**
	 * Dispatcher function to change the button text on wizard footer.
	 */
	const dispatchChangeButtonText = useCallback( ( data ) => {
		dispatch( {
			status: 'SET_NEXT_STEP',
			action_button: data,
		} );
	}, [] );

	/**
	 * Function used to change the footer button text and the primary button text while processing the request.
	 */
	const handleOnClickProcessing = function () {
		const processing_buttonText = __(
			'Installing required plugins',
			'cartflows'
		);

		setProcessing( {
			isProcessing: true,
			buttonText: processing_buttonText,
		} );

		dispatchChangeButtonText( {
			button_text: processing_buttonText,
			button_class: 'is-loading',
		} );

		dispatch( {
			status: 'PROCESSING',
		} );
	};

	useEffect( () => {
		dispatchChangeButtonText( {
			button_text: __( 'Install & Activate', 'cartflows' ),
			button_class: '',
		} );

		const installPluginsSuccess = document.addEventListener(
			'wcf-plugins-install-success',
			function () {
				setProcessing( false );
				history.push( {
					pathname: 'index.php',
					search: `?page=cartflow-setup&step=store-checkout`,
				} );

				dispatch( {
					status: 'RESET',
				} );
			},
			false
		);

		const installPluginsProcess = document.addEventListener(
			'wcf-install-require-plugins-processing',
			function () {
				handleOnClickProcessing();
			},
			false
		);

		return () => {
			document.removeEventListener(
				'wcf-plugins-install-success',
				installPluginsSuccess
			);
			document.removeEventListener(
				'wcf-install-require-plugins-processing',
				installPluginsProcess
			);
		};
	}, [ dispatchChangeButtonText ] );

	const handleRedirection = function ( e ) {
		e.preventDefault();

		setProcessing( {
			isProcessing: true,
			buttonText: __( 'Continuing…', 'cartflows' ),
		} );

		history.push( {
			pathname: 'index.php',
			search: `?page=cartflow-setup&step=global-checkout`,
		} );

		setProcessing( false );
	};

	return (
		<div className="wcf-container wcf-wizard--plugin-install">
			<div className="wcf-row mt-12">
				<div className="bg-white rounded mx-auto px-11 text-center">
					<span className="text-sm font-medium text-primary-600 mb-10 text-center block tracking-[.24em] uppercase">
						{ __( 'Step 3 of 6', 'cartflows' ) }
					</span>
					<h1 className="wcf-step-heading mb-4">
						<span className="flex items-center justify-center gap-3">
							{ __( 'Great job!', 'cartflows' ) }
							<svg
								xmlns="http://www.w3.org/2000/svg"
								className="h-8 w-8 align-middle text-2xl mr-1.5 fill-[#ffc83d]"
								viewBox="0 0 20 20"
								fill="currentColor"
							>
								<path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
							</svg>
						</span>
						{ __(
							"Now let's install some required plugins.",
							'cartflows'
						) }
					</h1>
					<p className="text-center overflow-hidden max-w-2xl mb-10 mx-auto text-lg font-normal text-slate-500 block">
						{ __(
							'Since CartFlows uses WooCommerce, we will install it for you with Cart Abandonment Recovery',
							'cartflows'
						) }
						<br />
						{ __(
							'to recover the abandoned orders.',
							'cartflows'
						) }
					</p>
					<p className="text-center overflow-hidden max-w-2xl mb-10 mx-auto text-lg font-normal text-slate-500 block">
						{ __(
							'The following plugin will be installed and activated for you:',
							'cartflows'
						) }
					</p>

					<div className="flex justify-center w-11/12 text-left text-base text-[#1F2937] mt-8 mx-auto">
						<fieldset className="">
							<form
								method="post"
								className="wcf-install-plugin-form grid grid-cols-2 space-y-1 text-gray-500 list-inside dark:text-gray-400"
							>
								{ Object.keys( required_plugins ).map(
									( plugin ) => {
										const plugin_name = plugin.replace(
												/-/g,
												' '
											),
											plugin_stat =
												required_plugins[ plugin ];

										if ( 'active' === plugin_stat ) {
											installed_plugins_count++;
										}

										return (
											<div
												className="relative flex items-start"
												key={ plugin }
											>
												<div className="!m-0 flex items-center gap-3 text-sm leading-6">
													<input
														id={ plugin }
														aria-describedby={
															plugin
														}
														name="required_plugins[]"
														type="checkbox"
														data-status={
															plugin_stat
														}
														data-slug={ plugin }
														className="!m-0 !h-5 !w-5 !rounded !border-gray-300 !text-[#f06434] focus:!ring-offset-2 focus:!ring-2 focus:!ring-[#f06434] checked:bg-[#f06434]"
														defaultChecked={
															'active' ===
															plugin_stat
														}
														disabled={
															'active' ===
															plugin_stat
														}
													/>

													<label
														htmlFor={ plugin }
														className="font-medium text-gray-500 capitalize"
													>
														{ plugin_name }{ ' ' }
														<span className="capitalize text-xs italic">
															({ plugin_stat })
														</span>
													</label>
												</div>
											</div>
										);
									}
								) }
							</form>
						</fieldset>
					</div>

					<div className="wcf-action-buttons mt-[40px] flex justify-center">
						{ Object.keys( required_plugins ).length ===
						installed_plugins_count ? (
							<button
								className={ `installed-required-plugins wcf-wizard--button ${
									action_button.button_class
										? action_button.button_class
										: ''
								}` }
								onClick={ handleRedirection }
							>
								{ __( 'Continue', 'cartflows' ) }
								<svg
									xmlns="http://www.w3.org/2000/svg"
									className="w-5 mt-0.5 ml-1.5 fill-[#243c5a]"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
									strokeWidth={ 2 }
								>
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										d="M17 8l4 4m0 0l-4 4m4-4H3"
									/>
								</svg>
							</button>
						) : (
							<button
								className={ `install-required-plugins wcf-wizard--button ${
									action_button.button_class
										? action_button.button_class
										: ''
								}` }
							>
								{ buttonText }
							</button>
						) }
					</div>
				</div>
			</div>
		</div>
	);
}

export default PluginsInstallStep;
