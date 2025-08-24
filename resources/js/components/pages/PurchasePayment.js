import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";

const PurchasePayment = () => {
    const [purchasePayments, setPurchasePayments] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        payment: null,
    });
    const history = useHistory();

    const getPurchasePayments = async () => {
        const response = await Api("/api/purchase_payments");
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPurchasePayments(response.data.data);
    };

    const columns = React.useMemo(
        () => [
            { Header: "ID", accessor: "id" },
            {
                Header: "Nomor PO",
                accessor: "purchase_order.purchase_number",
                Cell: ({ value }) => value || "-",
            },
            {
                Header: "Supplier",
                accessor: "purchase_order.supplier",
                Cell: ({ value }) => value || "-",
            },
            {
                Header: "Tanggal Bayar",
                accessor: (data) => {
                    return new Date(data.payment_date).toLocaleDateString(
                        "id-ID"
                    );
                },
            },
            {
                Header: "Jumlah Bayar",
                accessor: (data) => {
                    return data.amount.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    });
                },
                justifyText: "text-right",
            },
            {
                Header: "Aksi",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/purchase-payment/edit/${value.row.original.id}`
                                )
                            }
                            icon="Edit"
                            iconClass="text-white text-sm"
                            customClass="bg-yellow-400 hover:bg-yellow-500 mr-1"
                        />
                        <IconButton
                            onClick={() =>
                                setModalDelete({
                                    show: true,
                                    payment: value.row.original,
                                })
                            }
                            icon="Trash2"
                            iconClass="text-white text-sm"
                            customClass="bg-red-400 hover:bg-red-500 ml-1"
                        />
                    </div>
                ),
                id: "action-button",
            },
        ],
        []
    );

    const tableInstance = useTable(
        { columns, data: purchasePayments },
        usePagination
    );

    const goToPage = async (page) => {
        const response = await Api("api/purchase_payments", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPurchasePayments(response.data.data);
    };

    const handleDeletePayment = async () => {
        const response = await Api(
            `api/purchase_payments/${modalDelete.payment.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, payment: null });
            getPurchasePayments();
        }
    };

    useEffect(() => {
        getPurchasePayments();
    }, []);

    return (
        <Fragment>
            <Header
                title="Pembayaran Pembelian"
                action={{
                    title: "Buat Pembayaran Baru",
                    onClick: () => history.push("/purchase-payment/create"),
                }}
            />
            <div className="p-4">
                <Table
                    currentPage={currentPage}
                    lastPage={lastPage}
                    goToPage={goToPage}
                    tableInstance={tableInstance}
                />
            </div>
            {modalDelete.show && (
                <Modal
                    onSubmit={handleDeletePayment}
                    onCancel={() =>
                        setModalDelete({ show: false, payment: null })
                    }
                >
                    Apakah anda yakin akan menghapus pembayaran #
                    {modalDelete.payment.id} ini?
                </Modal>
            )}
        </Fragment>
    );
};

export default PurchasePayment;
