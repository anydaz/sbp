import React, { useState, useEffect } from "react";
import Api from "../../api.js";
import { useParams, useHistory } from "react-router-dom";
import Header from "../shared/Header";
import Table from "../shared/Table";
import { useTable, usePagination } from "react-table";
import { SecondaryButton } from "../shared/Button";

const ProductLog = () => {
    const [logs, setLogs] = useState([]);
    const [product, setProduct] = useState(null);
    const { id } = useParams();
    const history = useHistory();

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [productResponse, logsResponse] = await Promise.all([
                    Api(`/api/products/${id}`),
                    Api(`/api/products/${id}/logs`),
                ]);
                setProduct(productResponse.data);
                setLogs(logsResponse.data);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        fetchData();
    }, [id]);

    const columns = React.useMemo(
        () => [
            {
                Header: "Date",
                accessor: (row) =>
                    new Date(row.created_at).toLocaleString("id-ID"),
                sortType: "datetime",
            },
            {
                Header: "Action",
                accessor: "action",
            },
            {
                Header: "Qty Before",
                accessor: "qty_before",
                Cell: ({ value }) => (
                    <div className="text-right">
                        {parseFloat(value || 0).toLocaleString("id-ID")}
                    </div>
                ),
            },
            {
                Header: "Qty After",
                accessor: "qty_after",
                Cell: ({ value }) => (
                    <div className="text-right">
                        {parseFloat(value || 0).toLocaleString("id-ID")}
                    </div>
                ),
            },
            {
                Header: "COGS Before",
                accessor: "cogs_before",
                Cell: ({ value }) => (
                    <div className="text-right">
                        {parseFloat(value || 0).toLocaleString("id-ID")}
                    </div>
                ),
            },
            {
                Header: "COGS After",
                accessor: "cogs_after",
                Cell: ({ value }) => (
                    <div className="text-right">
                        {parseFloat(value || 0).toLocaleString("id-ID")}
                    </div>
                ),
            },
            {
                Header: "Note",
                accessor: "note",
                Cell: ({ value }) => (
                    <div className="whitespace-pre-wrap">{value}</div>
                ),
            },
        ],
        []
    );

    const tableInstance = useTable(
        {
            columns,
            data: logs,
            initialState: { pageIndex: 0 },
        },
        usePagination
    );

    if (!product) {
        return <div className="p-4">Loading...</div>;
    }

    return (
        <>
            <Header
                title={`Product History: ${product.name}`}
                customActionsComponent={
                    <SecondaryButton
                        text="Back to Products"
                        onClick={() => history.push("/product")}
                        icon="ArrowLeft"
                    />
                }
            />
            <div className="p-4">
                <div className="bg-white rounded-lg shadow p-4 mb-4">
                    <div className="grid grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Code
                            </label>
                            <div className="mt-1">{product.code}</div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Category
                            </label>
                            <div className="mt-1">{product.category?.name}</div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Current Quantity
                            </label>
                            <div className="mt-1">
                                {parseFloat(product.quantity).toLocaleString(
                                    "id-ID"
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Current COGS
                            </label>
                            <div className="mt-1">
                                {parseFloat(product.cogs).toLocaleString(
                                    "id-ID"
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Current Price
                            </label>
                            <div className="mt-1">
                                {parseFloat(product.price).toLocaleString(
                                    "id-ID"
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <Table tableInstance={tableInstance} />
            </div>
        </>
    );
};

export default ProductLog;
