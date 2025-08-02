<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DraftSalesOrder;
use App\Models\DraftSalesOrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use PDF;

class DraftSalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $default_scope = DraftSalesOrder::active()->select('draft_sales_orders.*')->with('user', 'customer', 'details.product', 'sales_order');

        if($request->with_product){
            $default_scope = $default_scope->with('details.product');
        };

        if($request->fresh){
            $default_scope = $default_scope
                                ->whereDate('draft_sales_orders.created_at', '>', Carbon::now()->subDays(7)->toDateString())
                                ->whereDoesntHave('sales_order', function (Builder $query) {
                                    $query->where('state', 'active');
                                });
        };

        $start = $request->start_date;
        $end = $request->end_date;
        if( $start && $end ){
            $default_scope =
                $default_scope->whereBetween('draft_sales_orders.created_at', [$start, $end]);
        }

        $user = auth()->user();
        if($user->role == "sales"){
            $default_scope = $default_scope->where('user_id', $user->id);
        }

        $search = $request->search;
        if($search){
            $default_scope =
                $default_scope
                    ->join('users', 'users.id', '=', 'draft_sales_orders.user_id')
                    ->where(function ($query) use ($search) {
                        $query->where('draft_sales_orders.id', 'like', '%'.$search.'%')
                              ->orWhere('users.name', 'like', '%'.$search.'%');
                    });
        }

        return $default_scope->paginate(10);
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
        //
        $user_id = auth()->user()->id;
        $request->request->add(['user_id' => $user_id]);

        $body = $request->all();
        $details = $request->details;
        // dd($details);

        try {
            DB::transaction(function () use ($body, $details) {
                $draft = DraftSalesOrder::create($body);

                // dd($details);
                $draft->details()->createMany($details);
            });

            return response()->json([], 201);
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
        //
        $draft = DraftSalesOrder::with('user', 'customer', 'details.product')->find($id);
        return response()->json($draft);
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
            DB::transaction(function () use ($body, $details, $id) {
                $draft = DraftSalesOrder::find($id);
                $draft->update($body);

                // dd($details);
                $draft->details()->update(['state' => 'deleted']);
                $draft->details()->createMany($details);
            });

            return response()->json([], 201);
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
        $draft = DraftSalesOrder::find($id);
        $draft->details()->update(['state' => 'deleted']);
        $draft->state = 'deleted';
        $draft->save();

        return response()->json($draft);
    }

    public function print($id, Request $request)
    {
        $title = "Checklist";
        $draft_sales_order = DraftSalesOrder::find($id);
        $pdf = PDF::loadView('print_draft_sales_order', compact('draft_sales_order', 'title'));
        // dump($pdf);
        return $pdf->stream();
    }
}
