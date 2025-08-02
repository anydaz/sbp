import React, { useState } from "react";
import TextField from "./TextField";
import { Calendar as AntdCalendar, theme } from "antd";
import dayjs from "dayjs";
import useClickOutside from "../../hooks/useClickOutside";

const onPanelChange = (value, mode) => {
    console.log(value.format("YYYY-MM-DD"), mode);
};

const Calendar = ({ value, onChange, widthClass = "w-full" }) => {
    const [show, setShow] = useState(false);

    // Use the custom hook to handle clicking outside
    const calendarRef = useClickOutside(() => {
        setShow(false);
    });

    const currentValue = value ? dayjs(value) : dayjs();

    return (
        <div className="relative" ref={calendarRef}>
            <div
                className="border border-gray-300 px-3 py-2 rounded-lg cursor-pointer text-xs"
                onClick={() => setShow(!show)}
            >
                {currentValue.format("YYYY-MM-DD")}
            </div>
            {show && (
                <div
                    className={`border border-gray-300 absolute bottom-0 left-0 z-10 bg-white rounded-lg shadow-lg translate-y-full w-[300px]`}
                    onClick={(e) => e.stopPropagation()}
                >
                    <AntdCalendar
                        value={currentValue}
                        fullscreen={false}
                        onChange={(val) => {
                            onChange(val.format("YYYY-MM-DD"));
                            setShow(false); // Close calendar after date selection
                        }}
                    />
                </div>
            )}
        </div>
    );
};

export default Calendar;
