<?php

namespace Database\Seeders;

use App\Models\Accesses\UserOutlet;
use Illuminate\Database\Seeder;

class UserOutlet1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserOutlet::insert([[
            'user_id' => 1,
            'outlet_id' => 1

        ]]);
    }
}
