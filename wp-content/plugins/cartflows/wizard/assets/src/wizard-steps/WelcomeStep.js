import React, { useEffect, useCallback } from 'react';
import { useHistory } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { useStateValue } from '../utils/StateProvider';
// import CartFlowsLogo from '@WizardImages/cartflows-logo.svg';
import { ArrowRightIcon } from '@heroicons/react/24/outline';

function WelcomeStep() {
	const history = useHistory();
	const [ { action_button }, dispatch ] = useStateValue();

	const redirectNextStep = useCallback( () => {
		history.push( {
			pathname: 'index.php',
			search: `?page=cartflow-setup&step=page-builder`,
		} );
	}, [] );

	useEffect( () => {
		dispatch( {
			status: 'SET_NEXT_STEP',
			action_button: {
				button_text: __( "Let's start", 'cartflows' ),
				button_class: 'wcf-start-setup',
			},
		} );

		const startOnboardingEvent = document.addEventListener(
			'wcf-redirect-page-builder-step',
			function () {
				redirectNextStep();
			},
			false
		);

		return () => {
			document.removeEventListener(
				'wcf-redirect-page-builder-step',
				startOnboardingEvent
			);
		};
	}, [ redirectNextStep ] );

	return (
		<div className="wcf-container">
			<div className="wcf-row mt-16">
				<div className="bg-white rounded mx-auto px-11">
					<span className="text-sm font-medium text-primary-600 mb-10 text-center block tracking-[.24em] uppercase">
						{ __( 'Step 1 of 6', 'cartflows' ) }
					</span>
					<h1 className="wcf-step-heading mb-4">
						{ __( 'Welcome to CartFlows', 'cartflows' ) }
					</h1>

					<p className="text-center overflow-hidden max-w-2xl mb-10 mx-auto text-lg font-normal text-slate-500">
						{ __(
							"You're only minutes away from having a more profitable WooCommerce store! This short setup wizard will help you get started with CartFlows.",
							'cartflows'
						) }
					</p>

					<div className="flex justify-center">
						<div
							className={ `wcf-wizard--button ${
								action_button.button_class
									? action_button.button_class
									: ''
							}` }
						>
							{ action_button.button_text }
							<ArrowRightIcon
								className="w-5 mt-0.5 ml-1.5 stroke-2"
								aria-hidden="true"
							/>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}

export default WelcomeStep;
