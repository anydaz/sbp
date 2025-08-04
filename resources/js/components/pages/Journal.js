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

    const columns = React.useMemo(
        () => [
            {
                Header: "Tanggal",
                accessor: "date",
            },
            {
                Header: "Keterangan",
                accessor: "description",
            },
            {
                Header: "debit",
                accessor: "debit",
            },
            {
                Header: "kredit",
                accessor: "credit",
            },
        ],
        []
    );

    const tableInstance = useTable({ columns, data: data });

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
