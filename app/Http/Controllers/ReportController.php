<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report_sales(Request $request) {
        $details = SalesOrderDetail::select('sales_order_id', DB::raw('SUM(subtotal) as total_amount'))
                        ->active()
                        ->groupBy('sales_order_id');

        $default_scope =
            SalesOrder::with('customer')
                ->select('*', DB::raw('(total_amount - sales_discount - total_return) as final_amount'))
                ->leftJoinSub($details, 'details', function ($join) {
                    $join->on('sales_orders.id', '=', 'details.sales_order_id');
                })
                ->active();


        $start = $request->start_date;
        $end = $request->end_date;
        $customer_id = $request->customer_id;
        if( $start && $end ){
            $default_scope = $default_scope->whereBetween('date', [$start, $end]);
        }
        if(isset($customer_id)){
            $default_scope = $default_scope->where('customer_id', $customer_id);
        }

        $reports = $default_scope->get();
        $response = ['data' => $reports, 'total' => $reports->sum('final_amount')];
        return response()->json($response, 200);
    }

    public function report_purchase(Request $request) {
        $details = PurchaseOrderDetail::select('purchase_order_id', DB::raw('SUM(subtotal) as total_amount'))
                        ->active()
                        ->groupBy('purchase_order_id');

        $default_scope =
            PurchaseOrder::select('*', DB::raw('(total_amount - purchase_discount) as final_amount'))
                ->leftJoinSub($details, 'details', function ($join) {
                    $join->on('purchase_orders.id', '=', 'details.purchase_order_id');
                })
                ->active();

        $start = $request->start_date;
        $end = $request->end_date;
        if( $start && $end ){
            $default_scope =
                $default_scope->whereBetween('created_at', [$start, $end]);
        }

        $reports = $default_scope->get();
        $response = ['data' => $reports, 'total' => $reports->sum('final_amount')];
        return response()->json($response, 200);
    }

    public function export_sales(Request $request) {
        $start = $request->start_date;
        $end = $request->end_date;
        $customer_id = $request->customer_id;

        $default_scope =
            SalesOrder::with('customer', 'details.product')->active();

        if( $start && $end ){
            $default_scope = $default_scope->whereBetween('date', [$start, $end]);
        }
        if(isset($customer_id)){
            $default_scope = $default_scope->where('customer_id', $customer_id);
        }

        $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new SalesExport($default_scope), $filename);
    }
}
