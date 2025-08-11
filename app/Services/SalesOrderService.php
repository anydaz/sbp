<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\PaymentType;
use App\Events\SalesOrderCreated;
use App\Events\SalesOrderUpdated;
use App\Events\SalesOrderDeleted;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesOrderService
{
    public function getSalesOrders($request)
    {
        $search = $request->search;
        $is_pending = $request->is_pending;
        $query = SalesOrder::active()
            ->with('user', 'customer', 'draft.user', 'details')
            ->where("is_pending", $is_pending);

        $query = $query->when($search, function ($q, $search) {
            return $q->where('sales_number', 'like', '%'.$search.'%')
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
        });

        return $query->orderBy('id','desc')->paginate(10);
    }

    public function createSalesOrder($data)
    {
        $user_id = auth()->user()->id;
        $data['user_id'] = $user_id;

        $payment_type = PaymentType::find($data['payment_type_id']);
        $data['sales_number'] = $this->generateSalesNumber($payment_type->code, $data['date']);

        $details = $data['details'];

        try {
            $sales_data = DB::transaction(function () use ($data, $details) {
                $details_total = array_sum(array_column($details, 'subtotal'));
                $discount = $data['discount'] ?? 0;
                $data['total'] = $details_total - $discount;

                // calculate total cogs from sum of detail cogs times quantity
                $data['total_cogs'] = array_sum(array_map(function ($detail) {
                    return $detail['cogs'] * $detail['qty'];
                }, $details));

                $sales = SalesOrder::create($data);
                $sales->details()->createMany($details);

                return $sales;
            });

            // Update product quantities
            foreach ($details as $detail) {
                $this->updateProductQuantity(
                    $detail['product_id'],
                    -$detail['qty'],
                    'Quantity reduced due to sales order creation'
                );
            }

            // Create accounting entry
            event(new SalesOrderCreated($sales_data));

            return SalesOrder::with(['payment_type','details.product'])->find($sales_data->id);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function getSalesOrder($id)
    {
        return SalesOrder::with('draft.user', 'payment_category', 'payment_type', 'customer', 'user', 'details.product')
            ->find($id);
    }

    public function updateSalesOrder($id, $data)
    {
        $details = $data['details'];

        try {
            return DB::transaction(function () use ($data, $details, $id) {
                $sales = SalesOrder::with('details')->find($id);

                // Update sales number if date changed
                if (isset($data['date']) && $sales->date != $data['date']) {
                    $payment_type = PaymentType::find($data['payment_type_id']);
                    $data['sales_number'] = $this->generateSalesNumber($payment_type->code, $data['date']);
                }

                // Restore quantities from old details
                foreach($sales->details as $detail) {
                    $this->updateProductQuantity(
                        $detail->product_id,
                        $detail->qty,
                        'Quantity restored due to sales order update'
                    );
                }

                // Store original sales order for the event
                $originalSalesOrder = clone $sales;

                // Delete old details
                $sales->details()->update(['state' => 'deleted']);

                // Create new details
                $sales->details()->createMany($details);

                // Update quantities for new details
                foreach ($details as $detail) {
                    $this->updateProductQuantity(
                        $detail['product_id'],
                        -$detail['qty'],
                        'Quantity reduced due to sales order update'
                    );
                }

                // Calculate new totals
                $details_total = array_sum(array_column($details, 'subtotal'));
                $discount = $data['discount'] ?? 0;
                $data['total'] = $details_total - $discount;
                $data['total_cogs'] = array_sum(array_map(function ($detail) {
                    return $detail['cogs'] * $detail['qty'];
                }, $details));

                // Update sales order
                $sales->update($data);

                $updatedSales = SalesOrder::with(['payment_type','details.product'])->find($sales->id);
                
                // Dispatch update event
                event(new SalesOrderUpdated($updatedSales, $originalSalesOrder));

                return $updatedSales;
            });
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function deleteSalesOrder($id)
    {
        return DB::transaction(function () use ($id) {
            $sales = SalesOrder::with('details')->find($id);

            // Restore quantities
            foreach($sales->details as $detail) {
                $this->updateProductQuantity(
                    $detail->product_id,
                    $detail->qty,
                    'Quantity restored due to sales order deletion'
                );
            }

            // Mark details as deleted
            $sales->details()->update(['state' => 'deleted']);

            // Mark sales order as deleted
            $sales->state = "deleted";
            $sales->save();

            // Dispatch delete event
            event(new SalesOrderDeleted($sales));

            return $sales;
        });
    }

    private function generateSalesNumber($payment_code, $date)
    {
        $today = Carbon::now()->isoFormat('DDMMYY');
        $date = $date ? Carbon::parse($date)->isoFormat('DDMMYY') : $today;

        $last_sales_with_same_code = SalesOrder::where("sales_number", 'like', '%SO/'.$payment_code.'/'.$date.'%')
            ->orderBy('id','desc')
            ->first();

        if ($last_sales_with_same_code) {
            $arr = explode("/", $last_sales_with_same_code->sales_number);
            $lastNumber = end($arr);
            return "SO/".$payment_code.'/'.$date.'/'.($lastNumber+1);
        }

        return "SO/".$payment_code.'/'.$date.'/1';
    }

    /**
     * Update product quantity and log the change
     *
     * @param int $productId The ID of the product to update
     * @param int $quantityChange The amount to change (positive for increase, negative for decrease)
     * @param string $note The note to include in the log
     * @return void
     */
    private function updateProductQuantity($productId, $quantityChange, $note)
    {
        $product = Product::find($productId);
        $quantityBeforeUpdate = $product->quantity;
        $quantityAfterUpdate = $quantityBeforeUpdate + $quantityChange;

        if ($quantityChange > 0) {
            $product->increment('quantity', $quantityChange);
        } else {
            $product->decrement('quantity', abs($quantityChange));
        }

        ProductLog::create([
            'product_id' => $productId,
            'action' => 'quantity_update',
            'qty_before' => $quantityBeforeUpdate,
            'qty_after' => $quantityAfterUpdate,
            'note' => $note,
        ]);
    }
}
