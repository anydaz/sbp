import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";

const PurchaseReturn = () => {
    const [purchaseReturns, setPurchaseReturns] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        return: null,
    });
    const history = useHistory();

    const getPurchaseReturns = async () => {
        const response = await Api("/api/purchase_returns");
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPurchaseReturns(response.data.data);
    };

    const columns = React.useMemo(
        () => [
            { Header: "id", accessor: "id" },
            { Header: "user", accessor: "user.name" },
            {
                Header: "tanggal",
                accessor: (data) => {
                    return new Date(data.created_at).toLocaleDateString(
                        "id-ID"
                    );
                },
            },
            {
                Header: "total",
                accessor: (data) => {
                    return data.details
                        .reduce(
                            (accum, detail) =>
                                accum + parseFloat(detail.subtotal),
                            0
                        )
                        .toLocaleString("id-ID");
                },
                justifyText: "text-right",
            },
            {
                Header: "aksi",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/purchase-return/edit/${value.row.original.id}`
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
                                    return: value.row.original,
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
        { columns, data: purchaseReturns },
        usePagination
    );

    const goToPage = async (page) => {
        const response = await Api("api/purchase_returns", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPurchaseReturns(response.data.data);
    };

    const handleDeleteReturn = async () => {
        const response = await Api(
            `api/purchase_returns/${modalDelete.return.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, return: null });
            getPurchaseReturns();
        }
    };

    useEffect(() => {
        getPurchaseReturns();
    }, []);

    return (
        <Fragment>
            <Header
                title="Retur Pembelian"
                action={{
                    title: "Buat Retur Pembelian Baru",
                    onClick: () => history.push("/purchase-return/create"),
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
                    onSubmit={handleDeleteReturn}
                    onCancel={() =>
                        setModalDelete({ show: false, return: null })
                    }
                >
                    Apakah anda yakin akan menghapus return #
                    {modalDelete.return.id} ini?
                </Modal>
            )}
        </Fragment>
    );
};

export default PurchaseReturn;
