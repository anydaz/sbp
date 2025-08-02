import React, { Fragment, useEffect, useState } from "react";
import { usePagination, useTable } from "react-table";
import Api from "../../api.js";
import Header from "../shared/Header.js";
import Table from "../shared/Table.js";
import TextField from "../shared/TextField.js";
import { IconButton } from "../shared/Button.js";
import Modal from "components/shared/Modal.js";
import { useHistory } from "react-router-dom";
import useDebounce from "hooks/useDebounce.js";

const ProductCategory = () => {
    const [search, setSearch] = useState(null);
    const [categories, setCategories] = useState([]);
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
    const history = useHistory();

    const getCategories = async () => {
        const response = await Api("/api/product_categories", {
            search: search ? search : null,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setCategories(response.data.data);
    };

    const goToPage = async (page) => {
        const response = await Api("api/product_categories", {
            page: page,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setProducts(response.data.data);
    };

    const handleDeleteCategory = async () => {
        const response = await Api(
            `api/product_categories/${modalDelete.product.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, product: null });
            getCategories();
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
            "api/product_categories/import",
            formData,
            "POST"
        );
        if (response) {
            setModalImport({ show: false, type: null });
            getCategories();
        }
    };

    useEffect(() => {
        getCategories();
    }, [search]);

    const columns = React.useMemo(
        () => [
            { Header: "Nama", accessor: "name" },
            { Header: "Kode", accessor: "code" },
            {
                Header: "Action",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/product-category/edit/${value.row.original.id}`
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

    const tableInstance = useTable(
        { columns, data: categories },
        usePagination
    );

    const debounceInput = useDebounce((text) => setSearch(text), 500);

    return (
        <Fragment>
            <Header
                title="Kategori Produk"
                action={{
                    title: "Buat Kategori",
                    onClick: () => history.push("/product-category/create"),
                }}
                secondaryAction={{
                    title: "Import Kategori Produk",
                    onClick: () =>
                        setModalImport({
                            show: true,
                            type: "new",
                        }),
                    icon: "Download",
                }}
            />
            <div className="p-4">
                <div className="flex">
                    <div className="w-1/3">
                        <TextField
                            label="Cari Kategori"
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
                    onSubmit={handleDeleteCategory}
                    onCancel={() =>
                        setModalDelete({ show: false, product: null })
                    }
                >
                    Apakah anda yakin akan menghapus product ini{" "}
                    {modalDelete.product.name}
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
        </Fragment>
    );
};

export default ProductCategory;
