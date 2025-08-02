<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Imports\NewProductsImport;
use App\Imports\BulkUpdatePriceImport;
use App\Exports\ProductsExport;
use PDF;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $limit = $request->limit;
        $sort_price = $request->sort_price;


        $sort_by = $request->sort_by;
        $keywords = preg_split('/\s+/', trim($search));
        $query = Product::with('category')->active();

        $query =  $query->when($keywords, function ($q, $keywords) {
            foreach ($keywords as $keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%');
            }
        });

        if($limit){
            return $query->paginate($limit);
        }

        $query =  $query->when($sort_by, function ($q, $sort_by) {
            return $q->orderBy($sort_by, $sort_by == "last_edited" ? "desc" : "asc");
        });

        $query =  $query->when($sort_price, function ($q, $sort) {
            return $q->orderBy("price", "asc");
        });

        return $query->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
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
        $body = $request->all();
        $product = Product::create($body);

        return response()->json($product);
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

        $product = Product::with('category')->find($id);
        return response()->json($product);
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
        $request->request->add(['last_edited' => Carbon::now() ]);
        $body = $request->all();
        $product = Product::find($id);
        $product->update($body);
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->state = "deleted";
        $product->save();

        return response()->json($product);
    }


    public function import(Request $request)
    {
        $import = new ProductsImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if($th->getCode() == 21000) {
                $message = "Duplicate Records";
            }

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

    public function import_new(Request $request)
    {
        $import = new NewProductsImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if($th->getCode() == 21000) {
                $message = "Duplicate Records";
            }


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

    public function bulk_update_price(Request $request)
    {
        // Add debugging to check if file is received
        if (!$request->hasFile('file')) {
            return response()->json([
                "error" => "No file uploaded"
            ], 422);
        }

        $file = $request->file('file');
        \Log::info('File details: ', [
            'name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType()
        ]);

        $import = new BulkUpdatePriceImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            \Log::error('Import error: ' . $message);

            return response()->json([
                "error"  => $message
            ], 422);
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function find_by_barcode($code)
    {
        //
        $product = Product::where('barcode', $code)->orWhere('efficiency_code', $code)->first();
        return response()->json($product);
    }

    public function find_by_efficiency_code($code)
    {
        //

        $product = Product::with('category')->where('efficiency_code', $code)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function print($id)
    {
        $product = Product::find($id);
        $pdf = PDF::loadView('print_product', compact('product'));
        return $pdf->stream();
}
}
