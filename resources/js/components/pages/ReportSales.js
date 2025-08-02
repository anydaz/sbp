import React, { useEffect, useState, Fragment } from "react";
import Header from "components/shared/Header.js";
import { useTable } from "react-table";
import Table from "components/shared/Table.js";
import Api from "root/api.js";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { getStartOfMonth, getEndOfMonth } from "helper/date-helper";
import DropdownWithApi from "components/shared/DropdownWithApi.js";
import axios from "axios";
import fileDownload from "js-file-download";
import Button from "components/shared/Button.js";

const ReportSales = () => {
    const [data, setData] = useState([]);
    const [total, setTotal] = useState(0);
    const [reportPeriod, setReportPeriod] = useState(new Date());
    const [customer, setCustomer] = useState(null);
    const [loading, setLoading] = useState(false);

    const getSalesReports = async () => {
        const response = await Api("/api/report/sales", {
            start_date: getStartOfMonth(reportPeriod).toISOString(),
            end_date: getEndOfMonth(reportPeriod).toISOString(),
            customer_id: customer?.id,
        });
        setData(response.data.data);
        setTotal(response.data.total);
    };

    const handleExport = async () => {
        setLoading(true);
        const token = localStorage.getItem("token");
        const response = await axios({
            method: "post",
            url: "api/report/sales/export",
            responseType: "blob",
            headers: {
                Accept: "multipart/form-data",
                Authorization: "Bearer " + token,
            },
            data: {
                start_date: getStartOfMonth(reportPeriod).toISOString(),
                end_date: getEndOfMonth(reportPeriod).toISOString(),
                customer_id: customer?.id,
            },
        });

        setLoading(false);

        // filename in MM-YYYY format
        const reportPeriodFormatted = reportPeriod
            .toLocaleDateString("id-ID", {
                month: "2-digit",
                year: "numeric",
            })
            .replace(/\//g, "_");

        let filename = `sales_report_${reportPeriodFormatted}`;

        if (customer) {
            filename += `_${customer.name}`;
        }

        fileDownload(response.data, filename + ".xlsx");
    };

    useEffect(() => {
        getSalesReports();
    }, [reportPeriod, customer]);

    const columns = React.useMemo(
        () => [
            { Header: "id", accessor: "id" },
            { Header: "customer", accessor: "customer.name" },
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
                accessor: (data) =>
                    parseFloat(data.total_amount).toLocaleString("id-ID"),
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
                Header: "grand total",
                accessor: (data) =>
                    parseFloat(data.final_amount).toLocaleString("id-ID"),
            },
        ],
        []
    );

    const tableInstance = useTable({ columns, data: data });

    return (
        <Fragment>
            <Header title="Report Penjualan" />
            <div className="p-4">
                <div className="flex gap-[8px]">
                    <div className="w-1/5 ">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Bulan
                        </label>
                        <DatePicker
                            showMonthYearPicker
                            selected={reportPeriod}
                            dateFormat="MM/yyyy"
                            onChange={(date) => setReportPeriod(date)}
                            className="w-full border rounded-lg mb-4 p-1"
                            wrapperClassName="w-full"
                        />
                    </div>
                    <div className="w-1/5">
                        <label
                            class={`font-semibold text-xs text-gray-600 pb-1 block`}
                        >
                            Customer
                        </label>
                        <DropdownWithApi
                            onChange={(customer) => setCustomer(customer)}
                            selected={customer}
                            type="customer"
                            allowEmpty
                        />
                    </div>
                    {/* Export Sales Detail */}
                    <div className="w-1/5 pt-[20px]">
                        <Button
                            text={
                                loading
                                    ? "Loading..."
                                    : "Export Detail Penjualan"
                            }
                            onClick={handleExport}
                            disabled={loading}
                        />
                    </div>
                </div>
                <p class="font-semibold text-md mb-4">
                    Total Pejualan: Rp. {total.toLocaleString("id-ID")}
                </p>
                <Table tableInstance={tableInstance} pagination={false} />
            </div>
        </Fragment>
    );
};

export default ReportSales;
