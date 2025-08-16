import React, { Fragment, useState, useEffect, useContext } from "react";
import { Switch, Route, useHistory, Redirect } from "react-router-dom";
import MenuItem from "./shared/MenuItem.js";
import Product from "./pages/Product.js";
import Customer from "./pages/Customer.js";
import CreateCustomer from "./pages/CreateCustomer.js";
import PaymentType from "./pages/PaymentType.js";
import CreatePaymentType from "./pages/CreatePaymentType.js";
import User from "./pages/User.js";
import CreateProduct from "./pages/CreateProduct.js";
import CreateDraftSalesOrder from "./pages/CreateDraftSalesOrder.js";
import CreateSalesOrder from "./pages/CreateSalesOrder.js";
import CreateUser from "./pages/CreateUser.js";
import DraftSalesOrder from "./pages/DraftSalesOrder.js";
import SalesOrder from "./pages/SalesOrder.js";
import SalesReturn from "./pages/SalesReturn.js";
import CreateSalesReturn from "./pages/CreateSalesReturn.js";
import ChangePassword from "./pages/ChangePassword.js";
import PurchaseOrder from "./pages/PurchaseOrder.js";
import CreatePurchaseOrder from "./pages/CreatePurchaseOrder.js";
import DeliveryNote from "./pages/DeliveryNote.js";
import CreateDeliveryNote from "./pages/CreateDeliveryNote.js";
import PurchaseReturn from "./pages/PurchaseReturn.js";
import CreatePurchaseReturn from "./pages/CreatePurchaseReturn.js";
import ReportPurchase from "./pages/ReportPurchase.js";
import ReportSales from "./pages/ReportSales.js";
import ReportProfitLoss from "./pages/ReportProfitLoss.js";
import Api from "root/api.js";
import UserContext, { UserProvider } from "root/context/UserContext.js";
import Avatar from "react-avatar";
import ProductCategory from "./pages/ProductCategory.js";
import CreateProductCategory from "./pages/CreateProductCategory.js";
import Journal from "./pages/Journal.js";
import Ledger from "./pages/Ledger.js";
import CapitalContribution from "./pages/CapitalContribution.js";
import ProductLog from "./pages/ProductLog.js";
import ExpenseTransaction from "./pages/ExpenseTransaction.js";

const ROLE_DICT = {
    sales: "Staff Sales",
    cashier: "Kasir",
    admin: "Administrator",
    backoffice: "Back Office",
};

const ROUTES = [
    // {
    //     role: "sales",
    //     path: ["/draft-sales-order/create", "/draft-sales-order/edit/:id"],
    //     component: CreateDraftSalesOrder,
    // },
    // {
    //     role: "sales",
    //     path: "/draft-sales-order",
    //     component: DraftSalesOrder,
    // },
    {
        role: "cashier",
        path: ["/sales-order/create", "/sales-order/edit/:id"],
        component: CreateSalesOrder,
    },
    {
        role: "cashier",
        path: "/sales-order",
        component: SalesOrder,
    },
    {
        role: "cashier",
        path: ["/sales-return/create", "/sales-return/edit/:id"],
        component: CreateSalesReturn,
    },
    {
        role: "cashier",
        path: "/sales-return",
        component: SalesReturn,
    },
    {
        role: "admin",
        path: "/user/create",
        component: CreateUser,
    },
    {
        role: "admin",
        path: "/user",
        component: User,
    },
    {
        role: "cashier",
        path: ["/customer/create", "/customer/edit/:id"],
        component: CreateCustomer,
    },
    {
        role: "admin",
        path: "/payment_type",
        component: PaymentType,
    },
    {
        role: "admin",
        path: ["/payment_type/create", "/payment_type/edit/:id"],
        component: CreatePaymentType,
    },
    {
        role: "cashier",
        path: "/customer",
        component: Customer,
    },
    {
        role: "backoffice",
        path: ["/product/create", "/product/edit/:id"],
        component: CreateProduct,
    },
    {
        role: "backoffice",
        path: "/product",
        component: Product,
    },
    {
        role: "backoffice",
        path: "/product/:id/logs",
        component: ProductLog,
    },
    {
        role: "backoffice",
        path: ["/product-category/create", "/product-category/edit/:id"],
        component: CreateProductCategory,
    },
    {
        role: "backoffice",
        path: "/product-category",
        component: ProductCategory,
    },
    {
        role: "all",
        path: "/change-password",
        component: ChangePassword,
    },
    {
        role: "backoffice",
        path: "/purchase-order",
        component: PurchaseOrder,
    },
    {
        role: "backoffice",
        path: ["/purchase-order/create", "/purchase-order/edit/:id"],
        component: CreatePurchaseOrder,
    },
    {
        role: "backoffice",
        path: "/delivery-note",
        component: DeliveryNote,
    },
    {
        role: "backoffice",
        path: ["/delivery-note/create", "/delivery-note/edit/:id"],
        component: CreateDeliveryNote,
    },
    {
        role: "backoffice",
        path: "/purchase-return",
        component: PurchaseReturn,
    },
    {
        role: "cashier",
        path: ["/purchase-return/create", "/purchase-return/edit/:id"],
        component: CreatePurchaseReturn,
    },
    {
        role: "admin",
        path: "/report-purchase",
        component: ReportPurchase,
    },
    {
        role: "admin",
        path: "/report-sales",
        component: ReportSales,
    },
    {
        role: "admin",
        path: "/report-profit-loss",
        component: ReportProfitLoss,
    },
    {
        role: "admin",
        path: "/journal",
        component: Journal,
    },
    {
        role: "admin",
        path: "/ledger",
        component: Ledger,
    },
    {
        role: "admin",
        path: "/capital-contribution",
        component: CapitalContribution,
    },
    {
        role: "admin",
        path: "/expense-transaction",
        component: ExpenseTransaction,
    },
];

const DEFAULT_PAGE_BY_ROLE = {
    sales: "sales-order",
    cashier: "sales-order",
    admin: "sales-order",
    backoffice: "purchase-order",
};

function Main() {
    const history = useHistory();
    const [loading, setLoading] = useState(true);
    const { user, setUser } = useContext(UserContext);

    const logout = async () => {
        localStorage.removeItem("token");
        history.push("/login");
    };

    const getAuthInfo = async () => {
        const response = await Api("/api/auth");
        console.log(response);
        setUser(response.data.data);
        setLoading(false);
    };

    const isAllowedFor = (role) => {
        const userRole = user.role;
        if (userRole === "admin" || userRole == role) return true;

        return false;
    };

    const renderRoutes = () => {
        const filteredRoutes = ROUTES.filter((route) => {
            return route.role == "all" || isAllowedFor(route.role);
        });
        console.log("filteredRoutes", filteredRoutes);
        return filteredRoutes.map((route) => {
            return (
                <Route
                    path={route.path}
                    component={route.component}
                    exact={true}
                />
            );
        });
    };

    useEffect(() => {
        getAuthInfo();
    }, []);

    return (
        <>
            {!loading && (
                <div className="w-full h-full">
                    <div className="grid grid-rows-6 grid-cols-12 h-full">
                        <div className="row-span-6 col-span-2 overflow-auto">
                            <div className="mt-5 bg-gray-100 ">
                                <div className="flex justify-center">
                                    <div className="">
                                        <img
                                            className="ring-2 ring-gray-300 rounded-lg w-16 h-16"
                                            src="assets/canvas.png"
                                        ></img>
                                    </div>
                                </div>
                                <div className="flex items-center mt-3 p-3 border-b">
                                    <Avatar
                                        name={user.name}
                                        size="32"
                                        round={true}
                                    />
                                    <div>
                                        <p className="ml-3 font-medium">
                                            {user.name}
                                        </p>
                                        <p className="ml-3 text-sm text-gray-500 ">
                                            {ROLE_DICT[user.role]}
                                        </p>
                                    </div>
                                </div>
                                <div className="border-b">
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="User"
                                            icon="Users"
                                            onClick={() =>
                                                history.push("/user")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("cashier") && (
                                        <MenuItem
                                            title="Customer"
                                            icon="UserCheck"
                                            onClick={() =>
                                                history.push("/customer")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Tipe Pembayaran"
                                            icon="CreditCard"
                                            onClick={() =>
                                                history.push("/payment_type")
                                            }
                                        />
                                    )}
                                    {
                                        <MenuItem
                                            title="Ganti Password"
                                            icon="Shield"
                                            onClick={() =>
                                                history.push("/change-password")
                                            }
                                        />
                                    }
                                </div>
                                <div className="border-b">
                                    {isAllowedFor("backoffice") && (
                                        <MenuItem
                                            title="Pembelian"
                                            icon="FileText"
                                            onClick={() =>
                                                history.push("/purchase-order")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("backoffice") && (
                                        <MenuItem
                                            title="Bukti Penerimaan"
                                            icon="Receipt"
                                            onClick={() =>
                                                history.push("/delivery-note")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("backoffice") && (
                                        <MenuItem
                                            title="Retur Pembelian"
                                            icon="RotateCcw"
                                            onClick={() =>
                                                history.push("/purchase-return")
                                            }
                                        />
                                    )}
                                </div>
                                <div className="border-b">
                                    {/* {isAllowedFor("sales") && (
                                        <MenuItem
                                            title="Draft Penjualan"
                                            icon="article"
                                            onClick={() =>
                                                history.push(
                                                    "/draft-sales-order"
                                                )
                                            }
                                        />
                                    )} */}
                                    {isAllowedFor("cashier") && (
                                        <MenuItem
                                            title="Penjualan"
                                            icon="ShoppingCart"
                                            onClick={() =>
                                                history.push("/sales-order")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("cashier") && (
                                        <MenuItem
                                            title="Retur Penjualan"
                                            icon="RotateCcw"
                                            onClick={() =>
                                                history.push("/sales-return")
                                            }
                                        />
                                    )}
                                </div>
                                <div className="border-b">
                                    {isAllowedFor("backoffice") && (
                                        <MenuItem
                                            title="Produk"
                                            icon="Package"
                                            onClick={() =>
                                                history.push("/product")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("backoffice") && (
                                        <MenuItem
                                            title="Kategori Produk"
                                            icon="Layers"
                                            onClick={() =>
                                                history.push(
                                                    "/product-category"
                                                )
                                            }
                                        />
                                    )}
                                </div>
                                <div className="border-b">
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Report Penjualan"
                                            icon="BarChart3"
                                            onClick={() =>
                                                history.push("/report-sales")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Report Pembelian"
                                            icon="TrendingUp"
                                            onClick={() =>
                                                history.push("/report-purchase")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Laporan Laba Rugi"
                                            icon="PieChart"
                                            onClick={() =>
                                                history.push(
                                                    "/report-profit-loss"
                                                )
                                            }
                                        />
                                    )}
                                </div>
                                <div className="border-b">
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Penambahan Modal"
                                            icon="BookOpen"
                                            onClick={() =>
                                                history.push(
                                                    "/capital-contribution"
                                                )
                                            }
                                        />
                                    )}
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Biaya"
                                            icon="BookOpen"
                                            onClick={() =>
                                                history.push(
                                                    "/expense-transaction"
                                                )
                                            }
                                        />
                                    )}
                                </div>
                                <div className="border-b">
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Jurnal Umum"
                                            icon="BookOpen"
                                            onClick={() =>
                                                history.push("/journal")
                                            }
                                        />
                                    )}
                                    {isAllowedFor("admin") && (
                                        <MenuItem
                                            title="Buku Besar"
                                            icon="BookOpen"
                                            onClick={() =>
                                                history.push("/ledger")
                                            }
                                        />
                                    )}
                                </div>
                                <MenuItem
                                    title="Logout"
                                    icon="LogOut"
                                    onClick={() => logout()}
                                />
                            </div>
                        </div>
                        <div className="row-span-6 col-span-10 bg-white relative h-full overflow-auto">
                            <Switch>
                                {renderRoutes()}
                                <Redirect
                                    to={DEFAULT_PAGE_BY_ROLE[user.role]}
                                />
                            </Switch>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}

const MainWrapper = () => {
    return (
        <UserProvider>
            <Main />
        </UserProvider>
    );
};

export default MainWrapper;
