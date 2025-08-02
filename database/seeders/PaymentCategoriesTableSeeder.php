<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PaymentCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cash = CA
        // Credit = CR

        DB::table('payment_categories')->insert([
            [
                'id' => 1,
                'name' => "Cash",
                'code' => "CA",
            ],
            [
                'id' => 2,
                'name' => "Credit",
                'code' => "CR",
            ],
        ]);
    }
}
