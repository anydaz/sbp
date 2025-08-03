<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `state`, `created_at`, `updated_at`) VALUES
// (1, 'Tanpa Nama', NULL, NULL, NULL, 'active', NULL, NULL),
// (2, 'PT KARUNIA KULINER INDONESIA', NULL, NULL, NULL, 'active', '2025-06-21 01:17:51', '2025-06-21 01:17:51'),
// (3, 'SABRILA DUARI', NULL, '0895331228730', NULL, 'active', '2025-06-21 01:19:52', '2025-06-21 01:19:52'),
// (4, 'SAM', NULL, '087777637872', NULL, 'active', '2025-06-21 01:20:18', '2025-06-21 01:20:18'),
// (5, 'ARY RIDJIWAN', NULL, '081295197070', NULL, 'active', '2025-06-21 01:20:53', '2025-06-21 01:20:53'),
// (6, 'BAPAK MICHAEL', NULL, NULL, NULL, 'active', '2025-06-21 01:21:09', '2025-06-21 01:21:09'),
// (7, 'THE LAURENCE ALAM SUTRA', NULL, NULL, NULL, 'active', '2025-06-21 01:21:37', '2025-06-21 01:21:37'),
// (8, 'HUISMAN', NULL, NULL, NULL, 'active', '2025-06-21 01:21:48', '2025-06-21 01:21:48'),
// (9, 'SAIGON', NULL, NULL, NULL, 'active', '2025-06-21 01:21:57', '2025-06-21 01:21:57'),
// (10, 'ADE', NULL, '081213533809', NULL, 'active', '2025-07-11 23:44:49', '2025-07-11 23:44:49'),
// (11, 'HURICANE', NULL, NULL, NULL, 'active', '2025-07-11 23:45:11', '2025-07-11 23:45:11'),
// (12, 'LA TROBE COFFEE & BRUNCH - LUSIDA', NULL, '081298105276', NULL, 'active', '2025-07-11 23:47:08', '2025-07-11 23:53:31');

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customers')->insert([
            [
                'id' => 1,
                'name' => "Tanpa Nama",
                'phone' => null,
                'state' => 'active'
            ],
        ]);
    }
}
