import React, { Fragment, useState, useEffect } from "react";
import Api from "../../api.js";
import Table from "../shared/Table.js";
import TextField from "../shared/TextField.js";
import Header from "../shared/Header.js";
import { useTable, usePagination } from "react-table";
import { useHistory } from "react-router-dom";
import useDebounce from "hooks/useDebounce.js";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";
import fileDownload from "js-file-download";
import Dropdown from "components/shared/Dropdown.js";
import Button, { SecondaryButton } from "../shared/Button.js";
import ModalInfo from "components/shared/ModalInfo.js";

const SORT_BY_LIST = [
    { display: "harga", column: "price" },
    { display: "nama", column: "name" },
    { display: "terakhir di update", column: "last_edited" },
];

const Product = () => {
    const [showModalInfo, setShowModalInfo] = useState(false);
    const [search, setSearch] = useState(null);
    const [products, setProducts] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalImport, setModalImport] = useState({
        show: false,
        type: null,
    });
    const [modalDelete, setModalDelete] = useState({
        show: false,
        product: null,
    });
    const [files, setFiles] = useState(null);
    const [sortBy, setSortBy] = useState({ display: "harga", column: "price" });
    const history = useHistory();

    const getProducts = async () => {
        const response = await Api("/api/products", {
            search: search ? search : null,
            sort_by: sortBy.column,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setProducts(response.data.data);
    };

    const goToPage = async (page) => {
        const response = await Api("api/products", {
            search: search ? search : null,
            sort_by: sortBy.column,
            page: page,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setProducts(response.data.data);
    };

    const handleFile = (e) => {
        let files = e.target.files;
        setFiles(files);
    };

    const handleUploadFile = async () => {
        let formData = new FormData();
        formData.append("file", files[0]);

        let urls = "";
        if (modalImport.type === "new") {
            urls = "api/products/new/import";
        } else if (modalImport.type === "bulk_update_price") {
            urls = "api/products/bulk_update_price";
        } else {
            urls = "api/products/import";
        }

        // const urls =
        //     modalImport.type === "new"
        //         ? "api/products/new/import"
        //         : "api/products/import";

        const response = await Api(urls, formData, "POST");
        if (response.status === 200) {
            setShowModalInfo(true);
            setTimeout(() => {
                setModalImport({ show: false, type: null });
                getProducts();
                setShowModalInfo(false);
            }, 1000);
        } else {
            setFiles(null);
        }
    };

    const handleDeleteProduct = async () => {
        const response = await Api(
            `api/products/${modalDelete.product.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, product: null });
            getProducts();
        }
    };

    const handleExport = async () => {
        const token = localStorage.getItem("token");
        const response = await axios({
            method: "post",
            url: "api/products/export",
            responseType: "blob",
            headers: {
                Accept: "multipart/form-data",
                Authorization: "Bearer " + token,
            },
        });
        fileDownload(response.data, "products.xlsx");
    };

    const print = async (id) => {
        // const response = await Api(`api/products/${id}/print`)
        // console.log(response);
        const token = localStorage.getItem("token");
        const response = await axios({
            method: "get",
            url: `api/products/${id}/print`,
            responseType: "blob",
            headers: {
                Accept: "multipart/form-data",
                Authorization: "Bearer " + token,
            },
        });

        console.log("response", response);
        var blob = new Blob([response.data], { type: "application/pdf" });
        var blobURL = URL.createObjectURL(blob);
        window.open(blobURL);
    };

    useEffect(() => {
        getProducts();
    }, [search, sortBy]);

    const columns = React.useMemo(
        () => [
            { Header: "Kode", accessor: "code" },
            { Header: "Nama", accessor: "name" },
            { Header: "Category", accessor: "category.name" },
            {
                Header: "Harga",
                accessor: (data) =>
                    parseFloat(data.price).toLocaleString("id-ID"),
                justifyText: "text-right",
            },
            {
                Header: "COGS",
                accessor: (data) =>
                    parseFloat(data.cogs).toLocaleString("id-ID"),
                justifyText: "text-right",
            },
            {
                Header: "Qty",
                accessor: (data) =>
                    parseFloat(data.quantity).toLocaleString("id-ID"),
                justifyText: "text-right",
            },
            {
                Header: "Last Update",
                accessor: (data) => {
                    return new Date(data.last_edited).toLocaleDateString(
                        "id-ID"
                    );
                },
            },
            {
                Header: "Action",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/product/edit/${value.row.original.id}`
                                )
                            }
                            icon="Edit"
                            iconClass="text-white text-sm"
                            customClass="bg-yellow-400 hover:bg-yellow-500 mr-1"
                        />
                        <IconButton
                            onClick={() => print(value.row.original.id)}
                            icon="Printer"
                            iconClass="text-white text-sm"
                            customClass="bg-blue-400 hover:bg-blue-500 mr-1"
                        />
                        <IconButton
                            onClick={() =>
                                setModalDelete({
                                    show: true,
                                    product: value.row.original,
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

    const tableInstance = useTable({ columns, data: products }, usePagination);
    const debounceInput = useDebounce((text) => setSearch(text), 500);

    return (
        <Fragment>
            <Header
                title="Produk"
                customActionsComponent={
                    <div>
                        <SecondaryButton
                            text="Export Product"
                            onClick={() => handleExport()}
                            icon="Upload"
                            customClass="mr-2 bg-white"
                        />
                        <SecondaryButton
                            text="Import New Product"
                            onClick={() =>
                                setModalImport({
                                    show: true,
                                    type: "new",
                                })
                            }
                            icon="Download"
                            customClass="mr-2 bg-white"
                        />
                        <SecondaryButton
                            text="Update Harga Jual"
                            onClick={() =>
                                setModalImport({
                                    show: true,
                                    type: "bulk_update_price",
                                })
                            }
                            icon="DollarSign"
                            customClass="mr-2 bg-white"
                        />
                        <Button
                            text="Buat Product Baru"
                            onClick={() => history.push("/product/create")}
                        />
                    </div>
                }
            />
            <div className="p-4">
                <div className="flex">
                    <div className="w-1/3">
                        <TextField
                            label="Cari Product"
                            onChange={(e) => debounceInput(e.target.value)}
                            customClass="mr-1"
                        />
                    </div>
                    <div className="w-1/3">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            {" "}
                            Urut Berdasarkan
                        </label>
                        <Dropdown
                            selected={sortBy.display}
                            options={SORT_BY_LIST}
                            onChange={(sortValue) => {
                                setSortBy(sortValue);
                            }}
                            customClass="mr-1 ml-1"
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
            {modalDelete.show && (
                <Modal
                    onSubmit={handleDeleteProduct}
                    onCancel={() =>
                        setModalDelete({ show: false, product: null })
                    }
                >
                    Apakah anda yakin akan menghapus product ini{" "}
                    {modalDelete.product.name}
                </Modal>
            )}
            {showModalInfo && <ModalInfo />}
        </Fragment>
    );
};

export default Product;
