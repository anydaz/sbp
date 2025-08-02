import React from "react";

const Chip = ({ items, selectedId, handleClick }) => {
    return (
        <div className="flex flex-wrap gap-2" style={{ height: "min-content" }}>
            {items.map((item) => (
                <button
                    key={item.id}
                    onClick={() => handleClick(item.id)}
                    className={`rounded-full border transition-all px-2 py-1
            ${
                item.id === selectedId
                    ? "border-blue-500 text-blue-600"
                    : "border-gray-300 text-gray-700 hover:border-gray-500"
            }`}
                >
                    {item.title}
                </button>
            ))}
        </div>
    );
};

export default Chip;
