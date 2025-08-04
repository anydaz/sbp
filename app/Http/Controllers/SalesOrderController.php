<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Product;
use App\Models\PaymentType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\SalesOrderCreated;
use PDF;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $is_pending = $request->is_pending;
        $query = SalesOrder::active()->with('user', 'customer', 'draft.user', 'details')->where("is_pending", $is_pending);
        $query =  $query->when($search, function ($q, $search) {
            return $q->where('sales_number', 'like', '%'.$search.'%')
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        });


        return $query->orderBy('id','desc')->paginate(10);
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

        $payment_type = PaymentType::find($request->payment_type_id);
        $request->request->add(['sales_number' => $this->getSalesNumber($payment_type->code, $request->date)]);

        $body = $request->all();
        $details = $request->details;

        try {
            // first, add saler order and details
            $sales_data = DB::transaction(function () use ($body, $details) {
                $details_total = array_sum(array_column($details, 'subtotal'));
                $discount = $body['discount'] ?? 0;
                $body['total'] = $details_total - $discount;

                // $total = $details ? array_sum(array_column($details, 'subtotal')) : 0;
                $sales = SalesOrder::create($body);

                $sales->details()->createMany($details);
                return $sales;
            });

            // second, update quantity of each products
            foreach ($details as $detail) {
                Product::where('id', $detail['product_id'])->decrement('quantity', $detail['qty']);
            }

            // third, create accounting entry
            event(new SalesOrderCreated($sales_data));

            $response = SalesOrder::with(['payment_type','details.product'])->find($sales_data->id);

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
        $sales = SalesOrder::with('draft.user', 'payment_category', 'payment_type', 'customer', 'user', 'details.product')->find($id);
        return response()->json($sales);
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
            $sales = DB::transaction(function () use ($body, $details, $id) {
                $sales = SalesOrder::with('details')->find($id);

                // if date is changed, update sales_number
                if (isset($body['date']) && $sales->date != $body['date']) {
                    $payment_type = PaymentType::find($body['payment_type_id']);
                    $body['sales_number'] = $this->getSalesNumber($payment_type->code, $body['date']);
                }

                // first, update quantity from to be deleted details
                $to_be_deleted_details = $sales->details;
                foreach($to_be_deleted_details as $detail) {
                    Product::where('id', $detail->product_id)->increment('quantity', $detail->qty);
                }

                // delete details
                $sales->details()->update(['state' => 'deleted']);

                // create new details
                $sales->details()->createMany($details);

                // update quantity from new details
                foreach ($details as $detail) {
                    Product::where('id', $detail['product_id'])->decrement('quantity', $detail['qty']);
                }

                // update sales
                $sales->update($body);

                return SalesOrder::with(['payment_type','details.product'])->find($sales->id);
            });

            return response()->json($sales, 200);
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
        $sales = DB::transaction(function () use ($id) {
            $sales = SalesOrder::with('details')->find($id);

            $to_be_deleted_details = $sales->details;
            foreach($to_be_deleted_details as $detail) {
                Product::where('id', $detail->product_id)->increment('quantity', $detail->qty);
            }

            $sales->details()->update(['state' => 'deleted']);
            $sales->state = "deleted";
            $sales->save();

            return $sales;
        });

        return response()->json($sales);
    }

    public function getSalesNumber($payment_code, $date){
        $today = Carbon::now()->isoFormat('DDMMYY');
        $date = $date ? Carbon::parse($date)->isoFormat('DDMMYY') : $today;

        $last_sales_with_same_code =
            SalesOrder::where("sales_number", 'like', '%SO/'.$payment_code.'/'.$date.'%')
                ->orderBy('id','desc')->first();

        if($last_sales_with_same_code){
            $arr = explode("/", $last_sales_with_same_code->sales_number);
            $lastNumber = end($arr);
            return "SO/".$payment_code.'/'.$date.'/'.($lastNumber+1);
        }else{
            return "SO/".$payment_code.'/'.$date.'/1';
        }
    }

    public function print($id, Request $request)
    {
        $type = $request->type;
        $title = "";
        switch ($type) {
            case 'sales':
                $title = "Invoice";
                break;
            case 'checklist':
                $title = "Invoice";
                break;
            case 'delivery':
                $title = "Surat Jalan";
                break;
            default:
                break;
        };

        $sales_order = SalesOrder::with('payment_type')->find($id);
        $pdf = PDF::loadView('print_sales_order', compact('sales_order', 'title'));
        return $pdf->stream();
    }
}
