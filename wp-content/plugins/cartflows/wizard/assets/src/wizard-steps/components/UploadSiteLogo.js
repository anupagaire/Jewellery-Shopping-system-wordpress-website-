import React, { useState, useEffect } from 'react';
import { addFilter } from '@wordpress/hooks';
import { sendPostMessage } from '@Utils/Helpers';
import { PhotoIcon, XMarkIcon } from '@heroicons/react/24/outline';

import { __ } from '@wordpress/i18n';
import { useStateValue } from '../../utils/StateProvider';
import { MediaUpload } from '@wordpress/media-utils';

function UploadSiteLogo( props ) {
	const { defaultPageBuilder } = props;
	const replaceMediaUpload = () => MediaUpload;
	const [ { site_logo }, dispatch ] = useStateValue();

	addFilter(
		'editor.MediaUpload',
		'core/edit-post/components/media-upload/replace-media-upload',
		replaceMediaUpload
	);

	const [ imgUpdated, setImageUpdated ] = useState( false );

	useEffect( () => {
		let temp_site_logo = site_logo;

		if ( '' === temp_site_logo && '' !== cartflows_wizard.site_logo.url ) {
			temp_site_logo = cartflows_wizard.site_logo;
		}

		updateValues( temp_site_logo );
	}, [] );

	/**
	 * Prepare the selected image array and update it in the preview iframe.
	 *
	 * @param {media} media
	 */
	const onSelectImage = ( media ) => {
		const mediaData = {
			id: media.id,
			url: media.url,
			width: site_logo.width,
		};

		updateValues( mediaData );
	};

	/**
	 * Remove the selected image from the iframe preview.
	 */
	const removeImage = () => {
		updateValues( '' );
	};

	/**
	 * Update the selected image value in the state and on the iframe preview.
	 *
	 * @param {data} data
	 */
	const updateValues = ( data ) => {
		dispatch( {
			status: 'SET_SITE_LOGO',
			site_logo: data,
		} );

		// Change the preview.
		changelogoInPreview( data );
		setImageUpdated( ! imgUpdated );
	};

	/**
	 * Send the data to the iframe preview window using windows messaging feature.
	 *
	 * @param {data} data
	 */
	const changelogoInPreview = ( data ) => {
		if ( '' === data ) {
			sendPostMessage( {
				action: 'clearHeaderLogo',
				data: {
					default_builder: defaultPageBuilder,
					site_logo: [],
				},
			} );
		} else {
			sendPostMessage( {
				action: 'changeHeaderLogo',
				data: {
					default_builder: defaultPageBuilder,
					site_logo: data,
				},
			} );
		}
	};

	const logo_btn_text =
		'' === site_logo || undefined === site_logo.url
			? __( 'Upload a Logo', 'cartflows' )
			: __( 'Change a Logo', 'cartflows' );
	return (
		<>
			<div className="wcf-options--row">
				<MediaUpload
					onSelect={ ( media ) => onSelectImage( media ) }
					allowedTypes={ [ 'image' ] }
					value={ site_logo.id }
					// multiple={ false }
					render={ ( { open } ) => (
						<>
							<div className="wcf-media-upload-wrapper flex gap-4">
								{ '' !== site_logo.url &&
								undefined !== site_logo.url ? (
									<div className="wcf-site-logo-wrapper">
										<div className="wcf-media-upload--selected-image">
											<div
												className="wcf-media-upload--preview relative wcf-inline-tooltip"
												data-tooltip={ __(
													'Remove logo',
													'cartflows'
												) }
											>
												<span
													className="wcf-close-site-logo absolute p-0.5 -left-2.5 bg-white rounded-full border border-gray-300 -top-1.5 cursor-pointer"
													onClick={ removeImage }
												>
													<XMarkIcon
														className="w-2.5 h-2.5 stroke-2 text-gray-600 hover:text-gray-800 "
														aria-hidden="true"
													/>
												</span>
												<img
													src={ site_logo.url }
													alt={
														'wcf-selected-logo-preview'
													}
													className="wcf-selected-image w-11 h-11"
													data-logo-data={ JSON.stringify(
														site_logo
													) }
												/>
											</div>
										</div>
									</div>
								) : (
									''
								) }

								<button
									className="wcf-media-upload-button relative inline-flex justify-center items-center gap-1.5 rounded px-4 py-2.5 text-sm font-normal leading-4 shadow-sm cursor-pointer bg-primary-25 border border-primary-300 text-primary-500 focus:bg-primary-50 focus:text-primary-600 focus:ring-offset-2 focus:ring-2 focus:ring-primary-500 focus:outline-none wcf-inline-tooltip"
									onClick={ open }
									data-tooltip={ __(
										'Suggested Dimensions: 180x60 pixels',
										'cartflows'
									) }
								>
									<PhotoIcon
										className="w-5 h-5 stroke-2"
										aria-hidden="true"
									/>
									{ logo_btn_text }
								</button>
							</div>
						</>
					) }
				/>
			</div>
		</>
	);
}

export default UploadSiteLogo;
