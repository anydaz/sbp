<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrderDetail;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DeliveryNote::active()->with('purchase_order', 'details')->orderBy('id','desc')->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $request->request->add(['user_id' => $user_id]);

        $body = $request->all();
        $details = $request->details;

        $purchase_order_id = $body['purchase_order_id'];
        $purchase_order = PurchaseOrder::find($purchase_order_id);

        try {
            $delivery_data = DB::transaction(function () use ($body, $details) {
                $delivery = DeliveryNote::create($body);

                $delivery->details()->createMany($details);
                return $delivery;
            });

            $shipping_cost = $purchase_order->shipping_cost ?? 0;
            $total_qty = array_sum($purchase_order->details->pluck('qty')->toArray());
            $shipping_cost_per_item = $total_qty > 0 ? $shipping_cost / $total_qty : 0;

            foreach ($details as $detail) {
                $product = Product::find($detail['product_id']);
                $qty_before_update = $product->quantity;
                $qty_after_update = $qty_before_update + $detail['received_qty'];
                $cogs_before_update = $product->cogs;

                // $product->increment('quantity', $detail['received_qty']);
                $received_price = PurchaseOrderDetail::find($detail["purchase_order_detail_id"])->price ;
                $newCogs = calculateCogs($product, $detail['received_qty'], $received_price + $shipping_cost_per_item);

                $product->quantity = $qty_after_update;
                $product->cogs = $newCogs;
                $product->save();

                // Log the product action
                $product->logs()->createMany([
                    [
                        'action' => 'quantity_update',
                        'qty_before' => $qty_before_update,
                        'qty_after' => $qty_after_update,
                        'note' => 'Received new products',
                    ],
                    [
                        'action' => 'cogs_calculation',
                        'cogs_before' => $cogs_before_update,
                        'cogs_after' => $newCogs,
                        'note' => 'COGS updated, item received at price: ' . $received_price . ' per item and shipping cost: ' . $shipping_cost_per_item . ' per item',
                    ]
                ]);
            }

            $response = DeliveryNote::with('details.product')->find($delivery_data->id);

            return response()->json($response, 201);
        } catch (Exception $error) {

        };
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $delivery =
            DeliveryNote::with(
                            'purchase_order',
                            'details.product',
                            'details.purchase_order_detail.delivery_details'
                        )
                        ->find($id);
        return response()->json($delivery);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $body = $request->all();
        $details = $request->details;

        try {
            $delivery_data = DB::transaction(function () use ($body, $details, $id) {
                $delivery = DeliveryNote::with('details')->find($id);

                $to_be_deleted_details = $delivery->details;
                foreach($to_be_deleted_details as $detail) {
                    $product = Product::find($detail->product_id);
                    $received_price = $detail->purchase_order_detail->price;

                    $newCogs = calculateCogs($product, -$detail->received_qty, $received_price);
                    $product->quantity = $product->quantity - $detail->received_qty;
                    $product->cogs = $newCogs;
                    $product->save();
                }
                $delivery->details()->update(['state' => 'deleted']);
                $delivery->details()->createMany($details);

                // update quantity from new details
                foreach ($details as $detail) {
                    $product = Product::find($detail['product_id']);
                    $received_price = PurchaseOrderDetail::find($detail["purchase_order_detail_id"])->price;
                    $newCogs = calculateCogs($product, $detail["received_qty"], $received_price);
                    $product->quantity = $product->quantity + $detail['received_qty'];
                    $product->cogs = $newCogs;
                    $product->save();
                }

                // update purchase
                $delivery->update($body);

                return DeliveryNote::with('details.product')->find($delivery->id);
            });

            return response()->json($delivery_data, 201);
        } catch (Exception $error) {
            return response()->json($error, 422);
        };
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delivery = DB::transaction(function () use ($id) {
            $delivery = DeliveryNote::with('details')->find($id);

            $to_be_deleted_details = $delivery->details;
            foreach($to_be_deleted_details as $detail) {
                $product = Product::find($detail->product_id);
                $received_price = $detail->purchase_order_detail->price;

                $newCogs = calculateCogs($product, -$detail->received_qty, $received_price);
                $product->quantity = $product->quantity - $detail->received_qty;
                $product->cogs = $newCogs;
                $product->save();
            }

            $delivery->details()->update(['state' => 'deleted']);
            $delivery->state = "deleted";
            $delivery->save();

            return $delivery;
        });

        return response()->json($delivery);
    }
}
