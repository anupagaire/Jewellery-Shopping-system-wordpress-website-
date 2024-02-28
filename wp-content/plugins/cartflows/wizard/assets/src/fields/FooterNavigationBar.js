import React from 'react';
import { __ } from '@wordpress/i18n';
import { useStateValue } from '../utils/StateProvider';
import { useHistory, useLocation } from 'react-router-dom';
import { getExitSetupWizard } from '@Utils/Helpers';
import apiFetch from '@wordpress/api-fetch';

function FooterNavigationBar( props ) {
	const { previousStep, nextStep, currentStep, maxSteps } = props;
	const paginationClass =
		'relative z-10 inline-flex items-center rounded-full p-1 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500';

	const [ { settingsProcess } ] = useStateValue();

	const query = new URLSearchParams( useLocation().search );
	const currentActiveStep = query.get( 'step' );

	const history = useHistory();

	const handlePreviousStep = function () {
		// if ( 'dashboard' === previousStep ) {
		// 	window.location = cartflows_wizard.admin_base_url;
		// 	return;
		// }
		if ( 'dashboard' !== previousStep ) {
			history.push( {
				pathname: 'index.php',
				search: `?page=cartflow-setup&step=` + previousStep,
			} );
		}

		return '';
	};

	const handleNextStep = function ( e ) {
		e.preventDefault();

		if ( '' !== nextStep && 'processing' !== settingsProcess ) {
			history.push( {
				pathname: 'index.php',
				search: `?page=cartflow-setup&step=` + nextStep,
			} );
		}

		if ( '' === nextStep && 'ready' === currentActiveStep ) {
			e.target.innerText = __( 'Redirecting..', 'cartflows' );

			const ajaxData = new window.FormData();

			ajaxData.append( 'action', 'cartflows_onboarding_exit' );
			ajaxData.append(
				'security',
				cartflows_wizard.onboarding_exit_nonce
			);
			ajaxData.append( 'current_step', currentActiveStep );

			apiFetch( {
				url: ajaxurl,
				method: 'POST',
				body: ajaxData,
			} ).then( ( response ) => {
				if ( response.success ) {
					window.location.href = getExitSetupWizard();
				}
			} );
		}
	};

	return (
		<>
			<footer className="wcf-setup-footer bg-white shadow-md-1 fixed inset-x-0 bottom-0 h-[70px] z-10">
				<div className="flex items-center justify-between max-w-md mx-auto px-7 py-4 h-full">
					<div className="wcf-footer-left-section flex">
						<div
							className={ `flex-shrink-0 flex text-sm font-normal hover:text-orange-500 cursor-pointer ${
								'dashboard' === previousStep
									? 'text-slate-300 pointer-events-none'
									: 'text-neutral-500'
							}` }
							onClick={ handlePreviousStep }
						>
							<button type="button">
								{ __( 'Back', 'cartflows' ) }
							</button>
						</div>
					</div>

					<div className="wcf-footer--pagination hidden md:-mt-px md:flex gap-3">
						{ Array( maxSteps )
							.fill()
							.map( ( i, index ) => {
								return (
									<span
										key={ index }
										className={ `wcf-footer-pagination--tab ${ paginationClass } ${
											currentStep === index
												? 'bg-primary-500'
												: 'bg-primary-100'
										}` }
									></span>
								);
							} ) }
					</div>

					<div className="wcf-footer-right-section flex">
						<button
							onClick={ handleNextStep }
							className={ `flex-shrink-0 flex text-sm text-neutral-500 font-normal hover:text-orange-500 cursor-pointer ${
								'processing' === settingsProcess
									? 'disabled pointer-events-none text-neutral-300'
									: ''
							}` }
						>
							{ '' !== nextStep && 'ready' !== currentActiveStep
								? __( 'Next', 'cartflows' )
								: __( 'Finish Store Setup', 'cartflows' ) }
						</button>
					</div>
				</div>
			</footer>
		</>
	);
}
export default FooterNavigationBar;
