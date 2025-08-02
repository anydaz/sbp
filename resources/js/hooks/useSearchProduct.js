import React from "react";
import Api from "root/api.js";

const useSearchProduct = ({ details, setDetails, defaultDetail }) => {
    const ref = React.useRef(null);

    const addProductToDetails = (product) => {
        // Check if product already exists in details
        // const existingProductIndex = details.findIndex(
        //     (detail) => detail.product && detail.product.id === product.id
        // );
        // if (existingProductIndex !== -1) {
        //     alert("Produk sudah ada di list penjualan");
        //     return;
        // }

        const emptyProductDetailIndex = details.findIndex(
            (detail) => !detail.product
        );

        if (emptyProductDetailIndex == -1) {
            const newDetails = [...details, defaultDetail];
            newDetails[newDetails.length - 1]["product"] = product;
            newDetails[newDetails.length - 1]["qty"] = 1;
            newDetails[newDetails.length - 1]["price"] = product.price;
            newDetails[newDetails.length - 1]["subtotal"] = product.price;
            setDetails(newDetails);
        } else {
            const newDetails = [...details];
            newDetails[emptyProductDetailIndex]["product"] = product;
            newDetails[emptyProductDetailIndex]["qty"] = 1;
            newDetails[emptyProductDetailIndex]["price"] = product.price;
            newDetails[emptyProductDetailIndex]["subtotal"] = product.price;
            setDetails(newDetails);
        }

        setTimeout(() => {
            scrollToBottom();
        }, 100);
    };

    const scrollToBottom = () => {
        const element = ref.current;
        console.log("scrolling to bottom");
        if (element) {
            console.log("scrolling to bottom", element, element.scrollHeight);
            element.scrollTop = element.scrollHeight;
        }
    };

    const onFindProduct = async (code) => {
        const data = await Api(`/api/products/efficiency_code/${code}`);
        const product = data.data;
        console.log("product", product, data);
        if (!product) {
            alert("Produk tidak ditemukan");
            return;
        }

        addProductToDetails(product);
    };

    return {
        ref,
        onFindProduct,
        addProductToDetails,
    };
};

export default useSearchProduct;
