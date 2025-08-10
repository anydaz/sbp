import React, { useState, useEffect } from "react";
import Api from "../../../api.js";
import Table from "../../shared/Table";
import { useTable, usePagination } from "react-table";

const QuantityLogs = ({ productId }) => {
    const [logs, setLogs] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    const fetchLogs = async (page = 1) => {
        try {
            const response = await Api(
                `/api/products/${productId}/logs?type=quantity&page=${page}`
            );
            setLogs(response.data.data);
            setCurrentPage(response.data.current_page);
            setLastPage(response.data.last_page);
        } catch (error) {
            console.error("Error fetching quantity logs:", error);
        }
    };

    useEffect(() => {
        fetchLogs();
    }, [productId]);

    const columns = React.useMemo(
        () => [
            {
                Header: "Date",
                accessor: (row) =>
                    new Date(row.created_at).toLocaleString("id-ID"),
                sortType: "datetime",
            },
            {
                Header: "Type",
                accessor: "action",
                Cell: ({ value }) => (
                    <div
                        className={`px-2 py-1 rounded-full text-sm inline-block
                        ${
                            value === "quantity_update"
                                ? "bg-green-100 text-green-800"
                                : "bg-yellow-100 text-yellow-800"
                        }`}
                    >
                        {value === "quantity_update" ? "Update" : "Revert"}
                    </div>
                ),
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
                Header: "Change",
                accessor: (row) => row.qty_after - row.qty_before,
                Cell: ({ value }) => (
                    <div
                        className={`text-right ${
                            value > 0 ? "text-green-600" : "text-red-600"
                        }`}
                    >
                        {value > 0 ? "+" : ""}
                        {parseFloat(value).toLocaleString("id-ID")}
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

    return (
        <Table
            tableInstance={tableInstance}
            currentPage={currentPage}
            lastPage={lastPage}
            goToPage={fetchLogs}
        />
    );
};

export default QuantityLogs;
