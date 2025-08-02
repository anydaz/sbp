<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return SalesReturn::active()->with('user', 'details')->orderBy('id','desc')->paginate(10);
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
            $return_data = DB::transaction(function () use ($body, $details) {
                $return = SalesReturn::create($body);

                $return->details()->createMany($details);

                foreach ($details as $detail) {
                    Product::where('id', $detail['product_id'])->increment('quantity', $detail['qty']);
                }

                return $return;
            });

            $response = SalesReturn::with('details.product')->find($return_data->id);
            return response()->json($response, 201);
        } catch (Exception $error) {
            return response()->json($error, 500);
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
        $return = SalesReturn::with('user', 'details.product')->find($id);
        return response()->json($return);
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
            $return_data = DB::transaction(function () use ($body, $details, $id) {
                $return = SalesReturn::with('details')->find($id);

                // first, update quantity from to be deleted details
                $to_be_deleted_details = $return->details;
                foreach($to_be_deleted_details as $detail) {
                    Product::where('id', $detail->product_id)->decrement('quantity', $detail->qty);
                }

                // delete details
                $return->details()->update(['state' => 'deleted']);

                // create new details
                $return->details()->createMany($details);

                // update quantity from new details
                foreach ($details as $detail) {
                    Product::where('id', $detail['product_id'])->increment('quantity', $detail['qty']);
                }

                // update return
                $return->update($body);

                return SalesReturn::with('details.product')->find($return->id);
            });

            return response()->json($return_data, 200);
        } catch (Exception $error) {
            return response()->json($error, 500);
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
        $response = DB::transaction(function () use ($id) {
            $return = SalesReturn::with('details')->find($id);

            $to_be_deleted_details = $return->details;
            foreach($to_be_deleted_details as $detail) {
                Product::where('id', $detail->product_id)->decrement('quantity', $detail->qty);
            }

            $return->details()->update(['state' => 'deleted']);
            $return->state = "deleted";
            $return->save();

            return $return;
        });

        return response()->json($response);
    }
}
