import React, { useEffect, useState, Fragment } from "react";
import Header from "components/shared/Header.js";
import Api from "root/api.js";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { getStartOfMonth, getEndOfMonth } from "helper/date-helper";

const ReportProfitLoss = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [reportPeriod, setReportPeriod] = useState(new Date());

    const getProfitLossReport = async () => {
        setLoading(true);
        try {
            const response = await Api("/api/report/profit-loss", {
                start_date: getStartOfMonth(reportPeriod).toISOString(),
                end_date: getEndOfMonth(reportPeriod).toISOString(),
            });
            setData(response.data);
        } catch (error) {
            console.error("Error fetching profit & loss report:", error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        getProfitLossReport();
    }, [reportPeriod]);

    const formatCurrency = (amount) => {
        return `Rp. ${Math.abs(amount).toLocaleString("id-ID")}`;
    };

    const formatNumber = (amount) => {
        return amount.toLocaleString("id-ID");
    };

    if (loading || !data) {
        return (
            <Fragment>
                <Header title="Laporan Laba Rugi" />
                <div className="p-4">
                    <div className="flex justify-center items-center h-64">
                        <div className="text-gray-500">
                            {loading ? "Loading..." : "No data available"}
                        </div>
                    </div>
                </div>
            </Fragment>
        );
    }

    return (
        <Fragment>
            <Header title="Laporan Laba Rugi" />
            <div className="p-4">
                {/* Period Selector */}
                <div className="flex gap-[8px] mb-6">
                    <div className="w-1/5">
                        <label className="font-semibold text-xs text-gray-600 pb-1 block">
                            Periode
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
                </div>

                {/* Report Header */}
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="text-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-800">
                            LAPORAN LABA RUGI
                        </h2>
                        <p className="text-gray-600">
                            Periode:{" "}
                            {new Date(
                                data.period.start_date
                            ).toLocaleDateString("id-ID")}{" "}
                            -{" "}
                            {new Date(data.period.end_date).toLocaleDateString(
                                "id-ID"
                            )}
                        </p>
                    </div>

                    <div className="space-y-6">
                        {/* Revenue Section */}
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 border-b-2 border-gray-300 pb-2 mb-4">
                                PENDAPATAN
                            </h3>
                            <div className="space-y-2">
                                {data.revenue.accounts.map((account, index) => (
                                    <div
                                        key={index}
                                        className="flex justify-between items-center py-1"
                                    >
                                        <div className="flex items-center space-x-3">
                                            <span className="text-sm text-gray-500 w-16">
                                                {account.account_code}
                                            </span>
                                            <span className="text-gray-700">
                                                {account.account_name}
                                            </span>
                                        </div>
                                        <span className="text-gray-700 font-mono">
                                            {formatCurrency(account.balance)}
                                        </span>
                                    </div>
                                ))}
                                <div className="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                    <span className="text-gray-800">
                                        Total Pendapatan
                                    </span>
                                    <span className="text-gray-800 font-mono">
                                        {formatCurrency(data.revenue.total)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* COGS Section */}
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 border-b-2 border-gray-300 pb-2 mb-4">
                                HARGA POKOK PENJUALAN (HPP)
                            </h3>
                            <div className="space-y-2">
                                {data.cogs.accounts.map((account, index) => (
                                    <div
                                        key={index}
                                        className="flex justify-between items-center py-1"
                                    >
                                        <div className="flex items-center space-x-3">
                                            <span className="text-sm text-gray-500 w-16">
                                                {account.account_code}
                                            </span>
                                            <span className="text-gray-700">
                                                {account.account_name}
                                            </span>
                                        </div>
                                        <span className="text-gray-700 font-mono">
                                            ({formatCurrency(account.balance)})
                                        </span>
                                    </div>
                                ))}
                                <div className="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                    <span className="text-gray-800">
                                        Total HPP
                                    </span>
                                    <span className="text-red-600 font-mono">
                                        ({formatCurrency(data.cogs.total)})
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Gross Profit */}
                        <div className="border-t border-gray-300 pt-4">
                            <div className="flex justify-between items-center py-2 font-bold text-lg">
                                <span
                                    className={
                                        data.gross_profit >= 0
                                            ? "text-blue-600"
                                            : "text-red-600"
                                    }
                                >
                                    LABA KOTOR
                                </span>
                                <span
                                    className={`font-mono ${
                                        data.gross_profit >= 0
                                            ? "text-blue-600"
                                            : "text-red-600"
                                    }`}
                                >
                                    {data.gross_profit >= 0 ? "" : "("}
                                    {formatCurrency(data.gross_profit)}
                                    {data.gross_profit >= 0 ? "" : ")"}
                                </span>
                            </div>
                        </div>

                        {/* Operating Expenses Section */}
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 border-b-2 border-gray-300 pb-2 mb-4">
                                BEBAN OPERASIONAL
                            </h3>
                            <div className="space-y-2">
                                {data.operating_expenses.accounts.map(
                                    (account, index) => (
                                        <div
                                            key={index}
                                            className="flex justify-between items-center py-1"
                                        >
                                            <div className="flex items-center space-x-3">
                                                <span className="text-sm text-gray-500 w-16">
                                                    {account.account_code}
                                                </span>
                                                <span className="text-gray-700">
                                                    {account.account_name}
                                                </span>
                                            </div>
                                            <span className="text-gray-700 font-mono">
                                                (
                                                {formatCurrency(
                                                    account.balance
                                                )}
                                                )
                                            </span>
                                        </div>
                                    )
                                )}
                                <div className="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                    <span className="text-gray-800">
                                        Total Beban Operasional
                                    </span>
                                    <span className="text-red-600 font-mono">
                                        (
                                        {formatCurrency(
                                            data.operating_expenses.total
                                        )}
                                        )
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Net Income Section */}
                        <div className="border-t-2 border-gray-400 pt-4">
                            <div className="flex justify-between items-center py-3">
                                <span
                                    className={`text-xl font-bold ${
                                        data.net_income >= 0
                                            ? "text-green-600"
                                            : "text-red-600"
                                    }`}
                                >
                                    {data.net_income >= 0
                                        ? "LABA BERSIH"
                                        : "RUGI BERSIH"}
                                </span>
                                <span
                                    className={`text-xl font-bold font-mono ${
                                        data.net_income >= 0
                                            ? "text-green-600"
                                            : "text-red-600"
                                    }`}
                                >
                                    {data.net_income >= 0 ? "" : "("}
                                    {formatCurrency(data.net_income)}
                                    {data.net_income >= 0 ? "" : ")"}
                                </span>
                            </div>
                        </div>

                        {/* Summary Statistics */}
                        <div className="grid grid-cols-4 gap-4 mt-6 p-4 bg-gray-50 rounded-lg">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">
                                    Total Pendapatan
                                </p>
                                <p className="text-lg font-semibold text-blue-600">
                                    {formatCurrency(data.revenue.total)}
                                </p>
                            </div>
                            <div className="text-center">
                                <p className="text-sm text-gray-500">HPP</p>
                                <p className="text-lg font-semibold text-red-600">
                                    {formatCurrency(data.cogs.total)}
                                </p>
                            </div>
                            <div className="text-center">
                                <p className="text-sm text-gray-500">
                                    Laba Kotor
                                </p>
                                <p
                                    className={`text-lg font-semibold ${
                                        data.gross_profit >= 0
                                            ? "text-blue-600"
                                            : "text-red-600"
                                    }`}
                                >
                                    {formatCurrency(data.gross_profit)}
                                </p>
                            </div>
                            <div className="text-center">
                                <p className="text-sm text-gray-500">
                                    Margin Kotor (%)
                                </p>
                                <p
                                    className={`text-lg font-semibold ${
                                        data.gross_profit >= 0
                                            ? "text-green-600"
                                            : "text-red-600"
                                    }`}
                                >
                                    {data.revenue.total > 0
                                        ? (
                                              (data.gross_profit /
                                                  data.revenue.total) *
                                              100
                                          ).toFixed(1) + "%"
                                        : "0.0%"}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Fragment>
    );
};

export default ReportProfitLoss;
