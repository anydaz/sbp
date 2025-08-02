import React from "react";
import classnames from "classnames";
import { CheckCircle, XCircle } from "lucide-react";

const ModalInfo = ({ text, subText, type = "success" }) => {
    const typeclass =
        type == "success"
            ? "bg-green-100 text-green-600"
            : "bg-red-100 text-red-600";
    const icon = type === "success" ? <CheckCircle /> : <XCircle />;

    return (
        <div className="fixed z-10 inset-0 overflow-y-auto">
            <div className="flex items-center justify-center min-h-screen pt-4 px-4 pb-20">
                <div
                    className="fixed inset-0 transition-opacity"
                    aria-hidden="true"
                >
                    <div className="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <div
                    className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-headline"
                >
                    <div className="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div className="flex flex-col justify-center items-center">
                            <div
                                className={classnames(
                                    "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full",
                                    typeclass
                                )}
                            >
                                {icon}
                            </div>
                            <div className="mt-3 text-center">
                                <h3
                                    className="text-lg leading-6 font-medium text-gray-900"
                                    id="modal-headline"
                                >
                                    {text || "Data Berhasil disimpan"}
                                </h3>
                                <div className="mt-2">
                                    <p className="text-sm text-gray-500">
                                        {subText ||
                                            "Anda akan dialihkan ke halaman utama sesaat lagi."}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ModalInfo;
