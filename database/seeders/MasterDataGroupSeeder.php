<?php

namespace Database\Seeders;

use App\Models\Accesses\MasterDataGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterDataGroup::insert(
            [
                [
                    'id' => 1,
                    'master_data_groupname' => 'ITEM',
                    'user_id' => 1
                ],
                [
                    'id' => 2,
                    'master_data_groupname' => 'PENGGUNA',
                    'user_id' => 1
                ],
                [
                    'id' => 3,
                    'master_data_groupname' => 'MITRA',
                    'user_id' => 1
                ]
            ]
        );
    }
}
