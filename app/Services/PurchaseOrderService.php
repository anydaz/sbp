<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Events\PurchaseOrderCreated;
use App\Events\PurchaseOrderUpdated;
use App\Events\PurchaseOrderDeleted;

class PurchaseOrderService
{
    public function getPurchaseOrders($withProduct = false, $search = null, $valid = false)
    {
        $query = PurchaseOrder::active()->with('user', 'delivery_notes', 'details.delivery_details');

        if ($withProduct) {
            $query = $query->with('details.product');
        }

        if ($search) {
            $query = $query->where('id', 'like', '%' . $search . '%')
                ->orWhere('supplier', 'like', '%' . $search . '%');
        }

        if ($valid) {
            $query = $this->addValidPurchaseOrderScope($query);
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    private function addValidPurchaseOrderScope($query)
    {
        return $query->whereExists(function ($query) {
            $total_received_query = DB::table('delivery_note_details')
                ->select('purchase_order_detail_id', DB::raw('SUM(received_qty) as total_received_qty'))
                ->where('state', 'active')
                ->groupBy('purchase_order_detail_id');

            $query->select(DB::raw(1))
                ->from('purchase_order_details')
                ->where('state', 'active')
                ->leftJoinSub($total_received_query, 'total_received', function ($join) {
                    $join->on('purchase_order_details.id', '=', 'total_received.purchase_order_detail_id');
                })
                ->whereRaw('(total_received.total_received_qty < purchase_order_details.qty OR total_received_qty IS NULL)')
                ->whereColumn('purchase_order_details.purchase_order_id', 'purchase_orders.id');
        });
    }

    public function createPurchaseOrder($data, $details, $userId)
    {
        try {
            $purchase_data = DB::transaction(function () use ($data, $details, $userId) {
                $data['user_id'] = $userId;
                $details_total = array_sum(array_column($details, 'subtotal'));
                $discount = $data['purchase_discount'] ?? 0;
                $data['total'] = $details_total + $data['shipping_cost'] - $discount;
                $data['shipping_cost_per_item'] = $data['shipping_cost'] / array_sum(array_column($details, 'qty'));

                $purchase = PurchaseOrder::create($data);
                $purchase->details()->createMany($details);

                event(new PurchaseOrderCreated($purchase));

                return $purchase;
            });

            return PurchaseOrder::with('details.product')->find($purchase_data->id);
        } catch (\Exception $error) {
            throw $error;
        }
    }

    public function getPurchaseOrder($id)
    {
        return PurchaseOrder::with('user', 'details.product')->findOrFail($id);
    }

    public function updatePurchaseOrder($id, $data, $details)
    {
        return DB::transaction(function () use ($id, $data, $details) {
            $purchase = PurchaseOrder::with('details')->findOrFail($id);

            // Store original purchase order for the event
            $originalPurchaseOrder = clone $purchase;

            $purchase->details()->update(['state' => 'deleted']);
            $purchase->details()->createMany($details);

            // Calculate totals
            $details_total = array_sum(array_column($details, 'subtotal'));
            $discount = $data['purchase_discount'] ?? 0;
            $data['total'] = $details_total + $data['shipping_cost'] - $discount;
            $data['shipping_cost_per_item'] = $data['shipping_cost'] / array_sum(array_column($details, 'qty'));

            $purchase->update($data);

            $updatedPurchase = PurchaseOrder::with('details.product')->find($purchase->id);

            // Dispatch update event
            event(new PurchaseOrderUpdated($updatedPurchase, $originalPurchaseOrder));

            return $updatedPurchase;
        });
    }

    public function deletePurchaseOrder($id)
    {
        return DB::transaction(function () use ($id) {
            $purchase = PurchaseOrder::with('details')->findOrFail($id);
            $purchase->details()->update(['state' => 'deleted']);
            $purchase->state = "deleted";
            $purchase->save();

            // Dispatch delete event
            event(new PurchaseOrderDeleted($purchase));

            return $purchase;
        });
    }
}
