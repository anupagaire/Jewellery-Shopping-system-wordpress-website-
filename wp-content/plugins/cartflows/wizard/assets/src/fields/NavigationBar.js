import React, { Fragment } from 'react';
import { __ } from '@wordpress/i18n';
import CfIcon from '@WizardImages/cartflows-icon.svg';
import { getExitSetupWizard } from '@Utils/Helpers';
import apiFetch from '@wordpress/api-fetch';
import { useLocation, useHistory } from 'react-router-dom';

import { Disclosure } from '@headlessui/react';
import { XMarkIcon } from '@heroicons/react/24/outline';

function Index() {
	const search = useLocation().search;
	const history = useHistory();
	let step = new URLSearchParams( search ).get( 'step' );
	step = step ? step : 'welcome';

	const menus = [
		{
			name: __( 'Welcome', 'cartflows' ),
			id: 'welcome',
		},
		{
			name: __( 'Page Builder', 'cartflows' ),
			id: 'page-builder',
		},
		{
			name: __( 'Required Plugins', 'cartflows' ),
			id: 'plugin-install',
		},
		{
			name: __( 'Store Checkout', 'cartflows' ),
			id: 'store-checkout',
		},
		{
			name: __( 'Subscribe', 'cartflows' ),
			id: 'optin',
		},
		{
			name: __( 'Done', 'cartflows' ),
			id: 'ready',
		},
	];

	const handleClick = ( e ) => {
		e.preventDefault();

		const ajaxData = new window.FormData();

		ajaxData.append( 'action', 'cartflows_onboarding_exit' );
		ajaxData.append( 'security', cartflows_wizard.onboarding_exit_nonce );
		ajaxData.append( 'current_step', step );

		apiFetch( {
			url: ajaxurl,
			method: 'POST',
			body: ajaxData,
		} ).then( ( response ) => {
			if ( response.success ) {
				window.location.href = getExitSetupWizard();
			}
		} );
	};

	const handleStepRedirection = function ( e ) {
		e.preventDefault();

		if ( e.target.id ) {
			const stepToRedirect = e.target.id;
			history.push( {
				pathname: 'index.php',
				search: `?page=cartflow-setup&step=` + stepToRedirect,
			} );
		}
	};

	return (
		<>
			<Disclosure
				as="nav"
				className="bg-white fixed top-0 w-full z-10 border-b border-slate-200"
			>
				{ ( {} ) => (
					<>
						<div className="px-4 sm:px-6 lg:px-8">
							<div className="flex h-16 justify-between">
								<div className="flex">
									<div className="flex flex-shrink-0 items-center">
										<img
											className="block lg:hidden h-8 w-auto"
											src={ CfIcon }
											alt="CartFlows"
										/>
										<img
											className="hidden lg:block h-8 w-auto"
											src={ CfIcon }
											alt="CartFlows"
										/>
									</div>
								</div>
								<div className="wcf-wizard-menu--navbar hidden sm:flex sm:space-x-8">
									{ menus.map( ( menu ) => {
										return (
											<a
												href="#"
												className={ `inline-flex items-center border-b-2 px-1 pt-1 text-base font-medium focus:outline-none focus:shadow-none ${
													step === menu.id
														? 'border-primary-500 text-gray-800'
														: 'border-transparent text-gray-300 hover:border-gray-300 hover:text-gray-700'
												}` }
												id={ menu.id }
												onClick={
													handleStepRedirection
												}
												key={ menu.id }
											>
												{ menu.name }
											</a>
										);
									} ) }
								</div>
								<div className="hidden sm:ml-6 sm:flex sm:items-center">
									<button
										type="button"
										className="rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
										onClick={ handleClick }
										title={ __(
											'Exit setup wizard',
											'cartflows'
										) }
									>
										<span className="sr-only">
											Exit Wizard
										</span>
										<XMarkIcon
											className="h-6 w-6"
											aria-hidden="true"
										/>
									</button>
								</div>
							</div>
						</div>
					</>
				) }
			</Disclosure>
		</>
	);
}
export default Index;
