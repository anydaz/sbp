<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SalesOrderService;
use PDF;

class SalesOrderController extends Controller
{
    protected $salesOrderService;

    public function __construct(SalesOrderService $salesOrderService)
    {
        $this->salesOrderService = $salesOrderService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->salesOrderService->getSalesOrders($request);
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
        try {
            $sales = $this->salesOrderService->createSalesOrder($request->all());
            return response()->json($sales, 201);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 422);
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
        $sales = $this->salesOrderService->getSalesOrder($id);
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
        try {
            $sales = $this->salesOrderService->updateSalesOrder($id, $request->all());
            return response()->json($sales, 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $sales = $this->salesOrderService->deleteSalesOrder($id);
            return response()->json($sales);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 422);
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
