import React, { useState, useEffect } from "react";
import {
    RefreshCw,
    Calendar,
    DollarSign,
    TrendingUp,
    TrendingDown,
} from "lucide-react";
import Api from "root/api.js";

const BalanceSheet = () => {
    const [balanceSheet, setBalanceSheet] = useState(null);
    const [loading, setLoading] = useState(false);
    const [selectedPeriod, setSelectedPeriod] = useState("");
    const [error, setError] = useState("");

    // Generate current month period identifier (YYYY-MM)
    const getCurrentPeriod = () => {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        return `${year}-${month}`;
    };

    // Generate list of available periods (last 12 months)
    const generatePeriodOptions = () => {
        const options = [];
        const now = new Date();

        for (let i = 0; i < 12; i++) {
            const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const periodId = `${year}-${month}`;
            const periodLabel = date.toLocaleDateString("id-ID", {
                year: "numeric",
                month: "long",
            });

            options.push({
                value: periodId,
                label: periodLabel,
            });
        }

        return options;
    };

    const periodOptions = generatePeriodOptions();

    useEffect(() => {
        const currentPeriod = getCurrentPeriod();
        setSelectedPeriod(currentPeriod);
        fetchBalanceSheet(currentPeriod);
    }, []);

    const fetchBalanceSheet = async (period) => {
        try {
            setLoading(true);
            setError("");

            const response = await Api(
                `/api/account-balances/balance-sheet?period_identifier=${period}`
            );

            if (response.data.success) {
                setBalanceSheet(response.data.data);
            } else {
                setError("Gagal mengambil data neraca");
            }
        } catch (err) {
            console.error("Error fetching balance sheet:", err);
            setError("Terjadi kesalahan saat mengambil data neraca");
        } finally {
            setLoading(false);
        }
    };

    const handlePeriodChange = (e) => {
        const period = e.target.value;
        setSelectedPeriod(period);
        fetchBalanceSheet(period);
    };

    const handleRefresh = () => {
        fetchBalanceSheet(selectedPeriod);
    };

    const calculateBalanceSheet = async () => {
        try {
            setLoading(true);
            setError("");

            // Calculate balances for the selected period
            const lastDayOfMonth = new Date(
                parseInt(selectedPeriod.split("-")[0]),
                parseInt(selectedPeriod.split("-")[1]),
                0
            );

            await Api.post("/api/account-balances/calculate", {
                date: lastDayOfMonth.toISOString().split("T")[0],
            });

            // Refresh the balance sheet data
            fetchBalanceSheet(selectedPeriod);
        } catch (err) {
            console.error("Error calculating balance sheet:", err);
            setError("Terjadi kesalahan saat menghitung neraca");
            setLoading(false);
        }
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    };

    const AccountSection = ({ title, accounts, total, type }) => {
        const getSectionIcon = () => {
            switch (type) {
                case "assets":
                    return <TrendingUp className="w-5 h-5 text-green-600" />;
                case "liabilities":
                    return <TrendingDown className="w-5 h-5 text-red-600" />;
                case "equity":
                    return <DollarSign className="w-5 h-5 text-blue-600" />;
                default:
                    return <DollarSign className="w-5 h-5 text-gray-600" />;
            }
        };

        const getSectionColor = () => {
            switch (type) {
                case "assets":
                    return "border-green-200 bg-green-50";
                case "liabilities":
                    return "border-red-200 bg-red-50";
                case "equity":
                    return "border-blue-200 bg-blue-50";
                default:
                    return "border-gray-200 bg-gray-50";
            }
        };

        return (
            <div className={`border rounded-lg p-4 ${getSectionColor()}`}>
                <div className="flex items-center mb-3">
                    {getSectionIcon()}
                    <h3 className="text-lg font-semibold ml-2">{title}</h3>
                </div>

                <div className="space-y-2">
                    {accounts?.map((account, index) => (
                        <div
                            key={index}
                            className="flex justify-between items-center py-1 border-b border-gray-200 last:border-b-0"
                        >
                            <div>
                                <span className="font-medium text-gray-700">
                                    {account.account_code}
                                </span>
                                <span className="ml-2 text-gray-600">
                                    {account.account_name}
                                </span>
                            </div>
                            <span className="font-medium text-gray-900">
                                {formatCurrency(account.balance)}
                            </span>
                        </div>
                    ))}
                </div>

                <div className="mt-3 pt-3 border-t border-gray-300">
                    <div className="flex justify-between items-center">
                        <span className="font-bold text-gray-800">
                            Total {title}
                        </span>
                        <span className="font-bold text-lg text-gray-900">
                            {formatCurrency(total)}
                        </span>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <div className="p-6">
            {/* Header */}
            <div className="flex justify-between items-center mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">
                        Neraca Keuangan
                    </h1>
                    <p className="text-gray-600">
                        Laporan posisi keuangan per akhir bulan
                    </p>
                </div>

                <div className="flex items-center space-x-3">
                    <button
                        onClick={calculateBalanceSheet}
                        disabled={loading}
                        className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center space-x-2"
                    >
                        <RefreshCw
                            className={`w-4 h-4 ${
                                loading ? "animate-spin" : ""
                            }`}
                        />
                        <span>Hitung Ulang</span>
                    </button>

                    <button
                        onClick={handleRefresh}
                        disabled={loading}
                        className="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 flex items-center space-x-2"
                    >
                        <RefreshCw
                            className={`w-4 h-4 ${
                                loading ? "animate-spin" : ""
                            }`}
                        />
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            {/* Period Selector */}
            <div className="mb-6">
                <div className="flex items-center space-x-4">
                    <Calendar className="w-5 h-5 text-gray-500" />
                    <label className="text-gray-700 font-medium">
                        Periode:
                    </label>
                    <select
                        value={selectedPeriod}
                        onChange={handlePeriodChange}
                        className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        disabled={loading}
                    >
                        {periodOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Error Message */}
            {error && (
                <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p className="text-red-800">{error}</p>
                </div>
            )}

            {/* Loading State */}
            {loading && (
                <div className="flex justify-center items-center py-12">
                    <RefreshCw className="w-8 h-8 animate-spin text-blue-600" />
                    <span className="ml-2 text-gray-600">
                        Memuat data neraca...
                    </span>
                </div>
            )}

            {/* Balance Sheet Content */}
            {!loading && balanceSheet && (
                <div className="space-y-6">
                    {/* Period Info */}
                    <div className="bg-gray-50 p-4 rounded-lg">
                        <h2 className="text-xl font-semibold text-gray-800 mb-2">
                            Neraca per{" "}
                            {
                                periodOptions.find(
                                    (p) => p.value === selectedPeriod
                                )?.label
                            }
                        </h2>
                        {balanceSheet.totals && (
                            <div
                                className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                                    balanceSheet.totals.is_balanced
                                        ? "bg-green-100 text-green-800"
                                        : "bg-red-100 text-red-800"
                                }`}
                            >
                                {balanceSheet.totals.is_balanced
                                    ? "✓ Neraca Seimbang"
                                    : "⚠ Neraca Tidak Seimbang"}
                            </div>
                        )}
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Assets */}
                        <div className="space-y-6">
                            <AccountSection
                                title="ASET"
                                accounts={balanceSheet.assets?.accounts}
                                total={balanceSheet.assets?.total}
                                type="assets"
                            />
                        </div>

                        {/* Liabilities and Equity */}
                        <div className="space-y-6">
                            <AccountSection
                                title="KEWAJIBAN"
                                accounts={balanceSheet.liabilities?.accounts}
                                total={balanceSheet.liabilities?.total}
                                type="liabilities"
                            />

                            <AccountSection
                                title="EKUITAS"
                                accounts={balanceSheet.equity?.accounts}
                                total={balanceSheet.equity?.total}
                                type="equity"
                            />
                        </div>
                    </div>

                    {/* Summary */}
                    {balanceSheet.totals && (
                        <div className="bg-gray-100 p-6 rounded-lg">
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">
                                Ringkasan Neraca
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="flex justify-between items-center p-3 bg-white rounded-lg">
                                    <span className="font-medium text-gray-700">
                                        Total Aset
                                    </span>
                                    <span className="font-bold text-green-600">
                                        {formatCurrency(
                                            balanceSheet.totals.total_assets
                                        )}
                                    </span>
                                </div>
                                <div className="flex justify-between items-center p-3 bg-white rounded-lg">
                                    <span className="font-medium text-gray-700">
                                        Total Kewajiban + Ekuitas
                                    </span>
                                    <span className="font-bold text-blue-600">
                                        {formatCurrency(
                                            balanceSheet.totals
                                                .total_liabilities_and_equity
                                        )}
                                    </span>
                                </div>
                            </div>

                            <div className="mt-4 text-center">
                                <div
                                    className={`inline-flex items-center px-4 py-2 rounded-lg font-semibold ${
                                        balanceSheet.totals.is_balanced
                                            ? "bg-green-200 text-green-800"
                                            : "bg-red-200 text-red-800"
                                    }`}
                                >
                                    {balanceSheet.totals.is_balanced
                                        ? "✓ ASET = KEWAJIBAN + EKUITAS"
                                        : "⚠ ASET ≠ KEWAJIBAN + EKUITAS"}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* No Data State */}
            {!loading && !balanceSheet && !error && (
                <div className="text-center py-12">
                    <div className="text-gray-500 mb-4">
                        <Calendar className="w-16 h-16 mx-auto mb-4" />
                        <p className="text-lg">
                            Tidak ada data neraca untuk periode yang dipilih
                        </p>
                        <p className="text-sm">
                            Klik "Hitung Ulang" untuk membuat data neraca
                        </p>
                    </div>
                </div>
            )}
        </div>
    );
};

export default BalanceSheet;
