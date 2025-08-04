import React, { useEffect, Fragment } from "react";
import Header from "../shared/Header.js";
import Table from "../shared/Table.js";
import Api from "../../api.js";
import { useTable, usePagination } from "react-table";

const Journal = () => {
    const [data, setData] = React.useState([]);

    const getJournal = async () => {
        const response = await Api("/api/journal_batches");

        console.log(response);
        setData(response.data.data);

        // const result = await response.json();
        // setData(response);
    };

    // const columns = React.useMemo(
    //     () => [
    //         {
    //             Header: "Tanggal",
    //             accessor: "date",
    //         },
    //         {
    //             Header: "Keterangan",
    //             accessor: "description",
    //         },
    //         {
    //             Header: "debit",
    //             accessor: "debit",
    //         },
    //         {
    //             Header: "kredit",
    //             accessor: "credit",
    //         },
    //     ],
    //     []
    // );

    const flattenJournalData = (data) => {
        let rows = [];
        data.forEach((batch) => {
            rows.push({
                ...batch,
                isBatch: true,
            });
            batch.entries.forEach((entry) => {
                rows.push({
                    ...entry,
                    isBatch: false,
                });
            });
        });
        return rows;
    };

    const columns = React.useMemo(
        () => [
            {
                Header: "Tanggal",
                accessor: (row) => (row.isBatch ? row.date : ""),
            },
            {
                Header: "Keterangan",
                accessor: (row) => row.description,
                className: "text-left",
                Cell: ({ value, row }) => (
                    <div
                        style={{
                            textAlign: "left",
                            paddingLeft: row.original.isBatch ? "0" : "20px",
                        }}
                    >
                        {value}
                    </div>
                ),
            },
            {
                Header: "debit",
                accessor: (row) => (row.isBatch ? "" : row.debit),
                className: "text-right",
                Cell: ({ value }) => (
                    <div style={{ textAlign: "right" }}>{value}</div>
                ),
            },
            {
                Header: "kredit",
                accessor: (row) => (row.isBatch ? "" : row.credit),
                className: "text-right",
                Cell: ({ value }) => (
                    <div style={{ textAlign: "right" }}>{value}</div>
                ),
            },
        ],
        []
    );

    const tableInstance = useTable({ columns, data: flattenJournalData(data) });

    useEffect(() => {
        getJournal();
    }, []);

    return (
        <Fragment>
            <Header title="Jurnal Umum" />
            <div className="container mx-auto p-4">
                {/* {JSON.stringify(data)} */}
                <Table tableInstance={tableInstance} pagination={false} />
            </div>
        </Fragment>
    );
};

export default Journal;
