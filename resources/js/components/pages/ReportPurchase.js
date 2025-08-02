import React, { useEffect, useState, Fragment } from 'react';
import Header from 'components/shared/Header.js';
import { useTable } from 'react-table';
import Table from 'components/shared/Table.js';
import Api from 'root/api.js';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { getStartOfMonth, getEndOfMonth } from 'helper/date-helper'

const ReportPurchase = () => {
    const [data, setData] = useState([]);
    const [total, setTotal] = useState(0);
    const [reportPeriod, setReportPeriod] = useState(new Date());

    const getPurchaseReports = async () => {
		const response = await Api("/api/report/purchase", {
			start_date: getStartOfMonth(reportPeriod).toISOString(),
			end_date: getEndOfMonth(reportPeriod).toISOString(),
		});
		setData(response.data.data);
        setTotal(response.data.total);
	}

    useEffect(() => {
		getPurchaseReports();
	}, [reportPeriod]);

    const columns = React.useMemo(() => [
		{Header: 'id',accessor: 'id'},
		{Header: 'supplier',accessor: 'supplier'},
		{Header: 'tanggal', accessor: (data) => {return new Date(data.created_at).toLocaleDateString('id-ID')}},
		{Header: 'total', accessor: (data) => parseFloat(data.total_amount).toLocaleString('id-ID') },
        {Header: 'diskon nota', accessor: (data) => data.purchase_discount.toLocaleString('id-ID')},
        {Header: 'grand total',  accessor: (data) => parseFloat(data.final_amount).toLocaleString('id-ID')},
	], []);

    const tableInstance = useTable({ columns, data: data });

    console.log(reportPeriod);

    return (
        <Fragment>
            <Header title='Report Pembelian'/>
            <div className="p-4">
                <DatePicker
                    showMonthYearPicker
                    selected={reportPeriod}
                    dateFormat="MM/yyyy"
                    onChange={date => setReportPeriod(date)}
                    className="border rounded-lg mb-4 p-1"
                />
                <p class="font-semibold text-md mb-4">Total Pembelian: Rp. {total.toLocaleString('id-ID')}</p>
                <Table
                    tableInstance={tableInstance}
                    pagination={false}
                />
            </div>
        </Fragment>
    )
}

export default ReportPurchase;