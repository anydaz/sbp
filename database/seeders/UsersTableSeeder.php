<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => "Richard Hendricks",  
                'email' => "admin@example.com",  
                'password' => Hash::make("1234"),
                'role' => 'admin'
            ], 
            [
                'name' => "Jared Dunn",  
                'email' => "sales@example.com",  
                'password' => Hash::make("1234"),
                'role' => 'sales'
            ],
            [
                'name' => "Monica Hall",  
                'email' => "sales2@example.com",  
                'password' => Hash::make("1234"),
                'role' => 'sales'
            ],
            [
                'name' => "Bertram Gilfoyle",  
                'email' => "cashier@example.com",  
                'password' => Hash::make("1234"),
                'role' => 'cashier'
            ]
        ]);
    }
}
