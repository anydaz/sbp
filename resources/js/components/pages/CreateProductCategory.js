import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import TextField from "../shared/TextField.js";
import Api from "../../api.js";
import ModalInfo from "components/shared/ModalInfo.js";
import { useHistory, useParams } from "react-router-dom";

const CreateProductCategory = () => {
    const [name, setName] = useState(null);
    const [code, setCode] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const history = useHistory();
    const { id } = useParams();

    const handleSave = async () => {
        const body = {
            name: name,
            code: code,
        };

        const method = id ? "PUT" : "POST";
        const url = id
            ? `/api/product_categories/${id}`
            : "/api/product_categories";

        const response = await Api(url, body, method);
        if (response) {
            setShowModal(true);
            setTimeout(() => {
                history.push("/product-category");
            }, 1000);
        }
    };

    const getCategoryData = async () => {
        const response = await Api(`/api/product_categories/${id}`);
        if (response) {
            const category = response.data;
            setName(category.name);
            setCode(category.code);
        }
    };

    useEffect(() => {
        // Check if current route is edit page or not
        // if edit page (has params id) then get product data
        if (id) {
            getCategoryData();
        }
    }, []);

    return (
        <Fragment>
            <Header
                title="Buat Kategori Baru"
                action={{ title: "Save", onClick: handleSave }}
                withBackButton={true}
            />
            <div className="p-4">
                <TextField
                    value={name}
                    label="Nama Kategori"
                    onChange={(e) => {
                        setName(e.target.value);
                    }}
                />
                <TextField
                    value={code}
                    label="Kode Kategori"
                    onChange={(e) => {
                        setCode(e.target.value);
                    }}
                />
            </div>
            {showModal && <ModalInfo />}
        </Fragment>
    );
};

export default CreateProductCategory;
