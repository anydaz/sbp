<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Imports\NewProductsImport;
use App\Imports\BulkUpdatePriceImport;
use App\Exports\ProductsExport;

class ProductService
{
    public function getProducts(Request $request)
    {
        $search = $request->search;
        $limit = $request->limit;
        $sort_price = $request->sort_price;
        $sort_by = $request->sort_by;

        $keywords = preg_split('/\s+/', trim($search));
        $query = Product::with('category')->active();

        $query = $query->when($keywords, function ($q, $keywords) {
            foreach ($keywords as $keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%');
            }
        });

        $query = $query->when($sort_by, function ($q, $sort_by) {
            return $q->orderBy($sort_by, $sort_by == "last_edited" ? "desc" : "asc");
        });

        $query = $query->when($sort_price, function ($q, $sort) {
            return $q->orderBy("price", "asc");
        });

        if ($limit) {
            return $query->paginate($limit);
        }

        return $query->paginate(10);
    }

    public function createProduct(array $data)
    {
        return Product::create($data);
    }

    public function getProduct($id)
    {
        return Product::with('category')->find($id);
    }

    public function updateProduct($id, array $data)
    {
        $data['last_edited'] = Carbon::now();
        $product = Product::find($id);
        $product->update($data);
        return $product;
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);
        $product->state = "deleted";
        $product->save();
        return $product;
    }

    public function exportProducts()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function getProductLogs($id, $type)
    {
        $product = Product::findOrFail($id);
        $query = $product->logs()
            ->orderBy('id', 'desc');

        if ($type === 'quantity') {
            $query->where('action', 'like', '%quantity%');
        } else if ($type === 'cogs') {
            $query->where('action', 'like', '%cogs%');
        }

        return $query->paginate(10);
    }
}
