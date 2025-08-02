import React from "react";
import Button, { SecondaryButton } from "./Button.js";
import { useHistory } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

const Header = ({
    title,
    action,
    secondaryAction,
    thirdAction,
    withBackButton,
    backAction,
    customActionsComponent,
}) => {
    let history = useHistory();

    return (
        <div className="p-4 flex items-center justify-between border-b border-gray-300 h-16">
            <div className="flex items-center">
                {withBackButton && (
                    <ArrowLeft
                        onClick={() =>
                            backAction ? backAction() : history.goBack()
                        }
                        className="hover:text-blue-700 cursor-pointer mr-2"
                    />
                )}
                <p className="text-xl font-medium">{title}</p>
            </div>
            {customActionsComponent ? (
                customActionsComponent
            ) : (
                <div>
                    {thirdAction && (
                        <SecondaryButton
                            text={thirdAction.title}
                            onClick={thirdAction.onClick}
                            icon={thirdAction.icon}
                            customClass="mr-2 bg-white"
                        />
                    )}
                    {secondaryAction && (
                        <SecondaryButton
                            text={secondaryAction.title}
                            onClick={secondaryAction.onClick}
                            icon={secondaryAction.icon}
                            customClass="mr-2 bg-white"
                        />
                    )}
                    {action && (
                        <Button
                            text={action.title}
                            onClick={action.onClick}
                            disabled={action.disabled}
                        />
                    )}
                </div>
            )}
        </div>
    );
};

export default Header;
