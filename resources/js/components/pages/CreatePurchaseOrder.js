import React, { Fragment, useEffect, useState } from "react";
import Header from "components/shared/Header.js";
import TextField from "components/shared/TextField.js";
import NumberField from "components/shared/NumberField.js";
import { IconButton } from "components/shared/Button.js";
import DropdownWithApi from "components/shared/DropdownWithApi.js";
import Api from "root/api.js";
import { useHistory, useParams } from "react-router-dom";
import ModalInfo from "components/shared/ModalInfo.js";
import Footer from "components/shared/Footer";
import { HotKeys } from "react-hotkeys";
import { find, set } from "lodash";
import SearchProduct from "../shared/SearchProduct";
import useSearchProduct from "../../hooks/useSearchProduct";

const CreatePurchaseOrder = () => {
    const defaultDetail = {
        product: null,
        qty: 0,
        price: 0,
        item_discount: 0,
        subtotal: 0,
    };
    const [onSearchProduct, setOnSearchProduct] = useState(false);
    const [purchaseNumber, setPurchaseNumber] = useState("");
    const [details, setDetails] = useState([defaultDetail]);
    const [purchaseDiscount, setPurchaseDiscount] = useState(0);
    const [shippingCost, setShippingCost] = useState(0);
    const [showModal, setShowModal] = useState(false);
    const [isCalculatingDiscount, setIsCalculationDiscount] = useState(false); // flag to prevent infinite loop
    const history = useHistory();
    const { id } = useParams();

    const { ref, onFindProduct, addProductToDetails } = useSearchProduct({
        details,
        setDetails,
        defaultDetail,
    });

    const addDetails = () => {
        setDetails([...details, defaultDetail]);
    };

    const removeDetails = (index) => {
        if (index == 0) return;

        const newDetails = [...details];
        newDetails.splice(index, 1);
        setDetails(newDetails);
    };

    const onChangeProduct = (arrayIndex, product) => {
        console.log(product);
        const newDetails = [...details];
        newDetails[arrayIndex]["product"] = product;
        newDetails[arrayIndex]["qty"] = 0;
        newDetails[arrayIndex]["item_discount"] = 0;
        newDetails[arrayIndex]["price"] = product.price;
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
    };

    const onChangeQuantity = (arrayIndex, quantity) => {
        const newDetails = [...details];
        newDetails[arrayIndex]["qty"] = quantity;
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
    };

    const onChangePrice = (arrayIndex, price) => {
        const newDetails = [...details];
        newDetails[arrayIndex]["price"] = price;
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
    };

    const onChangeItemDiscount = (arrayIndex, discount) => {
        if (isCalculatingDiscount) return;

        setIsCalculationDiscount(true);
        const newDetails = [...details];
        newDetails[arrayIndex]["item_discount"] = discount;
        calculateDiscount(newDetails[arrayIndex], "nominal");
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);

        setTimeout(() => {
            setIsCalculationDiscount(false);
        }, 50);
    };

    const onChangeDiscountPercentage = (
        arrayIndex,
        discPercentage,
        stackLevel = 1
    ) => {
        if (isCalculatingDiscount) return;

        setIsCalculationDiscount(true);
        const newDetails = [...details];
        const key = `discount_percentage${stackLevel || ""}`;
        newDetails[arrayIndex][key] =
            discPercentage && discPercentage > 100 ? 100 : discPercentage || 0;

        calculateDiscount(newDetails[arrayIndex], "percentage");
        calculateSubTotal(newDetails[arrayIndex]);
        setDetails(newDetails);
        setTimeout(() => {
            setIsCalculationDiscount(false);
        }, 50);
    };

    const calculateDiscount = (detail, type = "nominal") => {
        if (type == "nominal") {
            const percentage = (detail.item_discount / detail.price) * 100;
            detail["discount_percentage"] = parseFloat(percentage);
            detail["discount_percentage2"] = 0;
            detail["discount_percentage3"] = 0;
        } else if (type == "percentage") {
            const nominal = calculateNominalFromStackedDiscount(detail);
            detail["item_discount"] = parseFloat(nominal);
        }
    };

    const calculateNominalFromStackedDiscount = (detail) => {
        const percentageArray = [
            detail["discount_percentage1"] || 0,
            detail["discount_percentage2"] || 0,
            detail["discount_percentage3"] || 0,
        ];

        const discountNominal = percentageArray.reduce((accumulator, value) => {
            return value == 0
                ? accumulator
                : accumulator - (value / 100) * accumulator;
        }, detail.price);

        return detail.price - parseInt(discountNominal);
    };

    const calculateSubTotal = (detail) => {
        const price = detail.price || 0;
        const discount = detail.item_discount || 0;
        const qty = detail.qty || 0;

        detail["subtotal"] = (price - discount) * qty;
    };

    const calculateTotal = () => {
        const total = details.reduce((accum, detail) => {
            return accum + parseFloat(detail.subtotal);
        }, 0);
        return total - purchaseDiscount;
    };

    const handleSave = async () => {
        const restructured_details = details
            .map((detail) => {
                if (!(detail.product && detail.qty)) return;

                return {
                    product_id: detail.product.id,
                    price: detail.price,
                    item_discount: detail.item_discount,
                    discount_percentage1: detail.discount_percentage1,
                    discount_percentage2: detail.discount_percentage2,
                    discount_percentage3: detail.discount_percentage3,
                    qty: detail.qty,
                    subtotal: detail.subtotal,
                };
            })
            .filter((val) => val);

        if (restructured_details.length > 0) {
            const body = {
                purchase_number: purchaseNumber,
                purchase_discount: purchaseDiscount,
                shipping_cost: shippingCost,
                details: restructured_details,
            };

            const method = id ? "PUT" : "POST";
            const url = id
                ? `/api/purchase_orders/${id}`
                : "/api/purchase_orders";

            const response = await Api(url, body, method);
            if (response) {
                setShowModal(true);
                setTimeout(() => {
                    history.push("/purchase-order");
                }, 1000);
            }
        }
    };

    const getPurchase = async () => {
        const response = await Api(`/api/purchase_orders/${id}`);
        if (response) {
            const purchase = response.data;
            setPurchaseNumber(purchase.purchase_number);
            setPurchaseDiscount(purchase.purchase_discount);
            setShippingCost(purchase.shipping_cost);
            const details = purchase.details.map((detail) => {
                return {
                    product: detail.product,
                    price: detail.price,
                    qty: detail.qty,
                    item_discount: detail.item_discount,
                    discount_percentage1: detail.discount_percentage1,
                    discount_percentage2: detail.discount_percentage2,
                    discount_percentage3: detail.discount_percentage3,
                    subtotal: detail.subtotal,
                };
            });
            setDetails(details);
        }
    };

    useEffect(() => {
        if (id) {
            getPurchase();
        }
    }, []);

    const keyMap = {
        ADD: "shift++",
        DELETE: "shift+-",
    };

    const handlers = {
        ADD: addDetails,
        DELETE: () => removeDetails(details.length - 1),
    };

    if (onSearchProduct) {
        return (
            <SearchProduct
                setOnSearchProduct={setOnSearchProduct}
                onSelectProduct={(product) => {
                    addProductToDetails(product);
                    setOnSearchProduct(false);
                }}
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
                title="Buat Purchase Order"
                action={{
                    title: "save",
                    onClick: () => {
                        handleSave();
                    },
                }}
                withBackButton={true}
            />
            <div className="p-4 h-full flex flex-col flex-grow min-h-0">
                <div className="flex">
                    <TextField
                        value={purchaseNumber}
                        label="Nomor Pembelian"
                        onChange={(e) => {
                            setPurchaseNumber(e.target.value);
                        }}
                        customWrapperClass={"w-1/2 mr-1"}
                    />
                    <NumberField
                        value={shippingCost}
                        label="Biaya Pengiriman"
                        onChange={(value) => {
                            setShippingCost(value);
                        }}
                        customWrapperClass={"w-1/2 ml-1"}
                    />
                </div>
                <div className="flex items-center mt-2 mb-2">
                    <TextField
                        defaultValue=""
                        label="Input Kode Produk"
                        onKeyDown={(e) => {
                            if (e.key === "Enter") {
                                e.preventDefault();
                                onFindProduct(e.target.value);
                                e.target.value = ""; // Clear input after finding product
                            }
                        }}
                        customWrapperClass="w-1/5"
                    />
                    <IconButton
                        icon="Search"
                        onClick={() => {
                            setOnSearchProduct(true);
                        }}
                        customClass="ml-2"
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
                    <div className="w-20">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Kode
                        </label>
                    </div>
                    <div className="w-1/4">
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
                    <div className="w-16">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Diskon (Rp)
                        </label>
                    </div>
                    <div className="w-12">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Diskon (%)
                        </label>
                    </div>
                    <div className="w-12">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Diskon (%)
                        </label>
                    </div>
                    <div className="w-12">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Diskon (%)
                        </label>
                    </div>
                    <div className="w-36">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Subtotal
                        </label>
                    </div>
                    <div className="w-20">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        ></label>
                    </div>
                </div>
                <div
                    className="flex flex-col overflow-y-auto flex-grow min-h-0"
                    ref={ref}
                >
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
                                <div className="w-20">
                                    <TextField
                                        value={
                                            details[index]?.product?.code || ""
                                        }
                                        disabled={true}
                                        onChange={(e) => {}}
                                    />
                                </div>

                                <div className="w-1/4">
                                    <DropdownWithApi
                                        type="product"
                                        selected={details[index]?.product}
                                        onChange={(product) => {
                                            onChangeProduct(index, product);
                                        }}
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
                                        value={detail.price}
                                        onChange={(value) => {
                                            onChangePrice(index, value);
                                        }}
                                    />
                                </div>
                                <div className="w-16">
                                    <NumberField
                                        value={detail.item_discount}
                                        onChange={(value) => {
                                            onChangeItemDiscount(index, value);
                                        }}
                                    />
                                </div>
                                <div className="w-12">
                                    <NumberField
                                        value={detail.discount_percentage1}
                                        onChange={(value) => {
                                            onChangeDiscountPercentage(
                                                index,
                                                value,
                                                1
                                            );
                                        }}
                                    />
                                </div>
                                <div className="w-12">
                                    <NumberField
                                        value={detail.discount_percentage2}
                                        onChange={(value) => {
                                            onChangeDiscountPercentage(
                                                index,
                                                value,
                                                2
                                            );
                                        }}
                                    />
                                </div>
                                <div className="w-12">
                                    <NumberField
                                        value={detail.discount_percentage3}
                                        onChange={(value) => {
                                            onChangeDiscountPercentage(
                                                index,
                                                value,
                                                3
                                            );
                                        }}
                                    />
                                </div>
                                <div className="w-36">
                                    <NumberField
                                        disabled={true}
                                        value={detail.subtotal}
                                    />
                                </div>
                                <div className="w-20 flex">
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
                {showModal && <ModalInfo />}
            </div>
            <Footer
                text={"Total: " + calculateTotal().toLocaleString("id-ID")}
            />
        </HotKeys>
    );
};

export default CreatePurchaseOrder;
