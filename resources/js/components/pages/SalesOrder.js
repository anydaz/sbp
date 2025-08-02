import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";
import Table from "../shared/Table.js";
import { useHistory } from "react-router-dom";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";
import TextField from "components/shared/TextField.js";
import useDebounce from "hooks/useDebounce.js";
import Chip from "../shared/Chip.js";

const SalesOrder = () => {
    const [salesOrders, setSalesOrders] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [status, setStatus] = useState("active");
    const [modalDelete, setModalDelete] = useState({
        show: false,
        sales: null,
    });
    const [modalPrint, setModalPrint] = useState({
        show: false,
        sales: null,
        selectedType: null,
    });
    const [search, setSearch] = useState("");
    const history = useHistory();
    const debounceInput = useDebounce((text) => setSearch(text), 500);

    const getSalesOrders = async () => {
        const response = await Api("/api/sales_orders", {
            search: search ? search : null,
            is_pending: status === "pending" ? 1 : 0,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setSalesOrders(response.data.data);
    };

    const columns = React.useMemo(
        () => [
            {
                Header: "No Sales",
                accessor: (data) => data.sales_number || "--",
            },
            { Header: "pelanggan", accessor: "customer.name" },
            {
                Header: "tanggal",
                accessor: (data) => {
                    return new Date(data.date).toLocaleDateString("id-ID");
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
                accessor: (data) => data.sales_discount.toLocaleString("id-ID"),
            },
            {
                Header: "retur",
                accessor: (data) => data.total_return.toLocaleString("id-ID"),
            },
            {
                Header: "final",
                accessor: (data) => {
                    const total = data.details.reduce(
                        (accum, detail) => accum + parseFloat(detail.subtotal),
                        0
                    );
                    return (
                        total -
                        data.total_return -
                        (data.sales_discount || 0)
                    ).toLocaleString("id-ID");
                },
            },
            {
                Header: "status",
                accessor: (data) => {
                    return data.is_pending ? "Pending" : "Aktif";
                },
            },
            {
                Header: "aksi",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() => {
                                setModalPrint({
                                    show: true,
                                    selectedType: "sales",
                                    sales: value.row.original,
                                });
                            }}
                            icon="Printer"
                            iconClass="text-white text-sm"
                            customClass="bg-green-400 hover:bg-green-500 mr-1"
                        />
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/sales-order/edit/${value.row.original.id}`
                                )
                            }
                            icon="Edit"
                            iconClass="text-white text-sm"
                            customClass="bg-yellow-400 hover:bg-yellow-500 ml-1 mr-1"
                        />
                        <IconButton
                            onClick={() =>
                                setModalDelete({
                                    show: true,
                                    sales: value.row.original,
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
        { columns, data: salesOrders },
        usePagination
    );

    const goToPage = async (page) => {
        const response = await Api("api/sales_orders", {
            search: search ? search : null,
            page: page,
            is_pending: status === "pending" ? 1 : 0,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setSalesOrders(response.data.data);
    };

    const handleDeleteSales = async () => {
        const response = await Api(
            `api/sales_orders/${modalDelete.sales.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, sales: null });
            getSalesOrders();
        }
    };

    const handlePrintSales = async () => {
        // window.open(`sales_orders/${id}/print`, '_blank');
        const id = modalPrint.sales.id;
        const type = modalPrint.selectedType;
        const token = localStorage.getItem("token");
        const response = await axios({
            method: "get",
            url: `api/sales_orders/${id}/print`,
            responseType: "blob",
            headers: {
                Accept: "multipart/form-data",
                Authorization: "Bearer " + token,
            },
            params: { type: type },
        });

        // fileDownload(response.data, 'so.pdf');

        console.log("response", response);
        var blob = new Blob([response.data], { type: "application/pdf" });
        var blobURL = URL.createObjectURL(blob);
        window.open(blobURL);
    };

    useEffect(() => {
        getSalesOrders();
    }, [search, status]);

    return (
        <Fragment>
            <Header
                title="Penjualan"
                action={{
                    title: "Buat Penjualan Baru",
                    onClick: () => history.push("/sales-order/create"),
                }}
            />
            <div className="p-4">
                <div className="flex justify-between items-center ">
                    <div className="w-1/3">
                        <TextField
                            label="Cari berdasarkan No Sales / Customer"
                            onChange={(e) => debounceInput(e.target.value)}
                            customClass="mr-1"
                        />
                    </div>
                    <Chip
                        items={[
                            { id: "active", title: "Aktif" },
                            { id: "pending", title: "Pending" },
                        ]}
                        selectedId={status}
                        handleClick={(id) => {
                            setStatus(id);
                        }}
                    />
                </div>
                <Table
                    currentPage={currentPage}
                    lastPage={lastPage}
                    goToPage={goToPage}
                    tableInstance={tableInstance}
                />
            </div>
            {modalPrint.show && (
                <Modal
                    onSubmit={handlePrintSales}
                    onCancel={() => setModalPrint({ show: false, sales: null })}
                >
                    <div class="block">
                        <span class="text-gray-700">Jenis Print</span>
                        <div class="mt-2">
                            <div>
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        class="appearance-none
										h-4 w-4 border rounded-full border-gray-300
										checked:border-transparent checked:bg-checked-svg checked:bg-indigo-600
										focus:ring-2 focus:ring-indigo-600
										focus:outline-none focus:ring-offset-2"
                                        name="radio"
                                        value="1"
                                        onChange={() =>
                                            setModalPrint({
                                                ...modalPrint,
                                                selectedType: "sales",
                                            })
                                        }
                                        checked={
                                            modalPrint.selectedType == "sales"
                                        }
                                    />
                                    <span class="ml-2">Print SO Besar</span>
                                </label>
                            </div>
                            {/* <div>
								<label class="inline-flex items-center">
									<input
										type="radio"
										class="appearance-none
										h-4 w-4 border rounded-full border-gray-300
										checked:border-transparent checked:bg-checked-svg checked:bg-green-500
										focus:ring-2 focus:ring-green-500
										focus:outline-none focus:ring-offset-2"
										name="radio"
										value="2"
										onChange={() => setModalPrint({...modalPrint, selectedType: "checklist"})}
										checked={modalPrint.selectedType == "checklist"}
									/>
									<span class="ml-2">Print Checklist</span>
								</label>
							</div> */}
                            {/* if there is no customer (customer tanpa nama with id: 0), do not show options print surat jalan */}
                            {modalPrint.sales.customer_id != 0 && (
                                <div>
                                    <label class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            class="appearance-none
											h-4 w-4 border rounded-full border-gray-300
											checked:border-transparent checked:bg-checked-svg checked:bg-pink-600
											focus:ring-2 focus:ring-pink-600
											focus:outline-none focus:ring-offset-2"
                                            name="radio"
                                            value="3"
                                            onChange={() =>
                                                setModalPrint({
                                                    ...modalPrint,
                                                    selectedType: "delivery",
                                                })
                                            }
                                            checked={
                                                modalPrint.selectedType ==
                                                "delivery"
                                            }
                                        />
                                        <span class="ml-2">
                                            Print Surat Jalan
                                        </span>
                                    </label>
                                </div>
                            )}
                        </div>
                    </div>
                </Modal>
            )}
            {modalDelete.show && (
                <Modal
                    onSubmit={handleDeleteSales}
                    onCancel={() =>
                        setModalDelete({ show: false, sales: null })
                    }
                >
                    Apakah anda yakin akan menghapus penjualan #
                    {modalDelete.sales.id} ini?
                </Modal>
            )}
        </Fragment>
    );
};

export default SalesOrder;
