import React, { useState, useEffect, Fragment, useRef } from "react";
import useClickOutside from "hooks/useClickOutside";
import Api from "root/api.js";
import TextField from "components/shared/TextField.js";
import useDebounce from "hooks/useDebounce.js";
import Skeleton from "react-skeleton-loader";
import { ChevronDown } from "lucide-react";

const ItemSkeleton = ({ count = 1 }) => {
    function range(size, startAt = 0) {
        return [...Array(size).keys()].map((i) => i + startAt);
    }
    const array = range(count);

    return (
        <Fragment>
            {array.map((_, index) => {
                return (
                    <Fragment key={index}>
                        <Skeleton width="100%" widthRandomness={0} />
                        <Skeleton width="50%" widthRandomness={0} />
                    </Fragment>
                );
            })}
        </Fragment>
    );
};
const DropdownWithApi = ({
    type,
    onChange,
    selected,
    customClass,
    customOptionsWidth,
    disabled,
    firstDataAsDefault,
    fetchOnRender,
    allowEmpty = false,
}) => {
    const [datas, setDatas] = useState([]);
    const [open, setOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadMore, setIsLoadMore] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(Number.POSITIVE_INFINITY);
    const [search, setSearch] = useState("");
    const clickRef = useRef();
    const textFieldRef = useRef();
    useClickOutside(clickRef, () => setOpen(false));
    const debounceInput = useDebounce((text) => setSearch(text), 500);

    const getUrl = () => {
        if (type == "product") {
            return "/api/products?sort_price=1";
        } else if (type == "draft sales order") {
            return "/api/draft_sales_orders?with_product=1&fresh=1";
        } else if (type == "purchase order") {
            return "/api/purchase_orders?with_product=1&valid=1";
        } else if (type == "customer") {
            return "/api/customers";
        } else if (type == "payment type") {
            return "/api/payment_types";
        } else if (type == "product-category") {
            return "/api/product_categories";
        }
    };
    const getData = async () => {
        setIsLoading(true);
        const { data } = await Api(getUrl(), { search: search || null });

        if (firstDataAsDefault) {
            onChange(data.data[0]);
        }

        setCurrentPage(1);
        setLastPage(data.last_page);
        setDatas(data.data);
        setIsLoading(false);
    };

    const onHandleScroll = async (e) => {
        if (isLoadMore || currentPage == lastPage) return;

        const target = e.currentTarget;
        const scrollPercentage =
            (target.scrollTop / (target.scrollHeight - target.clientHeight)) *
            100;
        if (scrollPercentage > 80) {
            setIsLoadMore(true);
            const { data } = await Api(getUrl(), {
                search: search || null,

                page: currentPage + 1,
            });
            setCurrentPage(currentPage + 1);
            setDatas([...datas, ...data.data]);
            setTimeout(() => {
                setIsLoadMore(false);
            }, 800);
        }
    };

    const onHandeSelectData = (data) => {
        onChange(data);
        setOpen(false);
    };

    const renderOptions = (data) => {
        if (type == "product") {
            return (
                <>
                    <p>{data.name}</p>
                    <p className="text-xs text-gray-400">
                        {" "}
                        {data.code}
                        {" / Rp. "}
                        {data.price.toLocaleString()}
                    </p>
                </>
            );
        } else if (type == "draft sales order") {
            return (
                <>
                    <p>
                        # {data.id} / {data.user.name}{" "}
                    </p>
                </>
            );
        } else if (type == "purchase order") {
            return (
                <>
                    <p>{data.purchase_number}</p>
                </>
            );
        } else if (type == "customer" || type == "payment type") {
            return (
                <>
                    <p> {data.name} </p>
                </>
            );
        } else if (type == "product-category") {
            return (
                <>
                    <p> {data.name} </p>
                </>
            );
        }
    };

    const getSelected = () => {
        if (!selected) return;

        if (
            type == "product" ||
            type == "customer" ||
            type == "payment type" ||
            type == "product-category"
        ) {
            return selected?.name;
        } else if (type == "draft sales order") {
            return `# ${selected?.id} / ${selected?.user?.name}`;
        } else if (type == "purchase order") {
            return `${selected?.purchase_number}`;
        }
    };

    useEffect(() => {
        if (fetchOnRender) {
            getData();
        }
    }, []);

    useEffect(() => {
        if (open) {
            getData();
            textFieldRef.current.focus();
        }
    }, [open, search]);

    return (
        <div ref={clickRef} className="relative mb-5">
            <button
                type="button"
                className={`group h-[34px] inline-flex justify-between items-center w-full rounded-md border border-gray-300 \
              shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 \
              ${disabled ? "bg-gray-300 hover:bg-gray-300" : ""} \
              ${customClass}`}
                id="options-menu"
                onClick={() => setOpen(!open)}
                disabled={disabled}
            >
                <div className="w-full flex justify-between">
                    <span className="truncate">
                        {selected && type == "product" && (
                            <span className="relative">
                                <span className="fixed -translate-y-full w-max bg-black text-white text-xs rounded px-2 py-1 group-hover:opacity-100 opacity-0 transition-opacity pointer-events-none z-20">
                                    {selected?.name}
                                </span>
                            </span>
                        )}
                        {getSelected() || `Pilih ${type}`}
                    </span>
                    <ChevronDown className="w-5 h-5" />
                </div>
            </button>
            {open && (
                <div
                    className={`z-10 origin-top-left absolute left-0 mt-2 rounded-md
                shadow-lg bg-white ring-1 ring-black ring-opacity-5
                ${customOptionsWidth ? customOptionsWidth : "w-full"}`}
                >
                    <div
                        className="p-1 pt-16"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="options-menu"
                    >
                        <div className="p-4 absolute top-0 w-full">
                            <TextField
                                propsRef={textFieldRef}
                                onChange={(e) => {
                                    debounceInput(e.currentTarget.value);
                                }}
                                placeholder="Cari"
                                noMargin={true}
                            />
                        </div>
                        <div
                            className="max-h-40 overflow-auto"
                            onScroll={onHandleScroll}
                        >
                            {isLoading && <ItemSkeleton count={3} />}
                            {!isLoading && allowEmpty && (
                                <div
                                    className="block py-2  px-4 text-sm text-gray-700 hover:bg-gray-100
                        hover:text-gray-900 cursor-pointer"
                                    onClick={() => onHandeSelectData(null)}
                                >
                                    <p>Pilih {type}</p>
                                </div>
                            )}
                            {!isLoading &&
                                datas.map((data) => {
                                    return (
                                        <Fragment key={data.id}>
                                            <div
                                                className="block py-2  px-4 text-sm text-gray-700 hover:bg-gray-100
                            hover:text-gray-900 cursor-pointer"
                                                onClick={() =>
                                                    onHandeSelectData(data)
                                                }
                                            >
                                                {renderOptions(data)}
                                            </div>
                                        </Fragment>
                                    );
                                })}
                            {isLoadMore && <ItemSkeleton count={2} />}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DropdownWithApi;
