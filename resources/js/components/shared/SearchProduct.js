import Header from "components/shared/Header.js";
import TextField from "components/shared/TextField.js";
import { use, useEffect, useRef, useState } from "react";
import Api from "root/api.js";
import useDebounce from "hooks/useDebounce.js";

const SearchProduct = ({ setOnSearchProduct, onSelectProduct }) => {
    const [products, setProducts] = useState([]);
    const [searchQuery, setSearchQuery] = useState("");
    const [isLoadMore, setIsLoadMore] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(Number.POSITIVE_INFINITY);
    const ref = useRef(null);

    const fetchProducts = async () => {
        const response = await Api(`/api/products?sort_price=1`, {
            search: searchQuery,
        });
        if (response) {
            setProducts(response.data.data);
            setCurrentPage(1);
            setLastPage(data.last_page);
        }
    };

    useEffect(() => {
        fetchProducts(searchQuery);

        ref.current?.focus();
    }, [searchQuery]);

    useEffect(() => {
        const handleKeyDown = (event) => {
            console.log("key down", event.key);
            if (event.key === "Enter" && products.length > 0) {
                onSelectProduct(products[0]);
                setOnSearchProduct(false);
            }
            if (event.key === "Escape") {
                setOnSearchProduct(false);
            }
        };
        document.addEventListener("keydown", handleKeyDown);
        return () => {
            document.removeEventListener("keydown", handleKeyDown);
        };
    }, [products]);

    const debounceInput = useDebounce((text) => setSearchQuery(text), 500);

    const onHandleScroll = async (e) => {
        console.log("onHandleScroll", isLoadMore, currentPage, lastPage);
        if (isLoadMore || currentPage == lastPage) return;

        const target = e.currentTarget;
        const scrollPercentage =
            (target.scrollTop / (target.scrollHeight - target.clientHeight)) *
            100;
        if (scrollPercentage > 80) {
            setIsLoadMore(true);
            const { data } = await Api(`/api/products?sort_price=1`, {
                search: searchQuery || null,
                page: currentPage + 1,
            });
            setCurrentPage(currentPage + 1);
            setProducts([...products, ...data.data]);
            setTimeout(() => {
                setIsLoadMore(false);
            }, 800);
        }
    };

    return (
        <div>
            <Header
                withBackButton={true}
                title="Cari Produk"
                backAction={() => {
                    setOnSearchProduct(false);
                }}
            />
            <div className="p-4 h-full flex flex-col flex-grow min-h-0">
                <TextField
                    defaultValue=""
                    label="Input Nama Produk atau Kode"
                    customWrapperClass={"w-full"}
                    onChange={(e) => {
                        debounceInput(e.target.value);
                    }}
                    propsRef={ref}
                />
                {products.length > 0 ? (
                    <div
                        className="flex flex-col overflow-y-auto flex-grow min-h-0 max-h-[500px]"
                        onScroll={onHandleScroll}
                    >
                        {products.map((product) => (
                            <div
                                key={product.id}
                                className="flex justify-between items-center p-2 border-b border-gray-200 cursor-pointer hover:bg-gray-100"
                                onClick={() => {
                                    onSelectProduct(product);
                                    setOnSearchProduct(false);
                                }}
                            >
                                <div className="flex items-center">
                                    <div>
                                        <p className="text-md">
                                            {product.name}
                                        </p>
                                        <p className="text-sm text-gray-600">
                                            {" "}
                                            {product.efficiency_code}
                                            {" / Rp. "}
                                            {product.price.toLocaleString()}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="p-4 text-gray-600">
                        Tidak ada produk ditemukan
                    </p>
                )}
            </div>
        </div>
    );
};

export default SearchProduct;
