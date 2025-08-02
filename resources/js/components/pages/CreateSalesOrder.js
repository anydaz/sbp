import React, {
    Fragment,
    useCallback,
    useEffect,
    useRef,
    useState,
} from "react";
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
import SearchProduct from "../shared/SearchProduct";
import useSearchProduct from "../../hooks/useSearchProduct";
import Calendar from "../shared/Calendar";
import dayjs from "dayjs";
import { Info } from "lucide-react";

const CreateSalesOrder = () => {
    const defaultDetail = {
        product: null,
        qty: "",
        price: 0,
        item_discount: 0,
        subtotal: 0,
    };
    const [selectedDraft, setSelectedDraft] = useState(null);
    const [isManual, setIsManual] = useState(true);
    const [selectedCustomer, setSelectedCustomer] = useState({
        id: 1,
        name: "Tanpa Nama",
    });
    const [onSearchProduct, setOnSearchProduct] = useState(false);
    const [selectedPaymentType, setSelectedPaymentType] = useState(null);
    const [salesNumber, setSalesNumber] = useState(null);
    const [details, setDetails] = useState([defaultDetail]);
    const [salesDiscount, setSalesDiscount] = useState(0);
    const [totalReturn, setTotalReturn] = useState(0);
    const [showModal, setShowModal] = useState(false);
    const [isCalculatingDiscount, setIsCalculationDiscount] = useState(false); // flag to prevent
    const [isPendingAllowed, setIsPendingAllowed] = useState(true);
    const [date, setDate] = useState(dayjs().format("YYYY-MM-DD"));
    const [notes, setNotes] = useState("");
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
        const newDetails = [...details];
        newDetails.splice(index, 1);
        setDetails(newDetails);
    };

    const onChangeProduct = (arrayIndex, product) => {
        const newDetails = [...details];
        newDetails[arrayIndex]["product"] = product;
        newDetails[arrayIndex]["qty"] = 1;
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

    const onChangeDiscountPercentage = (arrayIndex, discPercentage) => {
        if (isCalculatingDiscount) return;

        setIsCalculationDiscount(true);
        const newDetails = [...details];
        newDetails[arrayIndex]["discount_percentage"] =
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
        } else if (type == "percentage") {
            const nominal = (detail.discount_percentage / 100) * detail.price;
            detail["item_discount"] = parseFloat(nominal);
        }
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
        return total - totalReturn - (salesDiscount || 0);
    };

    const handleSave = async (isPending = false) => {
        let validStock = true;

        const restructured_details = details
            .map((detail) => {
                const remainingStock =
                    detail.product.quantity + (detail?.previous_qty || 0);
                if (detail.qty > remainingStock) {
                    validStock = false;
                }
                if (!(detail.product && detail.qty)) return;

                return {
                    product_id: detail.product.id,
                    cogs: detail.cogs || detail.product.cogs, // use cogs from details if persist
                    price: detail.price,
                    item_discount: detail.item_discount,
                    qty: detail.qty,
                    subtotal: detail.subtotal,
                };
            })
            .filter((val) => val);

        if (!validStock) {
            alert("Kuantitas melebihi stok yang tersedia");
            return;
        }

        if (restructured_details.length > 0) {
            const body = {
                customer_id: selectedCustomer.id,
                payment_type_id: selectedPaymentType.id,
                sales_discount: salesDiscount,
                total_return: totalReturn,
                draft_sales_order_id: selectedDraft?.id,
                details: restructured_details,
                is_pending: isPending,
                date: date,
                notes: notes,
            };

            const method = id ? "PUT" : "POST";
            const url = id ? `/api/sales_orders/${id}` : "/api/sales_orders";

            const response = await Api(url, body, method);
            if (response && !isPending) {
                print(response.data);
            } else if (response && isPending) {
                setShowModal(true);
                setTimeout(() => {
                    history.push("/sales-order");
                }, 1000);
            } else {
            }
        }
    };

    const print = async (salesData) => {
        var data = new URLSearchParams();
        const item_data = salesData.details.map((detail) => {
            return [
                detail.product.alias,
                detail.qty,
                parseFloat(detail.price).toLocaleString("id-ID"),
                parseFloat(detail.item_discount).toLocaleString("id-ID"),
            ];
        });
        data.append("data", JSON.stringify(item_data));

        const totalBeforeDiscount = salesData.details.reduce(
            (accum, value) => accum + parseFloat(value.subtotal),
            0
        );
        const salesDiscount = salesData.sales_discount;
        const totalAfterDiscount =
            totalBeforeDiscount - totalReturn - salesDiscount;
        const date = new Date(salesData.created_at).toLocaleDateString(
            "id-ID",
            { year: "numeric", month: "short", day: "numeric" }
        );

        data.append(
            "total_before_discount",
            totalBeforeDiscount.toLocaleString("id-ID")
        );
        data.append("sales_discount", salesDiscount.toLocaleString("id-ID"));
        data.append("total_return", totalReturn.toLocaleString("id-ID"));
        data.append(
            "total_after_discount",
            totalAfterDiscount.toLocaleString("id-ID")
        );
        data.append("notes", salesData.notes ? salesData.notes : "");
        data.append("date", date);
        data.append("payment_type", salesData.payment_type.name);

        // const response = await Api(urls, formData, "POST");

        await Api(
            `/api/print`,
            {
                data: item_data,
                total_before_discount:
                    totalBeforeDiscount.toLocaleString("id-ID"),
                sales_discount: salesDiscount.toLocaleString("id-ID"),
                total_return: salesData.total_return.toLocaleString("id-ID"),
                total_after_discount:
                    totalAfterDiscount.toLocaleString("id-ID"),
                notes: salesData.notes ? salesData.notes : "",
                date: date,
                payment_type: salesData.payment_type.name,
                sales_number: salesData.sales_number,
            },
            "POST"
        );
        setShowModal(true);
        setTimeout(() => {
            history.push("/sales-order");
        }, 1000);

        // fetch("/api/print", {
        //     method: "POST",
        //     body: data,
        // }).then(function (response) {
        //     setShowModal(true);
        //     setTimeout(() => {
        //         history.push("/sales-order");
        //     }, 1000);
        // });
    };

    useEffect(() => {
        if (!selectedDraft || id) return;

        const salesDetails = selectedDraft.details.map((detail) => {
            return {
                ...defaultDetail,
                product: detail.product,
                qty: detail.qty,
                price: detail.price,
                subtotal: detail.subtotal,
            };
        });
        setSelectedCustomer(selectedDraft.customer);
        setDetails(salesDetails);

        // setSelectedCustomer(selectedDraft.customer);
    }, [selectedDraft]);

    const getSales = async () => {
        const response = await Api(`/api/sales_orders/${id}`);
        if (response) {
            const sales = response.data;
            if (sales.draft) {
                setSelectedDraft(sales.draft);
            } else {
                setIsManual(true);
            }
            setSelectedCustomer(sales.customer);
            setSalesDiscount(sales.sales_discount);
            setTotalReturn(sales.total_return);
            setIsPendingAllowed(sales.is_pending);
            setNotes(sales.notes);
            setDate(
                sales.date
                    ? dayjs(sales.date).format("YYYY-MM-DD")
                    : dayjs().format("YYYY-MM-DD")
            );
            setSelectedPaymentType(sales.payment_type);
            const details = sales.details.map((detail) => {
                return {
                    product: detail.product,
                    price: detail.price,
                    previous_qty: detail.qty,
                    qty: detail.qty,
                    item_discount: detail.item_discount,
                    discount_percentage:
                        (detail.item_discount / detail.price) * 100,
                    subtotal: detail.subtotal,
                    cogs: detail.cogs,
                };
            });
            setDetails(details);
        }
    };

    const NotesInputField = useCallback(() => {
        return (
            <TextField
                value={notes}
                onChange={(e) => {
                    setNotes(e.target.value);
                }}
                label="Notes"
                customWrapperClass={"w-1/5 ml-1 mr-1"}
            />
        );
    }, [notes]);

    useEffect(() => {
        if (id) {
            getSales();
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
                withBackButton={true}
                title="Buat Sales Order"
                action={{
                    title: "Simpan & Print Struk",
                    onClick: () => {
                        handleSave();
                    },
                }}
                secondaryAction={
                    isPendingAllowed && {
                        title: "Pending",
                        onClick: () => {
                            handleSave(true);
                        },
                    }
                }
            />
            <div className="p-4 h-full flex flex-col flex-grow min-h-0">
                {!selectedDraft && !isManual && (
                    <div className="bg-blue-300 rounded-lg mb-2">
                        <div className="max-w-7xl mx-auto p-2">
                            <div className="flex items-center justify-between flex-wrap">
                                <div className="w-0 flex-1 flex items-center">
                                    <span className="flex p-1 rounded-lg bg-blue-200">
                                        <Info />
                                    </span>
                                    <p className="ml-3 font-sm">
                                        Silahkan memilih draft penjualan
                                        terlebih dahulu
                                    </p>
                                </div>
                                <p
                                    onClick={() => setIsManual(true)}
                                    className="mr-1 cursor-pointer hover:text-white"
                                >
                                    Buat Manual
                                </p>
                            </div>
                        </div>
                    </div>
                )}
                {!isManual && (
                    <>
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Draft Penjualan
                        </label>
                        <DropdownWithApi
                            onChange={(draft) => setSelectedDraft(draft)}
                            selected={selectedDraft}
                            type="draft sales order"
                            disabled={id}
                        />
                    </>
                )}
                {(selectedDraft || isManual) && (
                    <Fragment>
                        <div className="flex justify-between gap-[8px]">
                            <div className="w-[228px]">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Customer
                                </label>
                                <DropdownWithApi
                                    onChange={(customer) =>
                                        setSelectedCustomer(customer)
                                    }
                                    selected={selectedCustomer}
                                    type="customer"
                                />
                            </div>
                            <NumberField
                                value={salesDiscount}
                                label="Diskon Nota"
                                onChange={(value) => {
                                    setSalesDiscount(value);
                                }}
                                customWrapperClass={"w-[228px]"}
                            />
                            {/* <TextField
                                value={totalReturn || 0}
                                label="Total Retur"
                                disabled={true}
                                customWrapperClass={"w-[228px]"}
                            /> */}

                            <NumberField
                                label="Total Retur"
                                value={totalReturn}
                                onChange={(value) => {
                                    setTotalReturn(value);
                                }}
                                customWrapperClass={"w-[228px]"}
                            />
                            <TextField
                                value={salesNumber || "-"}
                                label="No Penjualan"
                                disabled={true}
                                customWrapperClass={"w-[228px]"}
                            />
                            <div className="w-[228px]">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Tipe Pembayaran
                                </label>
                                <DropdownWithApi
                                    onChange={(customer) =>
                                        setSelectedPaymentType(customer)
                                    }
                                    selected={selectedPaymentType}
                                    type="payment type"
                                    fetchOnRender={true}
                                    firstDataAsDefault={!id}
                                    disabled={id}
                                />
                            </div>
                        </div>
                        <div className="flex items-start mt-2 mb-2 gap-[8px]">
                            <div className="w-[228px] flex">
                                <TextField
                                    defaultValue=""
                                    label="Input Kode Barang"
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
                                    customClass="ml-2 mt-[20px]"
                                />
                            </div>

                            <div className="w-[228px]">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Tanggal
                                </label>
                                <Calendar value={date} onChange={setDate} />
                            </div>
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
                                    Kode Efisiensi
                                </label>
                            </div>
                            <div className="w-1/3">
                                <label
                                    class={`font-semibold text-xs text-gray-600 pb-1 block`}
                                >
                                    Produk
                                </label>
                            </div>
                            <div className="w-24">
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
                            <div className="w-16">
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
                                const remainingStock =
                                    details[index]?.product?.quantity +
                                    (details[index]?.previous_qty || 0);
                                return (
                                    <div
                                        key={`${details[index]?.product?.id}-${index}`}
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
                                                    details[index]?.product
                                                        ?.efficiency_code || ""
                                                }
                                                disabled={true}
                                                onChange={(e) => {}}
                                            />
                                        </div>
                                        <div className="w-1/3">
                                            <DropdownWithApi
                                                type="product"
                                                selected={
                                                    details[index]?.product
                                                }
                                                onChange={(product) => {
                                                    onChangeProduct(
                                                        index,
                                                        product
                                                    );
                                                }}
                                                customOptionsWidth="w-50vw"
                                            />
                                        </div>
                                        <div className="w-24 relative">
                                            {detail.qty > remainingStock && (
                                                <p className="absolute top-0 transform -translate-y-full mb-2 text-xs text-red-500 w-max">
                                                    stock: {remainingStock}
                                                </p>
                                            )}
                                            <NumberField
                                                value={detail.qty}
                                                onChange={(value) => {
                                                    onChangeQuantity(
                                                        index,
                                                        value
                                                    );
                                                }}
                                            />
                                        </div>
                                        <div className="w-32">
                                            <NumberField
                                                value={detail.price}
                                                disabled={true}
                                            />
                                        </div>
                                        <div className="w-16">
                                            <NumberField
                                                value={detail.item_discount}
                                                onChange={(value) => {
                                                    onChangeItemDiscount(
                                                        index,
                                                        value
                                                    );
                                                }}
                                            />
                                        </div>
                                        <div className="w-16">
                                            <NumberField
                                                value={
                                                    detail.discount_percentage
                                                }
                                                onChange={(value) => {
                                                    onChangeDiscountPercentage(
                                                        index,
                                                        value
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
                                                    onClick={() =>
                                                        removeDetails(index)
                                                    }
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
                    </Fragment>
                )}
                {showModal && <ModalInfo />}
            </div>
            <Footer
                leftComponent={NotesInputField()}
                text={"Total: " + calculateTotal().toLocaleString("id-ID")}
            />
        </HotKeys>
    );
};

export default CreateSalesOrder;
