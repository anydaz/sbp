<?php

namespace App\Http\Controllers;

use App\Models\PurchasePayment;
use App\Models\PurchaseOrder;
use App\Events\PurchasePaymentCreated;
use App\Events\PurchasePaymentUpdated;
use App\Events\PurchasePaymentDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    /**
     * Display a listing of the purchase payments.
     */
    public function index()
    {
        $payments = PurchasePayment::with('purchase_order')
            ->active()
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return response()->json($payments);
    }

    /**
     * Store a newly created purchase payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Get the purchase order to validate payment amount
            $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);

            // Calculate remaining amount
            $totalPaid = PurchasePayment::where('purchase_order_id', $purchaseOrder->id)
                ->active()
                ->sum('amount');

            // Include down payment in total paid amount
            $totalPaidIncludingDownPayment = $totalPaid + ($purchaseOrder->down_payment ?? 0);
            $remainingAmount = $purchaseOrder->total - $totalPaidIncludingDownPayment;

            // Validate payment amount doesn't exceed remaining amount
            if ($request->amount > $remainingAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds remaining balance',
                    'errors' => ['amount' => ['Jumlah pembayaran melebihi sisa tagihan']]
                ], 422);
            }

            $payment = PurchasePayment::create([
                'purchase_order_id' => $request->purchase_order_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
            ]);

            // Fire the event for journal entry creation
            PurchasePaymentCreated::dispatch($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $payment->load('purchase_order'),
                'message' => 'Payment created successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase payment.
     */
    public function show($id)
    {
        $payment = PurchasePayment::with('purchase_order')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update the specified purchase payment.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $payment = PurchasePayment::findOrFail($id);
            $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);

            // Calculate remaining amount excluding current payment
            $totalPaid = PurchasePayment::where('purchase_order_id', $purchaseOrder->id)
                ->where('id', '!=', $payment->id)
                ->active()
                ->sum('amount');

            // Include down payment in total paid amount
            $totalPaidIncludingDownPayment = $totalPaid + ($purchaseOrder->down_payment ?? 0);
            $remainingAmount = $purchaseOrder->total - $totalPaidIncludingDownPayment;

            // Validate payment amount doesn't exceed remaining amount
            if ($request->amount > $remainingAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds remaining balance',
                    'errors' => ['amount' => ['Jumlah pembayaran melebihi sisa tagihan']]
                ], 422);
            }

            $payment->update([
                'purchase_order_id' => $request->purchase_order_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
            ]);

            // Fire the event for journal entry update
            PurchasePaymentUpdated::dispatch($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $payment->load('purchase_order'),
                'message' => 'Payment updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase payment.
     */
    public function destroy($id)
    {
        try {
            $payment = PurchasePayment::findOrFail($id);

            // Fire the event before deletion for journal entry reversal
            PurchasePaymentDeleted::dispatch($payment);

            $payment->update(['state' => 'inactive']);

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get credit purchase orders (orders with remaining balance)
     */
    public function getCreditPurchaseOrders()
    {
        try {
            // Get purchase orders that have payment_category indicating credit
            // or orders that have remaining balance
            $creditOrders = PurchaseOrder::with(['payment_category'])
                ->where('payment_category_id', '!=', 1)
                ->active()
                ->get()
                ->map(function ($order) {
                    $totalPaid = PurchasePayment::where('purchase_order_id', $order->id)
                        ->active()
                        ->sum('amount');

                    // Include down payment in total paid amount
                    $totalPaidIncludingDownPayment = $totalPaid + ($order->down_payment ?? 0);
                    $remainingAmount = $order->total - $totalPaidIncludingDownPayment;

                    return [
                        'id' => $order->id,
                        'purchase_number' => $order->purchase_number,
                        'supplier' => $order->supplier,
                        'total' => $order->total,
                        'down_payment' => $order->down_payment ?? 0,
                        'paid_amount' => $totalPaid,
                        'remaining_amount' => $remainingAmount,
                        'date' => $order->date,
                    ];
                })
                ->filter(function ($order) {
                    return $order['remaining_amount'] > 0; // Only orders with remaining balance
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $creditOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching credit purchase orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase order payment information
     */
    public function getPurchaseOrderPaymentInfo($purchaseOrderId)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['payment_category'])->findOrFail($purchaseOrderId);

            $totalPaid = PurchasePayment::where('purchase_order_id', $purchaseOrder->id)
                ->active()
                ->sum('amount');

            // Include down payment in total paid amount
            $totalPaidIncludingDownPayment = $totalPaid + ($purchaseOrder->down_payment ?? 0);
            $remainingAmount = $purchaseOrder->total - $totalPaidIncludingDownPayment;

            $paymentInfo = [
                'id' => $purchaseOrder->id,
                'purchase_number' => $purchaseOrder->purchase_number,
                'supplier' => $purchaseOrder->supplier,
                'total' => $purchaseOrder->total,
                'down_payment' => $purchaseOrder->down_payment ?? 0,
                'paid_amount' => $totalPaid,
                'remaining_amount' => $remainingAmount,
                'date' => $purchaseOrder->date,
                'payment_category' => $purchaseOrder->payment_category->name ?? null,
            ];

            return response()->json([
                'success' => true,
                'data' => $paymentInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching purchase order info: ' . $e->getMessage()
            ], 500);
        }
    }
}
