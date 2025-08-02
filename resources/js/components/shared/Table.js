import { Fragment } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

const Table = ({
    currentPage,
    lastPage,
    goToPage,
    tableInstance,
    pagination = true,
}) => {
    const {
        allColumns,
        getTableProps,
        getTableBodyProps,
        headerGroups,
        rows,
        prepareRow,
        pageOptions,
        page,
        state: { pageIndex, pageSize },
        gotoPage,
        previousPage,
        nextPage,
        setPageSize,
        canPreviousPage,
        canNextPage,
    } = tableInstance;

    // const { headers } = headerGroups;

    const listPages = [];
    // we try to maintain at least 3 page
    // unless there is less than 3 pages of data

    if (currentPage == lastPage && currentPage - 2 >= 1) {
        listPages.push(currentPage - 2);
    }
    if (currentPage - 1 && currentPage - 1 >= 1) {
        listPages.push(currentPage - 1);
    }

    listPages.push(currentPage);

    if (currentPage + 1 && currentPage + 1 <= lastPage) {
        listPages.push(currentPage + 1);
    }

    if (currentPage == 1 && currentPage + 2 <= lastPage) {
        listPages.push(currentPage + 2);
    }

    return (
        <Fragment>
            <table className="border-collapse w-full mb-2" {...getTableProps()}>
                <thead>
                    {headerGroups.map((headerGroup) => (
                        <tr
                            className="p-3 font-bold uppercase bg-gray-200 text-gray-600 border border-gray-300"
                            {...headerGroup.getHeaderGroupProps()}
                        >
                            {headerGroup.headers.map((column) => (
                                <th {...column.getHeaderProps()}>
                                    {column.render("Header")}
                                </th>
                            ))}
                        </tr>
                    ))}
                </thead>
                <tbody {...getTableBodyProps()}>
                    {rows.length == 0 && (
                        <tr className="bg-white lg:hover:bg-gray-100 mb-10">
                            <td
                                colspan={allColumns.length}
                                className="p-1.5 text-gray-800 text-center border border-b"
                            >
                                {" "}
                                No Data Available{" "}
                            </td>
                        </tr>
                    )}
                    {rows.length > 0 &&
                        rows.map((row) => {
                            prepareRow(row);
                            return (
                                <tr
                                    className="bg-white lg:hover:bg-gray-100 mb-10"
                                    {...row.getRowProps()}
                                >
                                    {row.cells.map((cell) => {
                                        return (
                                            <td
                                                className={`p-1.5 text-sm text-gray-800 ${
                                                    cell.column.justifyText ??
                                                    "text-center"
                                                }  border border-b`}
                                                {...cell.getCellProps()}
                                            >
                                                {cell.render("Cell")}
                                            </td>
                                        );
                                    })}
                                </tr>
                            );
                        })}
                </tbody>
            </table>
            {pagination && (
                <div className="w-full flex justify-end">
                    <nav className="relative z-0 inline-flex shadow-sm">
                        {currentPage !== 1 && (
                            <div>
                                <div
                                    onClick={() => goToPage(currentPage - 1)}
                                    className="cursor-pointer relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm leading-5 font-medium text-gray-500 hover:text-gray-400 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150"
                                >
                                    <ChevronLeft className="h-5 w-5" />
                                </div>
                            </div>
                        )}
                        <div>
                            {listPages.map((page) => {
                                return (
                                    <div
                                        onClick={
                                            page !== currentPage
                                                ? () => goToPage(page)
                                                : null
                                        }
                                        className={`cursor-pointer -ml-px relative inline-flex items-center px-4 py-2 border
												border-gray-300 bg-white text-sm font-medium leading-5
												ease-in-out duration-150
												${
                                                    page == currentPage
                                                        ? "text-blue-700 z-10 outline-none border-blue-300 shadow-outline-blue"
                                                        : "text-gray-500"
                                                }
												`}
                                    >
                                        {page}
                                    </div>
                                );
                            })}
                        </div>
                        {currentPage !== lastPage && (
                            <div>
                                <div
                                    onClick={() => goToPage(currentPage + 1)}
                                    className="cursor-pointer -ml-px relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm leading-5 font-medium text-gray-500 hover:text-gray-400 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150"
                                    aria-label="Next"
                                >
                                    <ChevronRight className="h-5 w-5" />
                                </div>
                            </div>
                        )}
                    </nav>
                </div>
            )}
        </Fragment>
    );
};

export default Table;
