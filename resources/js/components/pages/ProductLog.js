import React, { useState, useEffect } from "react";
import Api from "../../api.js";
import { useParams, useHistory } from "react-router-dom";
import Header from "../shared/Header";
import { SecondaryButton } from "../shared/Button";
import Chip from "../shared/Chip";
import QuantityLogs from "./product-logs/QuantityLogs";
import CogsLogs from "./product-logs/CogsLogs";

const ProductLog = () => {
    const [product, setProduct] = useState(null);
    const [logType, setLogType] = useState("quantity");
    const { id } = useParams();
    const history = useHistory();

    useEffect(() => {
        const fetchProduct = async () => {
            try {
                const response = await Api(`/api/products/${id}`);
                setProduct(response.data);
            } catch (error) {
                console.error("Error fetching product:", error);
            }
        };

        fetchProduct();
    }, [id]);

    if (!product) {
        return <div className="p-4">Loading...</div>;
    }

    return (
        <>
            <Header
                title={`Product History: ${product.name}`}
                customActionsComponent={
                    <SecondaryButton
                        text="Back to Products"
                        onClick={() => history.push("/product")}
                        icon="ArrowLeft"
                    />
                }
            />
            <div className="p-4">
                <div className="bg-white rounded-lg shadow p-4 mb-4">
                    <div className="grid grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Code
                            </label>
                            <div className="mt-1">{product.code}</div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Category
                            </label>
                            <div className="mt-1">{product.category?.name}</div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Current Quantity
                            </label>
                            <div className="mt-1">
                                {parseFloat(product.quantity).toLocaleString(
                                    "id-ID"
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Current COGS
                            </label>
                            <div className="mt-1">
                                {parseFloat(product.cogs).toLocaleString(
                                    "id-ID"
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Current Price
                            </label>
                            <div className="mt-1">
                                {parseFloat(product.price).toLocaleString(
                                    "id-ID"
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col gap-4">
                    <Chip
                        items={[
                            { id: "quantity", title: "Quantity Changes" },
                            { id: "cogs", title: "COGS Changes" },
                        ]}
                        selectedId={logType}
                        handleClick={(type) => setLogType(type)}
                    />
                    <div>
                        {logType === "quantity" ? (
                            <QuantityLogs productId={id} />
                        ) : (
                            <CogsLogs productId={id} />
                        )}
                    </div>
                </div>
            </div>
        </>
    );
};

export default ProductLog;
