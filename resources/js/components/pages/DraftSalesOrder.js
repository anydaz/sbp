import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { getStartOfDay, getEndOfDay } from "helper/date-helper";
import { IconButton } from "components/shared/Button.js";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

const DraftSalesOrder = () => {
    const [drafts, setDrafts] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        draft: null,
    });
    const [date, setDate] = useState(new Date());
    const history = useHistory();

    const getDraftOrders = async () => {
        const start_date = getStartOfDay(date);
        const end_date = getEndOfDay(date);

        const response = await Api("/api/draft_sales_orders", {
            start_date: start_date.toISOString(),
            end_date: end_date.toISOString(),
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setDrafts(response.data.data);
    };

    const columns = React.useMemo(
        () => [
            { Header: "id", accessor: "id" },
            { Header: "pelanggan", accessor: "customer.name" },
            { Header: "sales", accessor: "user.name" },
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
                                parseFloat(accum) + parseFloat(detail.subtotal),
                            0
                        )
                        .toLocaleString("id-ID");
                },
                justifyText: "text-right",
            },
            {
                Header: "Aksi",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                handlePrintDraft(value.row.original.id)
                            }
                            icon="Printer"
                            iconClass="text-white text-sm"
                            customClass="bg-green-400 hover:bg-green-500 mr-1"
                        />
                        {!value.row.original?.sales_order && (
                            <Fragment>
                                <IconButton
                                    onClick={() =>
                                        history.push(
                                            `/draft-sales-order/edit/${value.row.original.id}`
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
                                            draft: value.row.original,
                                        })
                                    }
                                    icon="Trash2"
                                    iconClass="text-white text-sm"
                                    customClass="bg-red-400 hover:bg-red-500 ml-1"
                                />
                            </Fragment>
                        )}
                    </div>
                ),
                id: "action-button",
            },
        ],
        []
    );

    const tableInstance = useTable({ columns, data: drafts }, usePagination);

    const goToPage = async (page) => {
        const response = await Api("api/draft_sales_orders", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setDrafts(response.data.data);
    };

    const handleDeleteDraft = async () => {
        const response = await Api(
            `api/draft_sales_orders/${modalDelete.draft.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, draft: null });
            getDraftOrders();
        }
    };

    const handlePrintDraft = async (id) => {
        const token = localStorage.getItem("token");
        const response = await axios({
            method: "get",
            url: `api/draft_sales_orders/${id}/print`,
            responseType: "blob",
            headers: {
                Accept: "multipart/form-data",
                Authorization: "Bearer " + token,
            },
        });

        var blob = new Blob([response.data], { type: "application/pdf" });
        var blobURL = URL.createObjectURL(blob);
        window.open(blobURL);
    };

    useEffect(() => {
        getDraftOrders();
    }, [date]);

    return (
        <Fragment>
            <Header
                title="Draft Sales Order"
                action={{
                    title: "Create New Draft Sales Order",
                    onClick: () => history.push("/draft-sales-order/create"),
                }}
            />
            <div className="p-4">
                <DatePicker
                    todayButton="Today"
                    showMonthDropdown
                    showYearDropdown
                    selected={date}
                    onChange={(date) => setDate(date)}
                    className="border rounded-lg mb-4 p-1"
                />
                <Table
                    currentPage={currentPage}
                    lastPage={lastPage}
                    goToPage={goToPage}
                    tableInstance={tableInstance}
                />
            </div>
            {modalDelete.show && (
                <Modal
                    onSubmit={handleDeleteDraft}
                    onCancel={() =>
                        setModalDelete({ show: false, product: null })
                    }
                >
                    Apakah anda yakin akan menghapus draft #
                    {modalDelete.draft.id} ini?
                </Modal>
            )}
        </Fragment>
    );
};

export default DraftSalesOrder;
