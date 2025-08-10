<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // protected $fillable = ['code', 'name', 'price', 'quantity', 'cogs', 'last_edited', 'product_category_id'];
        $products = [
            [
                'product_category_id' => 1,
                'code' => "P1",
                'name' => "Product Test 1",
                'price' => 100000,
                'quantity' => 0,
                'state' => "active",
                'cogs' => 0,
            ],
            [
                'product_category_id' => 1,
                'code' => "P2",
                'name' => "Product Test 2",
                'price' => 80000,
                'quantity' => 0,
                'state' => "active",
                'cogs' => 0,
            ]
        ];

        DB::table('products')->insert($products);
    }
}
