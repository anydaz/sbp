import React, { useState, useEffect } from "react";
import Api from "../../api.js";
import Header from "../shared/Header";

const Ledger = () => {
    const [accounts, setAccounts] = useState({});
    const [loading, setLoading] = useState(true);
    const [selectedAccount, setSelectedAccount] = useState(null);
    const [accountDetails, setAccountDetails] = useState(null);

    useEffect(() => {
        fetchAccounts();
    }, []);

    const fetchAccounts = async () => {
        try {
            const response = await Api("/api/accounts?parentAccount=0");
            setAccounts(response.data);
            setLoading(false);
        } catch (error) {
            console.error("Error fetching accounts:", error);
        }
    };

    const fetchAccountDetails = async (accountId) => {
        try {
            const response = await Api(`/api/accounts/${accountId}`);
            setAccountDetails(response.data);
        } catch (error) {
            console.error("Error fetching account details:", error);
        }
    };

    const formatAmount = (amount) => {
        return parseFloat(amount || 0).toLocaleString("id-ID");
    };

    const renderAccountGroup = (type, accounts) => (
        <div key={type} className="mb-8">
            <h2 className="text-xl font-semibold mb-4 capitalize">
                {type} Accounts
            </h2>
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Starting Balance
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ending Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {accounts.map((account) => (
                            <tr
                                key={account.id}
                                onClick={() => {
                                    setSelectedAccount(account);
                                    fetchAccountDetails(account.id);
                                }}
                                className="hover:bg-gray-50 cursor-pointer"
                            >
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {account.code}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {account.name}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {formatAmount(account.starting_balance)}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {formatAmount(account.total_debit)}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {formatAmount(account.total_credit)}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {formatAmount(account.balance)}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );

    if (loading) {
        return <div className="p-4">Loading...</div>;
    }

    return (
        <>
            <Header title="Buku Besar" />
            <div className="p-4">
                <div className="space-y-4">
                    {Object.entries(accounts).map(([type, accounts]) =>
                        renderAccountGroup(type, accounts)
                    )}
                </div>

                {/* Account Details Modal */}
                {selectedAccount && accountDetails && (
                    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
                        <div className="bg-white rounded-lg w-full max-w-6xl max-h-[90vh] overflow-auto">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <div className="flex justify-between items-center">
                                    <h3 className="text-lg font-semibold">
                                        {accountDetails.code} -{" "}
                                        {accountDetails.name}
                                    </h3>
                                    <button
                                        onClick={() => {
                                            setSelectedAccount(null);
                                            setAccountDetails(null);
                                        }}
                                        className="text-gray-400 hover:text-gray-500"
                                    >
                                        <span className="sr-only">Close</span>
                                        <svg
                                            className="h-6 w-6"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div className="px-6 py-4">
                                <table className="min-w-full">
                                    <thead>
                                        <tr>
                                            <th className="px-4 py-2 text-left">
                                                Date
                                            </th>
                                            <th className="px-4 py-2 text-left">
                                                Description
                                            </th>
                                            <th className="px-4 py-2 text-right">
                                                Debit
                                            </th>
                                            <th className="px-4 py-2 text-right">
                                                Credit
                                            </th>
                                            <th className="px-4 py-2 text-right">
                                                Balance
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td className="px-4 py-2"></td>
                                            <td className="px-4 py-2 font-semibold">
                                                Opening Balance
                                            </td>
                                            <td className="px-4 py-2 text-right"></td>
                                            <td className="px-4 py-2 text-right"></td>
                                            <td className="px-4 py-2 text-right">
                                                {formatAmount(
                                                    accountDetails.starting_balance
                                                )}
                                            </td>
                                        </tr>
                                        {accountDetails.entries.map((entry) => (
                                            <tr
                                                key={entry.id}
                                                className="hover:bg-gray-50"
                                            >
                                                <td className="px-4 py-2">
                                                    {new Date(
                                                        entry.date
                                                    ).toLocaleDateString(
                                                        "id-ID"
                                                    )}
                                                </td>
                                                <td className="px-4 py-2">
                                                    {entry.batch.description}
                                                </td>
                                                <td className="px-4 py-2 text-right">
                                                    {formatAmount(entry.debit)}
                                                </td>
                                                <td className="px-4 py-2 text-right">
                                                    {formatAmount(entry.credit)}
                                                </td>
                                                <td className="px-4 py-2 text-right">
                                                    {formatAmount(
                                                        entry.running_balance
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
};

export default Ledger;
