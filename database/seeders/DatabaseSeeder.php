<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            PaymentCategoriesTableSeeder::class,
            PaymentTypesTableSeeder::class,
            AccountTableSeeder::class,
            // CustomerTableSeeder::class,
            ProductCategorySeeder::class,
            // ProductsTableSeeder::class,
        ]);
    }
}
