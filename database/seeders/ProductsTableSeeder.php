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
        $products = [];
        for($i=0; $i<25; $i++){
            array_push($products, [
                'name' => "Product Test $i",
                'alias' => "Product Alias $i",
                'barcode' => strval(1234567890 * ($i+1)),
                'price' => 10000 * ($i+1),
                'quantity' => 100 * ($i+1),
                'state' => "active",
                'cogs' => 10000 * ($i+1),
            ]);
        }

        DB::table('products')->insert($products);
    }
}
