import React, { Fragment, useEffect, useState } from "react";
import Header from "components/shared/Header.js";
import TextField from "components/shared/TextField.js";
import NumberField from "components/shared/NumberField.js";
import DropdownWithApi from "components/shared/DropdownWithApi.js";
import Api from "root/api.js";
import { useHistory, useParams } from "react-router-dom";
import ModalInfo from "components/shared/ModalInfo.js";
import { Info } from "lucide-react";

const CreateDeliveryNote = () => {
    const defaultDetail = {
        product: null,
        purchase_order_details_id: null,
        qty: 0,
        received_qty: 0,
    };
    const [selectedPurchase, setSelectedPurchase] = useState(null);
    const [details, setDetails] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const history = useHistory();
    const { id } = useParams();

    const onChangeReceivedQuantity = (arrayIndex, quantity) => {
        const newDetails = [...details];

        // validation -> received quantity cannot more than order quantity
        const orderQty = newDetails[arrayIndex]["qty"];
        const already_received = newDetails[arrayIndex]["already_received"];
        let newQuantity = quantity;

        if (orderQty < already_received + quantity) {
            console.log("masuk sini", orderQty < already_received + quantity);
            newQuantity = orderQty - already_received;
        }

        newDetails[arrayIndex]["received_qty"] = newQuantity;
        setDetails(newDetails);
    };

    const handleSave = async () => {
        const restructured_details = details
            .map((detail) => {
                if (!(detail.product && detail.received_qty)) return;

                const shippingCostPerItem =
                    selectedPurchase.shipping_cost_per_item || 0;

                return {
                    purchase_order_detail_id: detail.purchase_order_detail_id,
                    product_id: detail.product.id,
                    received_qty: detail.received_qty,
                    received_value:
                        detail.received_qty *
                        (parseFloat(detail.price) +
                            parseFloat(shippingCostPerItem)),
                };
            })
            .filter((val) => val);

        if (restructured_details.length > 0) {
            const body = {
                purchase_order_id: selectedPurchase.id,
                details: restructured_details,
            };

            const method = id ? "PUT" : "POST";
            const url = id
                ? `/api/delivery_notes/${id}`
                : "/api/delivery_notes";

            const response = await Api(url, body, method);
            if (response) {
                setShowModal(true);
                setTimeout(() => {
                    history.push("/delivery-note");
                }, 1000);
            }
        }
    };

    useEffect(() => {
        if (!selectedPurchase || id) return;

        const deliveryDetails = selectedPurchase.details.map((detail) => {
            const already_received = detail.delivery_details.reduce(
                (accum, value) => accum + value.received_qty,
                0
            );

            return {
                ...defaultDetail,
                purchase_order_detail_id: detail.id,
                already_received: already_received,
                product: detail.product,
                price: detail.price,
                received_qty: detail.qty - already_received,
            };
        });
        // console.log("deliveryDetails", deliveryDetails);
        setDetails(deliveryDetails);
    }, [selectedPurchase]);

    const getReceivedQty = (delivery_details, received_qty) => {
        // should only called by getDeliveryNote
        // sum all quantity from all delivery that is related to purhcase order detail
        // but need to substract by current received qty
        const sum = delivery_details.reduce(
            (accum, value) => accum + value.received_qty,
            0
        );
        return sum - received_qty;
    };

    const getDeliveryNote = async () => {
        const response = await Api(`/api/delivery_notes/${id}`);
        if (response) {
            const delivery = response.data;
            setSelectedPurchase(delivery.purchase_order);
            const details = delivery.details.map((detail) => {
                return {
                    purchase_order_detail_id: detail.purchase_order_detail_id,
                    qty: detail.purchase_order_detail.qty,
                    already_received: getReceivedQty(
                        detail.purchase_order_detail.delivery_details,
                        detail.received_qty
                    ),
                    product: detail.product,
                    price: detail.price,
                    received_qty: detail.received_qty,
                };
            });
            setDetails(details);
        }
    };

    useEffect(() => {
        if (id) {
            getDeliveryNote();
        }
    }, []);

    return (
        <>
            <Header
                title="Buat Bukti Penerimaan"
                action={{
                    title: "save",
                    onClick: () => {
                        handleSave();
                    },
                }}
                withBackButton={true}
            />
            <div className="p-4 h-full flex-auto">
                {!selectedPurchase && (
                    <div className="bg-blue-300 rounded-lg mb-2">
                        <div className="max-w-7xl mx-auto p-2">
                            <div className="flex items-center justify-between flex-wrap">
                                <div className="w-0 flex-1 flex items-center">
                                    <span className="flex p-1 rounded-lg bg-blue-200">
                                        <Info />
                                    </span>
                                    <p className="ml-3 font-sm">
                                        Silahkan memilih pembelian terlebih
                                        dahulu
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
                <label class={`font-semibold text-xs text-gray-600 pb-1 block`}>
                    Pembelian
                </label>
                <DropdownWithApi
                    onChange={(purchase) => setSelectedPurchase(purchase)}
                    selected={selectedPurchase}
                    type="purchase order"
                    disabled={id}
                />
                {selectedPurchase && (
                    <Fragment>
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

                            <div className="w-5/12">
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
                                    Harga
                                </label>
                            </div>
                            <div className="w-32">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Kuantitas Pembelian
                                </label>
                            </div>
                            <div className="w-32">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Kuantitas Telah Diterima
                                </label>
                            </div>
                            <div className="w-32">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Kuantitas Akan Diterima
                                </label>
                            </div>
                        </div>
                        {details.map((detail, index) => {
                            return (
                                <div
                                    key={index}
                                    className="flex justify-between"
                                >
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
                                                details[index]?.product?.code ||
                                                ""
                                            }
                                            disabled={true}
                                            onChange={(e) => {}}
                                        />
                                    </div>
                                    <div className="w-5/12">
                                        <TextField
                                            disabled={true}
                                            value={
                                                details[index]?.product?.name
                                            }
                                        />
                                    </div>
                                    <div className="w-32">
                                        <NumberField
                                            value={detail.price}
                                            onChange={() => {}}
                                            disabled={true}
                                        />
                                    </div>
                                    <div className="w-32">
                                        <NumberField
                                            value={detail.qty}
                                            onChange={() => {}}
                                            disabled={true}
                                        />
                                    </div>
                                    <div className="w-32">
                                        <NumberField
                                            value={detail.already_received}
                                            onChange={() => {}}
                                            disabled={true}
                                        />
                                    </div>
                                    <div className="w-32">
                                        <NumberField
                                            value={detail.received_qty}
                                            onChange={(value) => {
                                                onChangeReceivedQuantity(
                                                    index,
                                                    value
                                                );
                                            }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </Fragment>
                )}
                {showModal && <ModalInfo />}
            </div>
        </>
    );
};

export default CreateDeliveryNote;
