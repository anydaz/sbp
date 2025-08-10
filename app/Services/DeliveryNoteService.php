<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\DB;
use App\Events\DeliveryNoteCreated;

class DeliveryNoteService
{
    public function getAllDeliveryNotes()
    {
        return DeliveryNote::active()
            ->with('purchase_order', 'details')
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function getDeliveryNote($id)
    {
        return DeliveryNote::with([
            'purchase_order',
            'details.product',
            'details.purchase_order_detail.delivery_details'
        ])->find($id);
    }

    public function createDeliveryNote(array $data, $userId)
    {
        $data['user_id'] = $userId;
        $details = $data['details'];
        $purchaseOrder = PurchaseOrder::find($data['purchase_order_id']);
        $shippingCostPerItem = $purchaseOrder->shipping_cost_per_item ?? 0;

        return DB::transaction(function () use ($data, $details, $shippingCostPerItem) {
            $data['total'] = array_sum(array_column($details, 'received_value'));
            $delivery = DeliveryNote::create($data);
            $delivery->details()->createMany($details);

            foreach ($details as $detail) {
                $this->updateProductQuantityAndCogs(
                    $detail['product_id'],
                    $detail['received_qty'],
                    $detail["purchase_order_detail_id"],
                    $shippingCostPerItem
                );
            }

            event(new DeliveryNoteCreated($delivery));

            return DeliveryNote::with('details.product')->find($delivery->id);
        });
    }

    public function updateDeliveryNote($id, array $data)
    {
        $details = $data['details'];

        return DB::transaction(function () use ($id, $data, $details) {
            $delivery = DeliveryNote::with('details')->find($id);
            $purchaseOrder = PurchaseOrder::find($delivery->purchase_order_id);
            $shippingCostPerItem = $purchaseOrder->shipping_cost_per_item ?? 0;

            // Revert previous quantities and COGS
            foreach ($delivery->details as $detail) {
                $this->revertProductQuantityAndCogs(
                    $detail->product_id,
                    $detail->received_qty,
                    $detail->purchase_order_detail->price
                );
            }

            // Mark old details as deleted
            $delivery->details()->update(['state' => 'deleted']);

            // Create new details
            $delivery->details()->createMany($details);

            // Update quantities and COGS with new details
            foreach ($details as $detail) {
                $receivedPrice = PurchaseOrderDetail::find($detail["purchase_order_detail_id"])->price;
                $this->updateProductQuantityAndCogs(
                    $detail['product_id'],
                    $detail['received_qty'],
                    $detail["purchase_order_detail_id"],
                    $shippingCostPerItem
                );
            }

            $delivery->update($data);

            return DeliveryNote::with('details.product')->find($delivery->id);
        });
    }

    public function deleteDeliveryNote($id)
    {
        return DB::transaction(function () use ($id) {
            $delivery = DeliveryNote::with('details')->find($id);

            foreach ($delivery->details as $detail) {
                $this->revertProductQuantityAndCogs(
                    $detail->product_id,
                    $detail->received_qty,
                    $detail->purchase_order_detail->price
                );
            }

            $delivery->details()->update(['state' => 'deleted']);
            $delivery->state = "deleted";
            $delivery->save();

            return $delivery;
        });
    }

    private function updateProductQuantityAndCogs($productId, $receivedQty, $purchaseOrderDetailId, $shippingCostPerItem)
    {
        $product = Product::find($productId);
        $cogsBeforeUpdate = $product->cogs;

        $qtyBeforeUpdate = $product->quantity;
        $qtyAfterUpdate = $qtyBeforeUpdate + $receivedQty;

        $receivedPrice = PurchaseOrderDetail::find($purchaseOrderDetailId)->price;
        $totalItemCost = $receivedPrice + $shippingCostPerItem;
        $newCogs = calculateCogs($product, $receivedQty, $totalItemCost);

        $product->quantity = $qtyAfterUpdate;
        $product->cogs = $newCogs;
        $product->save();

        // Log the product actions
        $product->logs()->createMany([
            [
                'action' => 'quantity_update',
                'qty_before' => $qtyBeforeUpdate,
                'qty_after' => $qtyAfterUpdate,
                'note' => 'Received new products',
            ],
            [
                'action' => 'cogs_calculation',
                'cogs_before' => $cogsBeforeUpdate,
                'cogs_after' => $newCogs,
                'note' => 'COGS updated, item received at price: ' . $receivedPrice . ' per item and shipping cost: ' . $shippingCostPerItem . ' per item',
            ]
        ]);
    }

    private function revertProductQuantityAndCogs($productId, $receivedQty, $receivedPrice)
    {
        $product = Product::find($productId);
        $newCogs = calculateCogs($product, -$receivedQty, $receivedPrice);

        $qtyBeforeUpdate = $product->quantity;
        $qtyAfterUpdate = $qtyBeforeUpdate - $receivedQty;

        $product->logs()->createMany([
            [
                'action' => 'quantity_revert',
                'qty_before' => $qtyBeforeUpdate,
                'qty_after' => $qtyAfterUpdate,
                'note' => 'Reverted quantity for updated / deleted delivery note',
            ],
            [
                'action' => 'cogs_revert',
                'cogs_before' => $product->cogs,
                'cogs_after' => $newCogs,
                'note' => 'Reverted COGS for updated / deleted delivery note',
                ]
            ]);

        $product->cogs = $newCogs;
        $product->quantity = $qtyAfterUpdate;
        $product->save();
    }
}
