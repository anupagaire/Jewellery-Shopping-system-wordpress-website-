import React, { useState } from 'react';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';

/* Component */
import WizardRoute from './WizardRoute';

function WizardMain() {
	const [ templatePreview, setTemplatePreview ] = useState( false );
	return (
		<Router>
			<div
				className={ `wizard-route bg-white h-screen ${
					templatePreview ? 'overflow-hidden' : ''
				}` }
			>
				<Switch>
					<Route path="/">
						<WizardRoute
							setTemplatePreview={ setTemplatePreview }
						/>
					</Route>
				</Switch>
			</div>
		</Router>
	);
}

export default WizardMain;
