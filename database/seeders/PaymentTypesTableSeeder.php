<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PaymentTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Trf BCA = bca
        DB::table('payment_types')->insert([
            [
                'id' => 1,
                'name' => "Transfer BCA",
                'code' => "bca",
                'state' => 'active'
            ],
        ]);
    }
}
