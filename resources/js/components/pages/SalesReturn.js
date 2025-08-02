import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";

const SalesReturn = () => {
    const [salesReturns, setSalesReturns] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        return: null,
    });
    const history = useHistory();

    const getSalesReturns = async () => {
        const response = await Api("/api/sales_returns");
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setSalesReturns(response.data.data);
    };

    const columns = React.useMemo(
        () => [
            { Header: "id", accessor: "id" },
            { Header: "customer", accessor: "customer" },
            {
                Header: "tanggal",
                accessor: (data) => {
                    return new Date(data.created_at).toLocaleDateString(
                        "id-ID"
                    );
                },
            },
            {
                Header: "aksi",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/sales-return/edit/${value.row.original.id}`
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
        { columns, data: salesReturns },
        usePagination
    );

    const goToPage = async (page) => {
        const response = await Api("api/sales_returns", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setSalesReturns(response.data.data);
    };

    const handleDeleteReturn = async () => {
        const response = await Api(
            `api/sales_returns/${modalDelete.return.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, return: null });
            getSalesReturns();
        }
    };

    useEffect(() => {
        getSalesReturns();
    }, []);

    return (
        <Fragment>
            <Header
                title="Retur Penjualan"
                action={{
                    title: "Buat Retur Penjualan Baru",
                    onClick: () => history.push("/sales-return/create"),
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

export default SalesReturn;
