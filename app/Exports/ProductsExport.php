<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function headings():array
    {
        return [
            'ID',
            'Kategori',
            'Nama',
            'Alias',
            'Barcode',
            'Kode Efikasi',
            'Harga',
            'Quantity'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->select('products.id', 'product_categories.name as category_name', 'products.name', 'products.alias', 'products.barcode', 'products.efficiency_code', 'products.price', 'products.quantity')
            ->where('products.state', "active")
            ->get();
    }
}
