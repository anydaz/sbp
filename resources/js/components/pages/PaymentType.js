import React, { Fragment, useState, useEffect } from "react";
import Api from "../../api.js";
import Table from "../shared/Table.js";
import Header from "../shared/Header.js";
import { useTable, usePagination } from "react-table";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";

const PaymentType = () => {
    const [search, setSearch] = useState(null);
    const [paymentTypes, setPaymentTypes] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        paymentType: null,
    });
    const history = useHistory();

    const getPaymentTypes = async () => {
        const response = await Api("/api/payment_types");
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPaymentTypes(response.data.data);
    };

    const handleDeletePaymentType = async () => {
        const response = await Api(
            `api/payment_types/${modalDelete.paymentType.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, paymentType: null });
            getPaymentTypes();
        }
    };

    const goToPage = async (page) => {
        const response = await Api("api/payment_types", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPaymentTypes(response.data.data);
    };

    useEffect(() => {
        getPaymentTypes();
    }, [search]);

    const columns = React.useMemo(
        () => [
            { Header: "Name", accessor: "name" },
            { Header: "Code", accessor: "code" },
            {
                Header: "Action",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/payment_type/edit/${value.row.original.id}`
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
                                    paymentType: value.row.original,
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
        { columns, data: paymentTypes },
        usePagination
    );

    return (
        <Fragment>
            <Header
                title="Tipe Pembayaran"
                action={{
                    title: "Buat Tipe Pembayaran",
                    onClick: () => history.push("/payment_type/create"),
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
                    onSubmit={handleDeletePaymentType}
                    onCancel={() =>
                        setModalDelete({ show: false, paymentType: null })
                    }
                >
                    Apakah anda yakin akan menghapus tipe pembayaran ini{" "}
                    {modalDelete.paymentType.name}
                </Modal>
            )}
        </Fragment>
    );
};

export default PaymentType;
