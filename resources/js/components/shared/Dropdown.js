import React, { useState, useEffect, Fragment, useRef } from "react";
import useClickOutside from "hooks/useClickOutside";
import { ChevronDown } from "lucide-react";

const Dropdown = ({ options, onChange, selected, customClass }) => {
    const [open, setOpen] = useState(false);
    const clickRef = useRef();

    useClickOutside(clickRef, () => setOpen(false));

    const onHandeSelect = (data) => {
        onChange(data);
        setOpen(false);
    };

    return (
        <div ref={clickRef} className="relative mb-5 ">
            <button
                type="button"
                className={`h-[34px] inline-flex justify-between w-full rounded-md border border-gray-300
              shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50
              ${customClass}`}
                id="options-menu"
                onClick={() => setOpen(!open)}
            >
                {selected || "Belum Terpilih"}
                <ChevronDown className="w-5 h-5" />
            </button>
            {open && (
                <div className="z-10 origin-top-right absolute right-0 mt-2 w-full rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                    <div
                        className="p-1"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="options-menu"
                    >
                        <div className="max-h-40 overflow-auto">
                            {options.map((option, index) => {
                                return (
                                    <Fragment key={index}>
                                        <div
                                            className="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100
                            hover:text-gray-900 cursor-pointer"
                                            onClick={() =>
                                                onHandeSelect(option)
                                            }
                                        >
                                            <p>{option?.display || option}</p>
                                        </div>
                                    </Fragment>
                                );
                            })}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Dropdown;
