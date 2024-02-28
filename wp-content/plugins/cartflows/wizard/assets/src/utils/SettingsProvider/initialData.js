export const settingsInitialState = {
	settingsProcess: false,
	unsavedChanges: false,
	showConfetti: false,
	preview: {},
	selected_page_builder: '',
	action_button: {
		button_text: '',
		button_action: '',
		button_class: '',
	},
	site_logo: cartflows_wizard.site_logo ? cartflows_wizard.site_logo : '',
};

const settingsEvents = ( state, data ) => {
	switch ( data.status ) {
		case 'SAVED':
			window.wcfUnsavedChanges = false;
			return {
				...state,
				settingsProcess: 'saved',
			};
		case 'PROCESSING':
			return {
				...state,
				settingsProcess: 'processing',
			};
		case 'RESET':
			return {
				...state,
				settingsProcess: false,
			};
		case 'UNSAVED_CHANGES':
			if ( 'change' === data.trigger ) {
				return {
					...state,
					unsavedChanges: true,
				};
			}
			return {
				...state,
				unsavedChanges: false,
			};
		case 'SET_SHOW_CONFETTI':
			return {
				...state,
				showConfetti: data.showConfetti,
			};
		case 'SET_NEXT_STEP':
			return {
				...state,
				action_button: data.action_button,
			};
		case 'SET_WIZARD_PAGE_BUILDER':
			return {
				...state,
				selected_page_builder: data.selected_page_builder,
			};
		case 'SET_SITE_LOGO':
			return {
				...state,
				site_logo: data.site_logo,
			};
		default:
			return state;
	}
};

export default settingsEvents;
