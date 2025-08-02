import React, {Fragment, useState, useEffect} from 'react';
import NumberFormat from 'react-number-format';

const NumberField = ({
	label = null,
	value,
	onChange = () => {},
	size="xs",
	customClass,
	disabled=false,
	placeholder="",
	prefix = "",
	customWrapperClass
}) => {
	return (
		<div className={customWrapperClass}>
			{
				label && <label class={`font-semibold text-${size} text-gray-600 pb-1 block`}>{label}</label>
			}
			<NumberFormat
				thousandSeparator={"."}
				decimalSeparator={","}
				prefix={prefix}
				value={value}
				defaultValue={0}
				onValueChange={(value) => onChange(value.floatValue)}
				className={
					`border rounded-lg px-3 py-2 mb-5 text-${size} \
					placeholder-gray-700 \
					w-full ${customClass}`
				}
				disabled={disabled}
				onFocus={e => {e.target.select();}}
			/>
			{/* <input
				value={displayValue}
				type="text"
				className={`
					border rounded-lg px-3 py-2 mt-1 mb-5 text-${size} \
					placeholder-gray-700 \
					w-full ${customClass}`
				}
				onChange={(e) => setRealValue(e.currentTarget.value.replace(/[^0-9-,]/g, ''))}
				disabled={disabled}
				placeholder={placeholder}
			/> */}
		</div>
	);
}
export default NumberField;