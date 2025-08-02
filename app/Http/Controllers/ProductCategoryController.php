<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Imports\ProductCategoriesImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $query = ProductCategory::active()->orderBy('id');

        $query =  $query->when($search, function ($q, $search) {
            return $q->where('name', 'like', '%'.$search.'%')
            ->orWhere('code', 'like', '%'.$search.'%');
        });

        return $query->paginate(10);
    }

    public function store(Request $request)
    {
        $body = $request->all();
        $category = ProductCategory::create($body);

        return response()->json($category);
    }

    public function show($id)
    {
        $category = ProductCategory::find($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {

        $body = $request->all();
        $category = ProductCategory::find($id);
        $category->update($body);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = ProductCategory::find($id);
        $category->state = "deleted";
        $category->save();

        return response()->json($category);
    }

    public function import(Request $request)
    {
        $import = new ProductCategoriesImport;

        Excel::import($import, $request->file('file'));

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
