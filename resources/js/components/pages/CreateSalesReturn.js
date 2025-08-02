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
import SearchProduct from "../shared/SearchProduct";
import useSearchProduct from "../../hooks/useSearchProduct";

const CreateSalesReturn = () => {
    const defaultDetails = [
        { product: null, qty: 0, subtotal: 0 },
        { product: null, qty: 0, subtotal: 0 },
        { product: null, qty: 0, subtotal: 0 },
    ];

    const [onSearchProduct, setOnSearchProduct] = useState(false);
    const [customer, setCustomer] = useState(null);
    const [details, setDetails] = useState(defaultDetails);
    const [showModal, setShowModal] = useState(false);
    const history = useHistory();
    const { id } = useParams();

    const isSaveDisabled = () => {
        const hasProducts = details.some(
            (detail) => detail.product && detail.qty > 0
        );

        return !customer || !hasProducts;
    };

    const { ref, onFindProduct, addProductToDetails } = useSearchProduct({
        details,
        setDetails,
        defaultDetail: defaultDetails[0],
    });

    const addDetails = () => {
        setDetails([...details, defaultDetails[0]]);
    };

    const removeDetails = (index) => {
        if (index == 0) return;

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

    const onChangePrice = (arrayIndex, price) => {
        const newDetails = [...details];
        newDetails[arrayIndex]["price"] = price;
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
    };

    const onChangeQuantity = (arrayIndex, quantity) => {
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
                    price: 0,
                    qty: detail.qty,
                    subtotal: 0,
                };
            })
            .filter((val) => val);

        if (restructured_details.length > 0) {
            const body = {
                customer: customer,
                details: restructured_details,
            };

            const method = id ? "PUT" : "POST";
            const url = id ? `/api/sales_returns/${id}` : "/api/sales_returns";

            const response = await Api(url, body, method);
            if (response) {
                setShowModal(true);
                setTimeout(() => {
                    history.push("/sales-return");
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

    const getReturns = async () => {
        const response = await Api(`/api/sales_returns/${id}`);
        if (response) {
            const draft = response.data;
            setCustomer(draft.customer);
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
            getReturns();
        }
    }, []);

    if (onSearchProduct) {
        return (
            <SearchProduct
                setOnSearchProduct={setOnSearchProduct}
                onSelectProduct={addProductToDetails}
            />
        );
    }

    return (
        <HotKeys
            className={"h-screen flex flex-col"}
            keyMap={keyMap}
            handlers={handlers}
            allowChanges={true}
        >
            <Header
                title="Buat Retur Penjualan"
                action={{
                    title: "save",
                    onClick: handleSave,
                    disabled: isSaveDisabled(),
                }}
                withBackButton={true}
            />
            <div className="p-4 h-full flex flex-col flex-grow min-h-0">
                <TextField
                    value={customer}
                    label="Customer Name"
                    onChange={(e) => {
                        setCustomer(e.target.value);
                    }}
                />
                <div className="flex items-center mt-2 mb-2">
                    <TextField
                        defaultValue=""
                        label="Input Kode Barang"
                        customWrapperClass={"w-1/5"}
                        onKeyDown={(e) => {
                            if (e.key === "Enter") {
                                e.preventDefault();
                                onFindProduct(e.target.value);
                                e.target.value = ""; // Clear input after finding product
                            }
                        }}
                    />
                    <IconButton
                        icon="Search"
                        onClick={() => {
                            setOnSearchProduct(true);
                        }}
                        customClass="ml-2"
                    />
                </div>
                <div className="flex gap-[8px] ">
                    <div className="w-12">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            No
                        </label>
                    </div>
                    <div className="w-20">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Kode Efisiensi
                        </label>
                    </div>
                    <div className="w-1/2">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Produk
                        </label>
                    </div>
                    <div className="w-[250px]">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Kuantitas
                        </label>
                    </div>
                    <div className="w-30">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Aksi
                        </label>
                    </div>
                </div>
                <div
                    className="flex flex-col overflow-y-auto flex-grow min-h-0"
                    ref={ref}
                >
                    {details.map((detail, index) => {
                        return (
                            <div key={index} className="flex gap-[8px]">
                                <div className="w-12">
                                    <TextField
                                        value={index + 1}
                                        disabled={true}
                                        onChange={(e) => {}}
                                    />
                                </div>
                                <div className="w-20">
                                    <TextField
                                        value={
                                            details[index]?.product
                                                ?.efficiency_code || ""
                                        }
                                        disabled={true}
                                        onChange={(e) => {}}
                                    />
                                </div>
                                <div className="w-1/2">
                                    <DropdownWithApi
                                        type="product"
                                        selected={details[index]?.product}
                                        onChange={(product) =>
                                            onChangeProduct(index, product)
                                        }
                                        customOptionsWidth="w-50vw"
                                    />
                                </div>
                                <div className="w-[250px]">
                                    <NumberField
                                        value={detail.qty}
                                        onChange={(value) => {
                                            onChangeQuantity(index, value);
                                        }}
                                    />
                                </div>
                                <div className="w-28 flex">
                                    {details.length > 1 && (
                                        <IconButton
                                            icon="remove"
                                            onClick={() => removeDetails(index)}
                                            customClass="mr-2"
                                        />
                                    )}
                                    {index == details.length - 1 && (
                                        <IconButton
                                            icon="add"
                                            onClick={addDetails}
                                        />
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
            {showModal && <ModalInfo />}
            <Footer
                text={"Total: " + calculateTotal().toLocaleString("id-ID")}
            />
        </HotKeys>
    );
};

export default CreateSalesReturn;
