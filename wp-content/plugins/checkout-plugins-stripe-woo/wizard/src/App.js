import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter } from 'react-router-dom';

/* Main Compnent */
import '@Admin/App.scss';
import Settings from '@Admin/Settings';

ReactDOM.render(
	<BrowserRouter>
		<Settings />
	</BrowserRouter>,
	document.getElementById( 'cpsw-onboarding-content' ),
);
