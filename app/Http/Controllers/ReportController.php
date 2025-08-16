<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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

    public function profit_loss(Request $request) {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // Get revenue accounts and their balances
        $revenueAccounts = Account::where('type', 'revenue')
            ->where('parent_account_id', '!=', null)
            ->get();

        $revenueData = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $startDate, $endDate, 'revenue');
            // Only include accounts with actual balances to keep the report clean
            if ($balance > 0) {
                $revenueData[] = [
                    'account_name' => $account->name,
                    'account_code' => $account->code,
                    'balance' => $balance
                ];
                $totalRevenue += $balance;
            }
        }

        // Get COGS accounts - focus on child accounts where actual transactions are recorded
        // Include both parent and child COGS accounts to cover all scenarios
        $cogsAccounts = Account::where('type', 'expense')
            ->where(function($query) {
                $query->where('code', 'LIKE', '5%')
                      ->orWhere('name', 'LIKE', '%cost of goods%')
                      ->orWhere('name', 'LIKE', '%cogs%')
                      ->orWhere('name', 'LIKE', '%harga pokok%');
            })
            ->get();

        $cogsData = [];
        $totalCOGS = 0;

        foreach ($cogsAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $startDate, $endDate, 'expense');
            // Only include accounts with actual balances to keep the report clean
            if ($balance > 0) {
                $cogsData[] = [
                    'account_name' => $account->name,
                    'account_code' => $account->code,
                    'balance' => $balance
                ];
                $totalCOGS += $balance;
            }
        }

        // Calculate Gross Profit
        $grossProfit = $totalRevenue - $totalCOGS;

        // Get operating expense accounts (expense accounts excluding COGS - typically accounts with codes 6xxx and above)
        $operatingExpenseAccounts = Account::where('type', 'expense')
            ->where(function($query) {
                $query->where('code', 'NOT LIKE', '5%')
                      ->where('name', 'NOT LIKE', '%cost of goods%')
                      ->where('name', 'NOT LIKE', '%cogs%')
                      ->where('name', 'NOT LIKE', '%harga pokok%');
            })
            ->get();

        $operatingExpenseData = [];
        $totalOperatingExpenses = 0;

        foreach ($operatingExpenseAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $startDate, $endDate, 'expense');
            // Only include accounts with actual balances to keep the report clean
            if ($balance > 0) {
                $operatingExpenseData[] = [
                    'account_name' => $account->name,
                    'account_code' => $account->code,
                    'balance' => $balance
                ];
                $totalOperatingExpenses += $balance;
            }
        }

        // Calculate Net Income
        $netIncome = $grossProfit - $totalOperatingExpenses;

        $response = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'revenue' => [
                'accounts' => $revenueData,
                'total' => $totalRevenue
            ],
            'cogs' => [
                'accounts' => $cogsData,
                'total' => $totalCOGS
            ],
            'gross_profit' => $grossProfit,
            'operating_expenses' => [
                'accounts' => $operatingExpenseData,
                'total' => $totalOperatingExpenses
            ],
            'net_income' => $netIncome
        ];

        return response()->json($response, 200);
    }

    private function getAccountBalance($accountId, $startDate, $endDate, $accountType) {
        $entries = JournalEntry::where('account_id', $accountId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->first();

        $totalDebit = $entries->total_debit ?? 0;
        $totalCredit = $entries->total_credit ?? 0;

        // For revenue accounts, credit increases balance (revenue)
        // For expense accounts, debit increases balance (expense)
        if ($accountType === 'revenue') {
            return $totalCredit - $totalDebit;
        } else {
            return $totalDebit - $totalCredit;
        }
    }
}
