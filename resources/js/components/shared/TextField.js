import React, { Fragment } from "react";

const TextField = ({
    label = null,
    onChange,
    value,
    defaultValue = "",
    type = "text",
    size = "xs",
    customClass,
    disabled = false,
    placeholder = "",
    propsRef,
    noMargin = false,
    customWrapperClass,
    onKeyDown = null,
}) => {
    return (
        <div className={customWrapperClass ?? "w-full"}>
            {label && (
                <label
                    class={`font-semibold text-${size} text-gray-600 pb-1 block`}
                >
                    {label}
                </label>
            )}
            <input
                value={value}
                defaultValue={!value ? defaultValue : undefined}
                type={type}
                className={`
					border rounded-lg px-3 py-2 text-${size} \
					placeholder-gray-700 \
					w-full ${customClass || ""} \
					${noMargin ? "m-0" : "mb-5"}`}
                onChange={onChange}
                disabled={disabled}
                placeholder={placeholder}
                ref={propsRef}
                onKeyDown={onKeyDown}
            />
        </div>
    );
};
export default TextField;
