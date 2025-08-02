import { useEffect, useRef } from "react";

/**
 * Custom hook that handles clicking outside of the referenced element
 * @param {Function} handler - Function to call when clicking outside
 * @returns {Object} - Ref object to attach to the element
 */
const useClickOutside = (handler) => {
    const ref = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            // If the ref exists and the clicked target is not inside the ref element
            if (ref.current && !ref.current.contains(event.target)) {
                handler();
            }
        };

        // Add event listener
        document.addEventListener("mousedown", handleClickOutside);
        document.addEventListener("touchstart", handleClickOutside);

        // Cleanup function to remove event listeners
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
            document.removeEventListener("touchstart", handleClickOutside);
        };
    }, [handler]);

    return ref;
};

export default useClickOutside;
