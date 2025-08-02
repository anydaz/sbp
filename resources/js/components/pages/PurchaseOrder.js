import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";
import ModalInfo from "components/shared/ModalInfo.js";
import TextField from "components/shared/TextField.js";
import useDebounce from "hooks/useDebounce.js";

const PurchaseOrder = () => {
    const [purchaseOrders, setPurchaseOrders] = useState([]);
    const [showModalInfo, setShowModalInfo] = useState(false);
    const [search, setSearch] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        purchase: null,
    });
    const history = useHistory();

    const [modalImport, setModalImport] = useState({
        show: false,
        type: null,
    });
    const [files, setFiles] = useState(null);

    const getPurchaseOrders = async () => {
        const response = await Api("/api/purchase_orders", {
            search: search,
        });
        if (response) {
            setCurrentPage(response.data.current_page);
            setLastPage(response.data.last_page);
            setPurchaseOrders(response.data.data);
        }
    };

    const columns = React.useMemo(
        () => [
            { Header: "nomor pembelian", accessor: "purchase_number" },
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
                Header: "diskon nota",
                accessor: (data) =>
                    data.purchase_discount.toLocaleString("id-ID"),
            },
            {
                Header: "final",
                accessor: (data) => {
                    const total = data.details.reduce(
                        (accum, detail) =>
                            parseFloat(accum) + parseFloat(detail.subtotal),
                        0
                    );
                    return (
                        total - (data.purchase_discount || 0)
                    ).toLocaleString("id-ID");
                },
            },
            {
                Header: "aksi",
                Cell: (value) => (
                    <div className="flex justify-center">
                        {!value?.row?.original?.delivery_notes?.length > 0 && (
                            <Fragment>
                                <IconButton
                                    onClick={() =>
                                        history.push(
                                            `/purchase-order/edit/${value.row.original.id}`
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
                                            purchase: value.row.original,
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

    const tableInstance = useTable(
        { columns, data: purchaseOrders },
        usePagination
    );

    const goToPage = async (page) => {
        const response = await Api("api/purchase_orders", { page: page });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setPurchaseOrders(response.data.data);
    };

    const handleDeletePurchase = async () => {
        const response = await Api(
            `api/purchase_orders/${modalDelete.purchase.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, purchase: null });
            getPurchaseOrders();
        }
    };

    const handleFile = (e) => {
        let files = e.target.files;
        setFiles(files);
    };

    const handleUploadFile = async () => {
        let formData = new FormData();
        formData.append("file", files[0]);

        const response = await Api(
            "api/purchase_order/import",
            formData,
            "POST"
        );
        console.log(response);
        if (response.status === 200) {
            setModalImport({ show: false, type: null });
            setShowModalInfo(true);
            setTimeout(() => {
                setShowModalInfo(false);
                getPurchaseOrders();
            }, 1000);
        } else {
            setFiles(null);
        }
    };

    useEffect(() => {
        getPurchaseOrders();
    }, [search]);

    const debounceInput = useDebounce((text) => setSearch(text), 500);

    return (
        <Fragment>
            <Header
                title="Pembelian"
                action={{
                    title: "Buat Pembelian Baru",
                    onClick: () => history.push("/purchase-order/create"),
                }}
                secondaryAction={{
                    title: "Import Pembelian",
                    onClick: () =>
                        setModalImport({
                            show: true,
                            type: "new",
                        }),
                    icon: "Download",
                }}
            />
            <div className="p-4">
                <div className="flex items-center">
                    <div className="w-1/3">
                        <TextField
                            label="Cari berdasarkan Supplier"
                            onChange={(e) => debounceInput(e.target.value)}
                            customClass="mr-1"
                        />
                    </div>
                </div>
                <Table
                    currentPage={currentPage}
                    lastPage={lastPage}
                    goToPage={goToPage}
                    tableInstance={tableInstance}
                />
            </div>
            {modalDelete.show && (
                <Modal
                    onSubmit={handleDeletePurchase}
                    onCancel={() =>
                        setModalDelete({ show: false, purchase: null })
                    }
                >
                    Apakah anda yakin akan menghapus pembelian #
                    {modalDelete.purchase.id} ini?
                </Modal>
            )}
            {modalImport.show && (
                <Modal
                    onSubmit={handleUploadFile}
                    onCancel={() => setModalImport({ show: false, type: null })}
                >
                    <input
                        type="file"
                        name="file"
                        onChange={(e) => handleFile(e)}
                    />
                </Modal>
            )}
            {showModalInfo && <ModalInfo />}
        </Fragment>
    );
};

export default PurchaseOrder;
