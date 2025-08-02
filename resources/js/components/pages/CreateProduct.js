import React, { Fragment, useState, useEffect } from "react";
import Header from "../shared/Header.js";
import TextField from "../shared/TextField.js";
import Api from "../../api.js";
import ModalInfo from "components/shared/ModalInfo.js";
import { useHistory, useParams } from "react-router-dom";
import NumberField from "components/shared/NumberField.js";
import DropdownWithApi from "components/shared/DropdownWithApi.js";

const CreateProduct = () => {
    const [code, setCode] = useState(null);
    const [name, setName] = useState(null);
    const [price, setPrice] = useState(0);
    const [showModal, setShowModal] = useState(false);
    const [category, setCategory] = useState(null);
    const [cogs, setCogs] = useState(0);
    const [quantity, setQuantity] = useState(0);
    const history = useHistory();
    const { id } = useParams();

    const handleSave = async () => {
        const body = {
            code: code,
            name: name,
            price: price,
            quantity: quantity,
            cogs: cogs,
            product_category_id: category.id,
        };

        const method = id ? "PUT" : "POST";
        const url = id ? `/api/products/${id}` : "/api/products";

        const response = await Api(url, body, method);
        if (response) {
            setShowModal(true);
            setTimeout(() => {
                history.push("/product");
            }, 1000);
        }
    };

    const getProductData = async () => {
        const response = await Api(`/api/products/${id}`);
        if (response) {
            const product = response.data;
            setName(product.name);
            setCode(product.code);
            setPrice(product.price);
            setCategory(product.category);
            setCogs(product.cogs);
            setQuantity(product.quantity);
        }
    };

    useEffect(() => {
        // Check if current route is edit page or not
        // if edit page (has params id) then get product data
        if (id) {
            getProductData();
        }
    }, []);

    return (
        <Fragment>
            <Header
                title="Buat Product Baru"
                action={{ title: "Save", onClick: handleSave }}
                withBackButton={true}
            />
            <div className="p-4">
                <TextField
                    value={code}
                    label="Kode Produk"
                    onChange={(e) => {
                        setCode(e.target.value);
                    }}
                />
                <TextField
                    value={name}
                    label="Nama Produk"
                    onChange={(e) => {
                        setName(e.target.value);
                    }}
                />
                <DropdownWithApi
                    type="product-category"
                    selected={category}
                    onChange={(category) => setCategory(category)}
                    customOptionsWidth="w-50vw"
                />
                <NumberField
                    value={price}
                    label="Harga"
                    onChange={(value) => {
                        setPrice(value);
                    }}
                />
            </div>
            {showModal && <ModalInfo />}
        </Fragment>
    );
};

export default CreateProduct;
