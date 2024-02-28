import React from 'react';

function InputField( props ) {
	const { attr } = props;

	const [ inputvalue, setInputvalue ] = React.useState( props.value );

	function handleChange( e ) {
		setInputvalue( e.target.value );
	}

	const type = props.type ? props.type : 'text';
	return (
		<>
			<div className="mb-3 xl:w-96">
				<label
					htmlFor="exampleFormControlInput1"
					className="form-label inline-block mb-2 text-gray-700"
				>
					{ props.label }
				</label>

				<input
					{ ...attr }
					type={ type }
					className="
					form-control
					block
					w-full
					px-3
					py-1.5
					text-base
					font-normal
					text-gray-700
					bg-white bg-clip-padding
					border border-solid border-gray-300
					rounded
					transition
					ease-in-out
					m-0
					focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
				"
					name={ props.name }
					value={ inputvalue }
					id={ props.id }
					onChange={ handleChange }
					placeholder={ props.placeholder }
					min={ props.min }
					max={ props.max }
					readOnly={ props.readonly }
				></input>
			</div>
		</>
	);
}

export default InputField;
