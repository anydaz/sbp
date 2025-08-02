import React, { Fragment, useState, useEffect } from "react";
import Api from "../../api.js";
import Table from "../shared/Table.js";
import TextField from "../shared/TextField.js";
import Header from "../shared/Header.js";
import { useTable, usePagination } from "react-table";
import useDebounce from "hooks/useDebounce.js";
import Barcode from "react-barcode";

const Product = () => {
    const [search, setSearch] = useState(null);
    const [products, setProducts] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    const getProducts = async () => {
        const response = await Api("/api/products", {
            search: search ? search : null,
            limit: 1,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setProducts(response.data.data);
    };

    const goToPage = async (page) => {
        const response = await Api("api/products", {
            search: search ? search : null,
            limit: 1,
            page: page,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setProducts(response.data.data);
    };

    useEffect(() => {
        getProducts();
    }, [search]);

    const columns = React.useMemo(
        () => [
            { Header: "Nama", accessor: "name" },
            { Header: "Alias", accessor: "alias" },
            { Header: "Kode Efisiensi", accessor: "efficiency_code" },
            {
                Header: "Harga",
                accessor: (data) =>
                    parseFloat(data.price).toLocaleString("id-ID"),
                justifyText: "text-right",
            },
            {
                Header: "Qty",
                accessor: (data) =>
                    parseFloat(data.quantity).toLocaleString("id-ID"),
                justifyText: "text-right",
            },
            {
                Header: "Barcode",
                Cell: (value) => <Barcode value={value.row.original.barcode} />,
            },
        ],
        []
    );

    const tableInstance = useTable({ columns, data: products }, usePagination);
    const debounceInput = useDebounce((text) => setSearch(text), 500);

    return (
        <Fragment>
            <Header title="Barcode Produk" />
            <div className="p-4">
                <div className="flex">
                    <div className="w-1/3">
                        <TextField
                            label="Cari Product"
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
        </Fragment>
    );
};

export default Product;
