export function getExitSetupWizard() {
	return cartflows_wizard.admin_url + '?page=cartflows&path=settings';
}

export function sendPostMessage( data ) {
	const frame = document.getElementById( 'cartflows-templates-preview' );

	frame.contentWindow.postMessage(
		{
			action: 'ScDispatchTemplatePreviewActions/' + data.action,
			value: data,
		},
		'*'
	);
}
