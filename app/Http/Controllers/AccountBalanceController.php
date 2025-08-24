<?php

namespace App\Http\Controllers;

use App\Services\AccountBalanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccountBalanceController extends Controller
{
    protected $balanceService;

    public function __construct(AccountBalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Get balance sheet data
     */
    public function balanceSheet(Request $request)
    {
        $periodIdentifier = $request->get('period_identifier');

        try {
            $balanceSheet = $this->balanceService->getBalanceSheetData($periodIdentifier);

            return response()->json([
                'success' => true,
                'data' => $balanceSheet
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching balance sheet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get asset balances
     */
    public function assets(Request $request)
    {
        $periodIdentifier = $request->get('period_identifier');

        try {
            $assets = $this->balanceService->getAssetBalances($periodIdentifier);

            return response()->json([
                'success' => true,
                'data' => [
                    'assets' => $assets,
                    'total' => $assets->sum('balance'),
                    'period' => [
                        'identifier' => $periodIdentifier ?? Carbon::now()->format('Y-m')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching asset balances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get liability balances
     */
    public function liabilities(Request $request)
    {
        $periodIdentifier = $request->get('period_identifier');

        try {
            $liabilities = $this->balanceService->getLiabilityBalances($periodIdentifier);

            return response()->json([
                'success' => true,
                'data' => [
                    'liabilities' => $liabilities,
                    'total' => $liabilities->sum('balance'),
                    'period' => [
                        'identifier' => $periodIdentifier ?? Carbon::now()->format('Y-m')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching liability balances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get equity balances
     */
    public function equity(Request $request)
    {
        $periodIdentifier = $request->get('period_identifier');

        try {
            $equity = $this->balanceService->getEquityBalances($periodIdentifier);

            return response()->json([
                'success' => true,
                'data' => [
                    'equity' => $equity,
                    'total' => $equity->sum('balance'),
                    'period' => [
                        'identifier' => $periodIdentifier ?? Carbon::now()->format('Y-m')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching equity balances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trial balance
     */
    public function trialBalance(Request $request)
    {
        $periodIdentifier = $request->get('period_identifier');

        try {
            $trialBalance = $this->balanceService->getTrialBalance($periodIdentifier);

            return response()->json([
                'success' => true,
                'data' => $trialBalance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching trial balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grouped balances by account type and parent
     */
    public function groupedBalances(Request $request)
    {
        $periodIdentifier = $request->get('period_identifier');

        try {
            $groupedBalances = $this->balanceService->getGroupedBalances($periodIdentifier);

            return response()->json([
                'success' => true,
                'data' => [
                    'grouped_balances' => $groupedBalances,
                    'period' => [
                        'identifier' => $periodIdentifier ?? Carbon::now()->format('Y-m')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching grouped balances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate and store balances
     */
    public function calculateBalances(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();

        try {
            $this->balanceService->calculateAndStoreBalances($date);

            return response()->json([
                'success' => true,
                'message' => 'Monthly account balances calculated successfully',
                'data' => [
                    'date' => $date->format('Y-m-d'),
                    'period_identifier' => $date->format('Y-m')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating balances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rebuild balances for a month
     */
    public function rebuildBalances(Request $request)
    {
        $request->validate([
            'period_identifier' => 'nullable|string',
        ]);

        $periodIdentifier = $request->get('period_identifier');

        try {
            $this->balanceService->rebuildBalances($periodIdentifier);

            return response()->json([
                'success' => true,
                'message' => 'Monthly account balances rebuilt successfully',
                'data' => [
                    'period_identifier' => $periodIdentifier ?? Carbon::now()->format('Y-m')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rebuilding balances: ' . $e->getMessage()
            ], 500);
        }
    }
}
