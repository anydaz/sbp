import React from 'react';

const Footer = ({leftComponent, text}) => {
    console.log("leftComponent", leftComponent);
    return (
        <div className="sticky bg-white bottom-0 p-4 border-t-2 flex justify-between items-center">
            {leftComponent}
            <p className="font-semibold ml-auto">{text}</p>
        </div>
    )
}

export default Footer;