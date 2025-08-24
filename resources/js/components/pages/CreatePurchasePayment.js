import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useHistory, useParams } from "react-router-dom";
import TextField from "../shared/TextField.js";
import NumberField from "../shared/NumberField.js";
import Dropdown from "../shared/Dropdown.js";

const CreatePurchasePayment = () => {
    const [purchaseOrders, setPurchaseOrders] = useState([]);
    const [selectedPurchaseOrder, setSelectedPurchaseOrder] = useState(null);
    const [orderDetails, setOrderDetails] = useState(null);
    const [paymentAmount, setPaymentAmount] = useState("");
    const [paymentDate, setPaymentDate] = useState(
        new Date().toISOString().split("T")[0]
    );
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const history = useHistory();
    const { id } = useParams();
    const isEdit = !!id;

    // Get credit purchase orders (orders that haven't been fully paid)
    const getCreditPurchaseOrders = async () => {
        try {
            const response = await Api("/api/purchase-orders/credit");
            const orders = response.data.data || response.data;
            setPurchaseOrders(orders);
        } catch (error) {
            console.error("Error fetching credit purchase orders:", error);
        }
    };

    // Get purchase order details including payment info
    const getPurchaseOrderDetails = async (purchaseOrderId) => {
        try {
            setLoading(true);
            const response = await Api(
                `/api/purchase-orders/${purchaseOrderId}/payment-info`
            );
            const details = response.data.data || response.data;
            setOrderDetails(details);

            // Set maximum payable amount as default
            if (details.remaining_amount > 0) {
                setPaymentAmount(details.remaining_amount.toString());
            }
        } catch (error) {
            console.error("Error fetching purchase order details:", error);
        } finally {
            setLoading(false);
        }
    };

    // Handle purchase order selection
    const handlePurchaseOrderChange = (value) => {
        setSelectedPurchaseOrder(value);
        setOrderDetails(null);
        setPaymentAmount("");

        if (value) {
            getPurchaseOrderDetails(value);
        }
    };

    // Validate payment amount
    const validatePaymentAmount = (amount) => {
        const numAmount = parseFloat(amount);
        if (!amount || numAmount <= 0) {
            return "Jumlah pembayaran harus lebih dari 0";
        }
        if (orderDetails && numAmount > orderDetails.remaining_amount) {
            return `Jumlah pembayaran tidak boleh melebihi sisa tagihan (${formatCurrency(
                orderDetails.remaining_amount
            )})`;
        }
        return null;
    };

    // Handle form submission
    const handleSubmit = async (e) => {
        e.preventDefault();

        // Validate form
        const newErrors = {};

        if (!selectedPurchaseOrder) {
            newErrors.purchase_order_id = "Purchase Order harus dipilih";
        }

        if (!paymentDate) {
            newErrors.payment_date = "Tanggal pembayaran harus diisi";
        }

        const amountError = validatePaymentAmount(paymentAmount);
        if (amountError) {
            newErrors.amount = amountError;
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        try {
            setLoading(true);

            const payload = {
                purchase_order_id: selectedPurchaseOrder,
                amount: parseFloat(paymentAmount),
                payment_date: paymentDate,
            };

            let response;
            if (isEdit) {
                response = await Api(
                    `/api/purchase_payments/${id}`,
                    payload,
                    "PUT"
                );
            } else {
                response = await Api("/api/purchase_payments", payload, "POST");
            }

            if (response.data.success || response.data.id) {
                history.push("/purchase-payment");
            } else {
                setErrors({
                    general: "Terjadi kesalahan saat menyimpan pembayaran",
                });
            }
        } catch (error) {
            console.error("Error saving purchase payment:", error);
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            } else {
                setErrors({
                    general: "Terjadi kesalahan saat menyimpan pembayaran",
                });
            }
        } finally {
            setLoading(false);
        }
    };

    // Format currency for display
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    };

    // Get existing payment data for edit mode
    const getPaymentData = async () => {
        try {
            const response = await Api(`/api/purchase-payments/${id}`);
            const payment = response.data.data || response.data;

            setSelectedPurchaseOrder(payment.purchase_order_id);
            setPaymentAmount(payment.amount.toString());
            setPaymentDate(payment.payment_date);

            // Get order details
            await getPurchaseOrderDetails(payment.purchase_order_id);
        } catch (error) {
            console.error("Error fetching payment data:", error);
        }
    };

    useEffect(() => {
        getCreditPurchaseOrders();

        if (isEdit) {
            getPaymentData();
        }
    }, [id]);

    return (
        <Fragment>
            <Header
                title={
                    isEdit
                        ? "Edit Pembayaran Pembelian"
                        : "Buat Pembayaran Pembelian"
                }
                action={{
                    title: "Kembali",
                    onClick: () => history.push("/purchase-payment"),
                }}
            />

            <div className="p-4">
                <div className="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
                    {errors.general && (
                        <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p className="text-red-800">{errors.general}</p>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Purchase Order Dropdown */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Purchase Order{" "}
                                <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={selectedPurchaseOrder || ""}
                                onChange={(e) =>
                                    handlePurchaseOrderChange(e.target.value)
                                }
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                disabled={loading || isEdit}
                            >
                                <option value="">Pilih Purchase Order</option>
                                {purchaseOrders.map((order) => (
                                    <option key={order.id} value={order.id}>
                                        {order.purchase_number} -{" "}
                                        {order.supplier} -{" "}
                                        {formatCurrency(order.total)}
                                    </option>
                                ))}
                            </select>
                            {errors.purchase_order_id && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.purchase_order_id}
                                </p>
                            )}
                        </div>

                        {/* Order Details */}
                        {orderDetails && (
                            <div className="bg-gray-50 p-4 rounded-lg">
                                <h3 className="text-lg font-medium text-gray-900 mb-3">
                                    Detail Purchase Order
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm text-gray-600">
                                            Nomor PO
                                        </p>
                                        <p className="font-semibold">
                                            {orderDetails.purchase_number}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">
                                            Supplier
                                        </p>
                                        <p className="font-semibold">
                                            {orderDetails.supplier}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">
                                            Total Order
                                        </p>
                                        <p className="font-semibold text-blue-600">
                                            {formatCurrency(orderDetails.total)}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">
                                            Uang Muka
                                        </p>
                                        <p className="font-semibold text-purple-600">
                                            {formatCurrency(
                                                orderDetails.down_payment || 0
                                            )}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">
                                            Total Dibayar (Cicilan)
                                        </p>
                                        <p className="font-semibold text-green-600">
                                            {formatCurrency(
                                                orderDetails.paid_amount
                                            )}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">
                                            Sisa Tagihan
                                        </p>
                                        <p className="font-bold text-red-600 text-lg">
                                            {formatCurrency(
                                                orderDetails.remaining_amount
                                            )}
                                        </p>
                                    </div>
                                </div>

                                {/* Payment Summary */}
                                <div className="mt-4 pt-4 border-t border-gray-200">
                                    <p className="text-sm text-gray-700 mb-2">
                                        <span className="font-medium">
                                            Perhitungan:
                                        </span>
                                    </p>
                                    <div className="text-sm space-y-1">
                                        <div className="flex justify-between">
                                            <span>Total Order:</span>
                                            <span>
                                                {formatCurrency(
                                                    orderDetails.total
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Uang Muka:</span>
                                            <span>
                                                -{" "}
                                                {formatCurrency(
                                                    orderDetails.down_payment ||
                                                        0
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Cicilan Terbayar:</span>
                                            <span>
                                                -{" "}
                                                {formatCurrency(
                                                    orderDetails.paid_amount
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex justify-between font-semibold border-t pt-1">
                                            <span>Sisa Tagihan:</span>
                                            <span className="text-red-600">
                                                {formatCurrency(
                                                    orderDetails.remaining_amount
                                                )}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Payment Date */}
                        <TextField
                            label="Tanggal Pembayaran"
                            name="payment_date"
                            type="date"
                            value={paymentDate}
                            onChange={(e) => setPaymentDate(e.target.value)}
                            required={true}
                            error={errors.payment_date}
                        />

                        {/* Payment Amount */}
                        <NumberField
                            label="Jumlah Pembayaran"
                            name="amount"
                            value={paymentAmount}
                            onChange={setPaymentAmount}
                            placeholder="Masukkan jumlah pembayaran"
                            required={true}
                            error={errors.amount}
                            min={0}
                            max={orderDetails?.remaining_amount}
                        />

                        {orderDetails && paymentAmount && (
                            <div className="bg-blue-50 p-4 rounded-lg">
                                <p className="text-sm text-blue-600">
                                    Sisa tagihan setelah pembayaran:{" "}
                                    <span className="font-semibold">
                                        {formatCurrency(
                                            orderDetails.remaining_amount -
                                                parseFloat(paymentAmount || 0)
                                        )}
                                    </span>
                                </p>
                            </div>
                        )}

                        {/* Submit Buttons */}
                        <div className="flex justify-end space-x-4 pt-6 border-t">
                            <button
                                type="button"
                                onClick={() =>
                                    history.push("/purchase-payment")
                                }
                                className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                disabled={loading}
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                                disabled={loading}
                            >
                                {loading
                                    ? "Menyimpan..."
                                    : isEdit
                                    ? "Update Pembayaran"
                                    : "Simpan Pembayaran"}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Fragment>
    );
};

export default CreatePurchasePayment;
