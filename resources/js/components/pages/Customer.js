import React, { Fragment, useState, useEffect } from "react";
import Api from "../../api.js";
import Table from "../shared/Table.js";
import TextField from "../shared/TextField.js";
import Header from "../shared/Header.js";
import { useTable, usePagination } from "react-table";
import { useHistory } from "react-router-dom";
import useDebounce from "hooks/useDebounce.js";
import Modal from "components/shared/Modal.js";
import { IconButton } from "components/shared/Button.js";

const Customer = () => {
    const [search, setSearch] = useState(null);
    const [customers, setCustomers] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [modalDelete, setModalDelete] = useState({
        show: false,
        customer: null,
    });
    const history = useHistory();

    const getCustomers = async () => {
        const response = await Api("/api/customers", {
            search: search ? search : null,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setCustomers(response.data.data);
    };

    const handleDeleteCustomer = async () => {
        const response = await Api(
            `api/customers/${modalDelete.customer.id}`,
            {},
            "DELETE"
        );
        if (response) {
            setModalDelete({ show: false, customer: null });
            getCustomers();
        }
    };

    const goToPage = async (page) => {
        const response = await Api("api/customers", {
            search: search ? search : null,
            page: page,
        });
        setCurrentPage(response.data.current_page);
        setLastPage(response.data.last_page);
        setCustomers(response.data.data);
    };

    useEffect(() => {
        getCustomers();
    }, [search]);

    const columns = React.useMemo(
        () => [
            { Header: "Name", accessor: "name" },
            { Header: "Email", accessor: "email" },
            { Header: "No Telepon", accessor: "phone" },
            { Header: "Alamat", accessor: "address" },
            {
                Header: "Action",
                Cell: (value) => (
                    <div className="flex justify-center">
                        <IconButton
                            onClick={() =>
                                history.push(
                                    `/customer/edit/${value.row.original.id}`
                                )
                            }
                            icon="Edit"
                            iconClass="text-white text-sm"
                            customClass="bg-yellow-400 hover:bg-yellow-500 mr-1"
                        />
                        <IconButton
                            onClick={() =>
                                setModalDelete({
                                    show: true,
                                    customer: value.row.original,
                                })
                            }
                            icon="Trash2"
                            iconClass="text-white text-sm"
                            customClass="bg-red-400 hover:bg-red-500 ml-1"
                        />
                    </div>
                ),
                id: "action-button",
            },
        ],
        []
    );

    const tableInstance = useTable({ columns, data: customers }, usePagination);
    const debounceInput = useDebounce((text) => setSearch(text), 500);

    return (
        <Fragment>
            <Header
                title="Customer"
                action={{
                    title: "Buat Customer Baru",
                    onClick: () => history.push("/customer/create"),
                }}
            />
            <div className="p-4">
                <div className="w-2/5">
                    <TextField
                        label="Cari Customer"
                        onChange={(e) => debounceInput(e.target.value)}
                    />
                </div>
                <Table
                    currentPage={currentPage}
                    lastPage={lastPage}
                    goToPage={goToPage}
                    tableInstance={tableInstance}
                />
            </div>
            {modalDelete.show && (
                <Modal
                    onSubmit={handleDeleteCustomer}
                    onCancel={() =>
                        setModalDelete({ show: false, customer: null })
                    }
                >
                    Apakah anda yakin akan menghapus customer ini{" "}
                    {modalDelete.customer.name}
                </Modal>
            )}
        </Fragment>
    );
};

export default Customer;
