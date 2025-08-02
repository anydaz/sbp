import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";

const DeliveryNotes = () => {
    const [deliveryNotes, setDeliveryNotes] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        deliveryNotes: null,
    });
    const history = useHistory();

    const getDeliveryNotes = async () => {
        const response = await Api("/api/delivery_notes");
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setDeliveryNotes(response.data.data);
    };

    const columns = React.useMemo(
        () => [
            { Header: "id", accessor: "id" },
            {
                Header: "id pembelian",
                accessor: (data) => {
                    return "#" + data.purchase_order_id;
                },
            },
            { Header: "supplier", accessor: "purchase_order.supplier" },
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
                                    `/delivery-note/edit/${value.row.original.id}`
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
                                    deliveryNotes: value.row.original,
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
        { columns, data: deliveryNotes },
        usePagination
    );

    const goToPage = async (page) => {
        const response = await Api("api/delivery_notes", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setDeliveryNotes(response.data.data);
    };

    const handleDeleteDeliveryNotes = async () => {
        const response = await Api(
            `api/delivery_notes/${modalDelete.deliveryNotes.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, deliveryNotes: null });
            getDeliveryNotes();
        }
    };

    useEffect(() => {
        getDeliveryNotes();
    }, []);

    return (
        <Fragment>
            <Header
                title="Bukti Penerimaan"
                action={{
                    title: "Buat Bukti Penerimaan Baru",
                    onClick: () => history.push("/delivery-note/create"),
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
                    onSubmit={handleDeleteDeliveryNotes}
                    onCancel={() =>
                        setModalDelete({ show: false, sales: null })
                    }
                >
                    Apakah anda yakin akan menghapus bukti penerimaan #
                    {modalDelete.deliveryNotes.id} ini?
                </Modal>
            )}
        </Fragment>
    );
};

export default DeliveryNotes;
