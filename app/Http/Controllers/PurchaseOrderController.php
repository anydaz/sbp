<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Imports\PurchaseOrdersImport;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $default_scope = PurchaseOrder::active()->with('user', 'delivery_notes', 'details.delivery_details');

        if($request->with_product){
            $default_scope = $default_scope->with('details.product');
        };

        if($request->search){
            $search = $request->search;
            $default_scope = $default_scope->where('id', 'like', '%'.$search.'%')
            ->orWhere('supplier', 'like', '%'.$search.'%');
        };

        if($request->valid){
            // $default_scope = $default_scope->doesntHave('delivery_notes');
            $default_scope = $default_scope->whereExists(function ($query) {
                $total_received_query = DB::table('delivery_note_details')
                    ->select('purchase_order_detail_id', DB::raw('SUM(received_qty) as total_received_qty'))
                    ->where('state','active')
                    ->groupBy('purchase_order_detail_id');

                $query->select(DB::raw(1))
                      ->from('purchase_order_details')
                      ->where('state','active')
                      ->leftJoinSub($total_received_query, 'total_received', function ($join) {
                        $join->on('purchase_order_details.id', '=', 'total_received.purchase_order_detail_id');
                      })
                      ->whereRaw('(total_received.total_received_qty < purchase_order_details.qty OR total_received_qty IS NULL)')
                      ->whereColumn('purchase_order_details.purchase_order_id', 'purchase_orders.id');
            });
        };

        return $default_scope->orderBy('id','desc')->paginate(10);
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

        try {
            $purchase_data = DB::transaction(function () use ($body, $details) {
                $details_total = array_sum(array_column($details, 'subtotal'));
                $discount = $body['purchase_discount'] ?? 0;
                $body['total'] = $details_total - $discount;

                $purchase = PurchaseOrder::create($body);

                $purchase->details()->createMany($details);
                return $purchase;
            });

            // Dispatch event for journal entry
            event(new \App\Events\PurchaseOrderCreated($purchase_data));

            $response = PurchaseOrder::with('details.product')->find($purchase_data->id);
            return response()->json($response, 201);
        } catch (Exception $error) {
            // ...existing code...
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchase = PurchaseOrder::with('user', 'details.product')->find($id);
        return response()->json($purchase);
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
            $purchase = DB::transaction(function () use ($body, $details, $id) {
                $purchase = PurchaseOrder::with('details')->find($id);

                // first, update quantity from to be deleted details
                // $to_be_deleted_details = $purchase->details;
                // foreach($to_be_deleted_details as $detail) {
                //     Product::where('id', $detail->product_id)->decrement('quantity', $detail->qty);
                // }

                // delete details
                $purchase->details()->update(['state' => 'deleted']);

                // create new details
                $purchase->details()->createMany($details);

                // update quantity from new details
                // foreach ($details as $detail) {
                //     Product::where('id', $detail['product_id'])->increment('quantity', $detail['qty']);
                // }

                // update purchase
                $purchase->update($body);

                return PurchaseOrder::with('details.product')->find($purchase->id);
            });

            return response()->json($purchase, 200);
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
        $purchase = DB::transaction(function () use ($id) {
            $purchase = PurchaseOrder::with('details')->find($id);

            // $to_be_deleted_details = $purchase->details;
            // foreach($to_be_deleted_details as $detail) {
            //     Product::where('id', $detail->product_id)->decrement('quantity', $detail->qty);
            // }

            $purchase->details()->update(['state' => 'deleted']);
            $purchase->state = "deleted";
            $purchase->save();

            return $purchase;
        });

        return response()->json($purchase);
    }


    public function import(Request $request)
    {
        $import = new PurchaseOrdersImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return response()->json([
                "error"  => $message
            ], 422);
        }

        if($import->getRowCount() == 0)
        {
            return response()->json([
                "error"  => "Excel file has no data",
            ], 422);
        }
        else
        {
            return response()->json([], 200);
        }
    }
}
