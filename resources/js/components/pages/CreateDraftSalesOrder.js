import React, { Fragment, useState, useEffect } from "react";
import Header from "components/shared/Header.js";
import TextField from "components/shared/TextField.js";
import NumberField from "components/shared/NumberField.js";
import { IconButton } from "components/shared/Button.js";
import DropdownWithApi from "components/shared/DropdownWithApi.js";
import Api from "root/api.js";
import { useHistory, useParams } from "react-router-dom";
import ModalInfo from "components/shared/ModalInfo.js";
import { HotKeys } from "react-hotkeys";
import Footer from "components/shared/Footer";

const CreateDraftSalesOrder = () => {
    const defaultDetails = [
        { product: null, qty: 0, subtotal: 0 },
        { product: null, qty: 0, subtotal: 0 },
        { product: null, qty: 0, subtotal: 0 },
    ];

    const [selectedCustomer, setSelectedCustomer] = useState(null);
    const [details, setDetails] = useState(defaultDetails);
    const [showModal, setShowModal] = useState(false);
    const history = useHistory();
    const { id } = useParams();

    const addDetails = () => {
        setDetails([...details, defaultDetails[0]]);
    };

    const removeDetails = (index) => {
        const newDetails = [...details];
        newDetails.splice(index, 1);
        setDetails(newDetails);
    };

    const onChangeProduct = (arrayIndex, product) => {
        const newDetails = [...details];
        newDetails[arrayIndex]["product"] = product;
        newDetails[arrayIndex]["qty"] = 1;
        newDetails[arrayIndex]["price"] = product.price;
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
    };

    const onChangeQuantity = (arrayIndex, quantity) => {
        // console.log("quantity", quantity)
        const newDetails = [...details];
        newDetails[arrayIndex]["qty"] = quantity;
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
    };

    const calculateSubTotal = (detail) => {
        detail["subtotal"] = (detail.price || 0) * (detail.qty || 0);
    };

    const calculateTotal = () => {
        const total = details.reduce((accum, detail) => {
            return accum + parseFloat(detail.subtotal);
        }, 0);
        return total;
    };

    const handleSave = async () => {
        const restructured_details = details
            .map((detail) => {
                if (!(detail.product && detail.qty > 0)) return;

                return {
                    product_id: detail.product.id,
                    price: detail.price,
                    qty: detail.qty,
                    subtotal: detail.subtotal,
                };
            })
            .filter((val) => val);

        if (restructured_details.length > 0) {
            const body = {
                customer_id: selectedCustomer.id,
                details: restructured_details,
            };

            const method = id ? "PUT" : "POST";
            const url = id
                ? `/api/draft_sales_orders/${id}`
                : "/api/draft_sales_orders";

            const response = await Api(url, body, method);
            if (response) {
                setShowModal(true);
                setTimeout(() => {
                    history.push("/draft-sales-order");
                }, 1000);
            }
        }
    };

    const keyMap = {
        ADD: "shift++",
        DELETE: "shift+-",
    };

    const handlers = {
        ADD: addDetails,
        DELETE: () => removeDetails(details.length - 1),
    };

    const getDraft = async () => {
        const response = await Api(`/api/draft_sales_orders/${id}`);
        if (response) {
            const draft = response.data;
            setSelectedCustomer(draft.customer);
            const details = draft.details.map((detail) => {
                return {
                    product: detail.product,
                    price: detail.price,
                    qty: detail.qty,
                    subtotal: detail.subtotal,
                };
            });
            setDetails(details);
        }
    };

    useEffect(() => {
        if (id) {
            getDraft();
        }
    }, []);

    return (
        <HotKeys
            className={"min-h-screen flex flex-col"}
            keyMap={keyMap}
            handlers={handlers}
            allowChanges={true}
        >
            <Header
                title="Create Draft Sales Order"
                action={{ title: "save", onClick: handleSave }}
                withBackButton={true}
            />
            <div className="p-4 mb-16 h-full flex-auto">
                <div className="w-1/3 mr-1 mb-5">
                    <label
                        class={`font-semibold text-xs text-gray-600 pb-1 block`}
                    >
                        Customer
                    </label>
                    <DropdownWithApi
                        onChange={(customer) => setSelectedCustomer(customer)}
                        selected={selectedCustomer}
                        type="customer"
                    />
                </div>
                <div className="flex justify-between">
                    <div className="w-12">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            No
                        </label>
                    </div>
                    <div className="w-2/5">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Produk
                        </label>
                    </div>
                    <div className="w-32">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Kuantitas
                        </label>
                    </div>
                    <div className="w-32">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Harga
                        </label>
                    </div>
                    <div className="w-36">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Sub Total
                        </label>
                    </div>
                    <div className="w-28">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Aksi
                        </label>
                    </div>
                </div>
                {details.map((detail, index) => {
                    return (
                        <div key={index} className="flex justify-between">
                            <div className="w-12">
                                <TextField
                                    value={index + 1}
                                    disabled={true}
                                    onChange={(e) => {}}
                                />
                            </div>
                            <div className="w-2/5">
                                <DropdownWithApi
                                    type="product"
                                    selected={details[index]?.product}
                                    onChange={(product) =>
                                        onChangeProduct(index, product)
                                    }
                                    customOptionsWidth="w-50vw"
                                />
                            </div>
                            <div className="w-32">
                                <NumberField
                                    value={detail.qty}
                                    onChange={(value) => {
                                        onChangeQuantity(index, value);
                                    }}
                                />
                            </div>
                            <div className="w-32">
                                <NumberField
                                    type="number"
                                    value={detail.price}
                                    disabled={true}
                                />
                                {/* <TextField
									value={detail.price?.toLocaleString("id-ID")}
									disabled={true}
								/> */}
                            </div>
                            <div className="w-36">
                                <TextField
                                    disabled={true}
                                    value={detail.subtotal?.toLocaleString(
                                        "id-ID"
                                    )}
                                />
                            </div>
                            <div className="w-28 flex">
                                {details.length > 1 && (
                                    <IconButton
                                        icon="Minus"
                                        onClick={() => removeDetails(index)}
                                        customClass="mr-2"
                                    />
                                )}
                                {index == details.length - 1 && (
                                    <IconButton
                                        icon="Plus"
                                        onClick={addDetails}
                                    />
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
            {showModal && <ModalInfo />}
            <Footer
                text={"Total: " + calculateTotal().toLocaleString("id-ID")}
            />
        </HotKeys>
    );
};

export default CreateDraftSalesOrder;
