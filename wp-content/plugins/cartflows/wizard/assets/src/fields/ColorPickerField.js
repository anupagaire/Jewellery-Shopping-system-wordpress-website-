import React, { useState } from 'react';
import reactCSS from 'reactcss';
import './ColorPickerField.scss';
import { __ } from '@wordpress/i18n';
import { SwatchIcon, ArrowPathIcon } from '@heroicons/react/24/outline';

import { SketchPicker } from 'react-color';

function ColorPickerField( props ) {
	const {
		name,
		label,
		value,
		isActive = true,
		handleOnchange,
		displayAs = 'selector',
	} = props;

	const [ displayColorPicker, setdisplayColorPicker ] = useState( false );
	const [ color, setColor ] = useState( value );

	const styles = reactCSS( {
		default: {
			color: {
				width: '36px',
				height: '30px',
				background: color,
			},
		},
	} );

	const handleClick = () => {
		setdisplayColorPicker( ( prevValue ) => ! prevValue );
	};
	const handleClose = () => {
		setdisplayColorPicker( false );
	};
	const handleResetColor = () => {
		handleChange( '' );
	};

	const handleChange = ( newcolor ) => {
		if ( newcolor ) {
			setColor( newcolor.hex );
		} else {
			setColor( newcolor );
		}

		// Trigger change
		const changeEvent = new CustomEvent( 'wcf:color:change', {
			bubbles: true,
			detail: {
				e: 'color',
				name: props.name,
				value: newcolor ? newcolor.hex : newcolor,
			},
		} );

		document.dispatchEvent( changeEvent );

		if ( handleOnchange ) {
			handleOnchange( newcolor );
		}
	};
	return (
		<div
			className={ `wcf-field wcf-color-field ${
				! isActive ? 'wcf-hide' : ''
			}` }
		>
			<div className="wcf-field__data">
				{ label && 'selector' === displayAs && (
					<div className="wcf-field__data--label">
						<label>{ label }</label>
					</div>
				) }
				<div
					className={ `wcf-field__data--content ${
						'button' === displayAs ? '!w-full' : ''
					}` }
				>
					<div className="wcf-colorpicker-selector">
						{ 'selector' === displayAs ? (
							<>
								<div
									className="wcf-colorpicker-swatch-wrap"
									onClick={ handleClick }
								>
									<span
										className="wcf-colorpicker-swatch"
										style={ styles.color }
									/>
									<span className="wcf-colorpicker-label">
										{ __( 'Select Color', 'cartflows' ) }
									</span>
									<input
										type="hidden"
										name={ name }
										value={ color }
									/>
								</div>
								{ color && (
									<span
										className="wcf-colorpicker-reset"
										onClick={ handleResetColor }
										title={ __( 'Reset', 'cartflows' ) }
									>
										<span className="dashicons dashicons-update-alt"></span>
									</span>
								) }
							</>
						) : (
							<button
								type="button"
								className="inline-flex relative justify-center items-center gap-1.5 rounded px-4 py-2.5 text-sm font-normal leading-4 shadow-sm cursor-pointer bg-primary-25 border border-primary-300 text-primary-500 focus:bg-primary-50 focus:text-primary-600 focus:ring-offset-2 focus:ring-2 focus:ring-primary-500 focus:outline-none"
								onClick={ handleClick }
							>
								<SwatchIcon
									className="w-5 h-5 stroke-2"
									aria-hidden="true"
								/>
								{ label && <label>{ label }</label> }
								{ color && (
									<span
										className={ `wcf-colorpicker-reset absolute !p-0 -left-2 -top-1.5 bg-white rounded-full text-gray-600` }
										onClick={ handleResetColor }
										title={ __( 'Reset', 'cartflows' ) }
									>
										<ArrowPathIcon
											className="w-4 h-5 stroke-2"
											aria-hidden="true"
										/>
									</span>
								) }
							</button>
						) }
					</div>
					<div className="wcf-color-picker">
						{ displayColorPicker ? (
							<div className="wcf-color-picker-popover">
								<div
									className="wcf-color-picker-cover"
									onClick={ handleClose }
								/>
								<SketchPicker
									name={ name }
									color={ color }
									onChange={ handleChange }
									disableAlpha={ true }
								/>
							</div>
						) : null }
					</div>
				</div>
			</div>
		</div>
	);
}

export default ColorPickerField;
