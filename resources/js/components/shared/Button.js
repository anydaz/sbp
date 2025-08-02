import React from "react";
import classnames from "classnames";
import * as Icons from "lucide-react";

export const Button = ({
    text,
    onClick,
    fullWidth = false,
    customClass,
    textClass,
    disabled = false,
}) => {
    const defaultClass = `px-2 transition duration-200 ${
        disabled ? "bg-gray-400" : "hover:bg-blue-600 bg-blue-700"
    } \
         text-white h-[34px] \
		rounded text-sm font-semibold text-center inline-block`;

    const additionalClass = { "w-full": fullWidth };
    return (
        <button
            type="button"
            className={classnames(defaultClass, additionalClass, customClass)}
            onClick={onClick}
            disabled={disabled}
        >
            <span className={classnames("inline-block", textClass)}>
                {text}
            </span>
        </button>
    );
};

export const SecondaryButton = ({
    text,
    icon,
    onClick,
    fullWidth = false,
    customClass,
    textClass,
}) => {
    const defaultClass =
        "px-2 transition duration-200 bg-white inline-flex items-center \
		hover:bg-gray-50 text-black border h-[34px] \
		rounded text-sm font-semibold text-center inline-block";

    const additionalClass = { "w-full": fullWidth };

    // Get the icon component from Lucide React
    const IconComponent = icon ? Icons[icon] : null;

    return (
        <button
            type="button"
            className={classnames(defaultClass, additionalClass, customClass)}
            onClick={onClick}
        >
            {IconComponent && <IconComponent className="absolute w-4 h-4" />}
            <span
                className={classnames(
                    "inline-block",
                    icon ? "ml-8" : null,
                    textClass
                )}
            >
                {text}
            </span>
        </button>
    );
};

export const IconButton = ({ icon, onClick, customClass, iconClass }) => {
    const defaultClass =
        "w-8 h-8 flex justify-center items-center transition duration-200 \
		border \
		rounded inline-block";

    // Get the icon component from Lucide React
    const IconComponent = Icons[icon];

    return (
        <button
            type="button"
            className={classnames(defaultClass, customClass)}
            onClick={onClick}
        >
            {IconComponent ? (
                <IconComponent className={classnames("w-4 h-4", iconClass)} />
            ) : (
                <Icons.HelpCircle
                    className={classnames("w-4 h-4", iconClass)}
                />
            )}
        </button>
    );
};

export default Button;
