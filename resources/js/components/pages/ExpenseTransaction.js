import React, { useState, useEffect } from "react";
import Api from "../../api";
import Header from "../shared/Header";
// import { useConfirm } from "../../hooks/useConfirm";
// import { formatDate } from "../../helpers";

const ExpenseTransaction = () => {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [expenseAccounts, setExpenseAccounts] = useState([]);
    const [form, setForm] = useState({
        account_id: "",
        date: new Date().toISOString().split("T")[0],
        amount: "",
        notes: "",
    });
    const [editingId, setEditingId] = useState(null);
    // const { confirm } = useConfirm();

    useEffect(() => {
        fetchTransactions();
        fetchExpenseAccounts();
    }, []);

    const fetchExpenseAccounts = async () => {
        try {
            const response = await Api("/api/accounts/type/expense");
            setExpenseAccounts(response.data);
            if (response.data.length > 0) {
                setForm((form) => ({
                    ...form,
                    account_id: response.data[0].id,
                }));
            }
        } catch (error) {
            console.error("Error fetching expense accounts:", error);
        }
    };
    const fetchTransactions = async () => {
        try {
            const response = await Api("/api/expense-transactions");
            setTransactions(response.data);
            setLoading(false);
        } catch (error) {
            console.error("Error fetching transactions:", error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await Api(
                    `/api/expense-transactions/${editingId}`,
                    form,
                    "PUT"
                );
            } else {
                await Api("/api/expense-transactions", form, "POST");
            }
            fetchTransactions();
            handleCloseModal();
        } catch (error) {
            console.error("Error saving transaction:", error);
        }
    };

    const handleEdit = (transaction) => {
        setForm({
            account_id: transaction.account_id,
            date: transaction.date,
            amount: transaction.amount,
            notes: transaction.notes || "",
        });
        setEditingId(transaction.id);
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        // if (
        //     await confirm(
        //         "Are you sure you want to delete this capital contribution?"
        //     )
        // ) {
        //     try {
        //         await Api(`/api/capital-contributions/${id}`, "DELETE");
        //         fetchContributions();
        //     } catch (error) {
        //         console.error("Error deleting contribution:", error);
        //     }
        // }
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setForm({
            account_id: expenseAccounts.length > 0 ? expenseAccounts[0].id : "",
            date: new Date().toISOString().split("T")[0],
            amount: "",
            notes: "",
        });
        setEditingId(null);
    };

    if (loading) {
        return <div className="p-4">Loading...</div>;
    }

    return (
        <>
            <Header title="Expense Transactions" />
            <div className="p-4">
                <div className="mb-4">
                    <button
                        onClick={() => setShowModal(true)}
                        className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Add Transaction
                    </button>
                </div>

                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <table className="min-w-full">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Expense Type
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Notes
                                </th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                    Amount
                                </th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {transactions.map((transaction) => (
                                <tr key={transaction.id}>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        {transaction.account.code} -{" "}
                                        {transaction.account.name}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        {transaction.date}
                                    </td>
                                    <td className="px-6 py-4">
                                        {transaction.notes}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        {parseFloat(
                                            transaction.amount
                                        ).toLocaleString("id-ID")}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <button
                                            onClick={() =>
                                                handleEdit(transaction)
                                            }
                                            className="text-blue-600 hover:text-blue-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            onClick={() =>
                                                handleDelete(transaction.id)
                                            }
                                            className="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Modal */}
                {showModal && (
                    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
                        <div className="bg-white rounded-lg w-full max-w-md">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h3 className="text-lg font-medium">
                                    {editingId ? "Edit" : "Add"} Expense
                                    Transaction
                                </h3>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="p-6">
                                    <div className="mb-4">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Expense Type
                                        </label>
                                        <select
                                            value={form.account_id}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    account_id: e.target.value,
                                                })
                                            }
                                            className="w-full px-3 py-2 border rounded-md"
                                            required
                                        >
                                            {expenseAccounts.map((account) => (
                                                <option
                                                    key={account.id}
                                                    value={account.id}
                                                >
                                                    {account.code} -{" "}
                                                    {account.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="mb-4">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Date
                                        </label>
                                        <input
                                            type="date"
                                            value={form.date}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    date: e.target.value,
                                                })
                                            }
                                            className="w-full px-3 py-2 border rounded-md"
                                            required
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Amount
                                        </label>
                                        <input
                                            type="number"
                                            value={form.amount}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    amount: e.target.value,
                                                })
                                            }
                                            className="w-full px-3 py-2 border rounded-md"
                                            required
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Notes
                                        </label>
                                        <input
                                            type="text"
                                            value={form.notes}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    notes: e.target.value,
                                                })
                                            }
                                            className="w-full px-3 py-2 border rounded-md"
                                        />
                                    </div>
                                </div>
                                <div className="px-6 py-4 bg-gray-50 text-right">
                                    <button
                                        type="button"
                                        onClick={handleCloseModal}
                                        className="mr-3 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600"
                                    >
                                        {editingId ? "Update" : "Save"}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
};

export default ExpenseTransaction;
