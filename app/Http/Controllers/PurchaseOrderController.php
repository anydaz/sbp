<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PurchaseOrderService;
use App\Imports\PurchaseOrdersImport;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $result = $this->purchaseOrderService->getPurchaseOrders(
                $request->with_product,
                $request->search,
                $request->valid
            );
            return response()->json($result);
        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
        }
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
            $result = $this->purchaseOrderService->createPurchaseOrder(
                $request->all(),
                $request->details,
                auth()->id()
            );
            return response()->json($result, 201);
        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
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
        try {
            $result = $this->purchaseOrderService->getPurchaseOrder($id);
            return response()->json($result);
        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 404);
        }
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
            $result = $this->purchaseOrderService->updatePurchaseOrder(
                $id,
                $request->all(),
                $request->details
            );
            return response()->json($result);
        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
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
            $result = $this->purchaseOrderService->deletePurchaseOrder($id);
            return response()->json($result);
        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
        }
    }


    public function import(Request $request)
    {
        try {
            $rowCount = $this->purchaseOrderService->importPurchaseOrders($request->file('file'));
            if ($rowCount === 0) {
                return response()->json(['error' => 'Excel file has no data'], 422);
            }
            return response()->json([], 200);
        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
        }
    }
}
