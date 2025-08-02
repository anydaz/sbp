import React from "react";
import * as Icons from "lucide-react";

const MenuItem = ({ title, icon, onClick }) => {
    // Get the icon component from Lucide React
    const IconComponent = Icons[icon];

    return (
        <div
            onClick={onClick}
            className="flex items-center text-sm hover:border-blue-700 hover:border-l-4 hover:bg-gray-200 p-3 cursor-pointer"
        >
            {IconComponent ? (
                <IconComponent className="mr-3 text-blue-700 w-5 h-5" />
            ) : (
                <Icons.HelpCircle className="mr-3 text-blue-700 w-5 h-5" />
            )}
            {title}
        </div>
    );
};

export default MenuItem;
