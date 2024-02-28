import React from 'react';
import ReactDOM from 'react-dom';
import './WizardApp.scss';
import './tailwind-styles.scss';

/* State Provider */
import { StateProvider } from '@Utils/StateProvider';

// /* Settings Provider */
import { SettingsProvider } from '@Utils/SettingsProvider';
import settingsEvents, {
	settingsInitialState,
} from '@Utils/SettingsProvider/initialData';

import WizardMain from './WizardMain';

window.addEventListener( 'DOMContentLoaded', function () {
	ReactDOM.render(
		<React.StrictMode>
			<StateProvider
				initialState={ settingsInitialState }
				reducer={ settingsEvents }
			>
				<SettingsProvider
					initialState={ settingsInitialState }
					reducer={ settingsEvents }
				>
					<WizardMain />
				</SettingsProvider>
			</StateProvider>
		</React.StrictMode>,
		document.getElementById( 'wcf-setup-wizard-page' )
	);
} );
