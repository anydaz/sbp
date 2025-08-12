import React, { useState, useEffect } from "react";
import Api from "../../api";
import Header from "../shared/Header";

const CapitalContribution = () => {
    const [contributions, setContributions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [form, setForm] = useState({
        date: new Date().toISOString().split("T")[0],
        amount: "",
        notes: "",
    });
    const [editingId, setEditingId] = useState(null);
    // const { confirm } = useConfirm();

    useEffect(() => {
        fetchContributions();
    }, []);

    const fetchContributions = async () => {
        try {
            const response = await Api("/api/capital-contributions");
            setContributions(response.data);
            setLoading(false);
        } catch (error) {
            console.error("Error fetching contributions:", error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await Api(
                    `/api/capital-contributions/${editingId}`,
                    form,
                    "PUT"
                );
            } else {
                await Api("/api/capital-contributions", form, "POST");
            }
            fetchContributions();
            handleCloseModal();
        } catch (error) {
            console.error("Error saving contribution:", error);
        }
    };

    const handleEdit = (contribution) => {
        setForm({
            date: contribution.date,
            amount: contribution.amount,
            notes: contribution.notes || "",
        });
        setEditingId(contribution.id);
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
            <Header title="Capital Contributions" />
            <div className="p-4">
                <div className="mb-4">
                    <button
                        onClick={() => setShowModal(true)}
                        className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Add Contribution
                    </button>
                </div>

                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <table className="min-w-full">
                        <thead className="bg-gray-50">
                            <tr>
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
                            {contributions.map((contribution) => (
                                <tr key={contribution.id}>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        {contribution.date}
                                    </td>
                                    <td className="px-6 py-4">
                                        {contribution.notes}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        {parseFloat(
                                            contribution.amount
                                        ).toLocaleString("id-ID")}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <button
                                            onClick={() =>
                                                handleEdit(contribution)
                                            }
                                            className="text-blue-600 hover:text-blue-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            onClick={() =>
                                                handleDelete(contribution.id)
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
                                    {editingId ? "Edit" : "Add"} Capital
                                    Contribution
                                </h3>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="p-6">
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

export default CapitalContribution;
