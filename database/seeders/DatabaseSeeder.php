<?php

namespace Database\Seeders;

use App\Models\Accesses\Outlet;
use App\Models\Accesses\User;
use App\Models\Accesses\UserGroup;
use App\Models\Accesses\UserOutlet;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserGroup::insert([
            'id' => 2,
            'user_groupname' => 'admin',
            'user_id' => 1
        ]);
        Outlet::insert(
            [
                [
                    'id' => 1,
                    'outlet_name' => 'Outlet 1',
                    'outlet_address' => 'Jl. Raya',
                    'owner_user_id' => 1,
                    'outlet_logo' => 'logo.png',
                    'outlet_hp' => '08123456789',
                    'user_id' => 1,

                ],
                [
                    'id' => 2,
                    'outlet_name' => 'Outlet 2',
                    'outlet_address' => 'Jl. hola raya',
                    'owner_user_id' => 1,
                    'outlet_logo' => 'logo.png',
                    'outlet_hp' => '08123456789',
                    'user_id' => 1,

                ]
            ]
        );

        User::insert([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => bcrypt('12345678'),
            'user_group_id' => 1,
            'is_kasir' => true,
            'user_group_id' => 2,
            'user_id' => 1
        ]);
    }
}
