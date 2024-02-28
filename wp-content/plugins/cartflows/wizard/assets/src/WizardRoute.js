import React from 'react';
import { useLocation } from 'react-router-dom';

import {
	WelcomeStep,
	PageBuilderStep,
	PluginsInstallStep,
	OptinStep,
	GlobalCheckout,
	ReadyStep,
} from '@WizardSteps';
import { NavigationBar, FooterNavigationBar } from '@WizardFields';

function WizardRoute( props ) {
	const { setTemplatePreview } = props;
	const query = new URLSearchParams( useLocation().search );
	const action = query.get( 'step' );
	const templatePreview = query.get( 'preview' );
	let previous_step = 'dashboard',
		next_step = '',
		step_sequence = '';
	const maxSteps = 6;

	const get_route_page = function () {
		let route_page = '';

		switch ( action ) {
			case 'welcome':
				route_page = <WelcomeStep />;
				previous_step = 'dashboard';
				next_step = 'page-builder';
				step_sequence = 0;
				break;
			case 'page-builder':
				route_page = <PageBuilderStep />;
				previous_step = 'welcome';
				next_step = 'plugin-install';
				step_sequence = 1;
				break;
			case 'plugin-install':
				route_page = <PluginsInstallStep />;
				previous_step = 'page-builder';
				next_step = 'store-checkout';
				step_sequence = 2;
				break;
			case 'store-checkout':
				route_page = <GlobalCheckout />;
				previous_step = 'plugin-install';
				next_step = 'optin';
				step_sequence = 3;
				setTemplatePreview( templatePreview ? true : false );
				break;
			case 'optin':
				setTemplatePreview( false );
				route_page = <OptinStep />;
				previous_step = 'store-checkout';
				next_step = 'ready';
				step_sequence = 4;
				break;
			case 'ready':
				route_page = <ReadyStep />;
				previous_step = 'optin';
				step_sequence = 5;
				break;
			default:
				route_page = <WelcomeStep />;
				next_step = 'page-builder';
				step_sequence = 0;
				break;
		}

		return route_page;
	};

	return (
		<>
			<NavigationBar />
			<main className="wcf-setup-wizard-content py-24 relative bg-white">
				{ get_route_page() }
			</main>
			<FooterNavigationBar
				previousStep={ previous_step }
				nextStep={ next_step }
				currentStep={ step_sequence }
				maxSteps={ maxSteps }
			/>
		</>
	);
}

export default WizardRoute;
