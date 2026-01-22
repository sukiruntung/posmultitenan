<?php

namespace Database\Seeders;

use App\Models\Accesses\MasterDataAccess;
use Illuminate\Database\Seeder;

class MasterAccessOutlet1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterDataAccess::insert(
            [
                [
                    'outlet_id' => 1,
                    'master_data_id' => 1,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 2,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 3,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 5,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 6,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 7,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 8,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 9,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 10,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 11,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 13,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 12,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 14,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 15,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 16,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 17,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 18,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ],
                [
                    'outlet_id' => 1,
                    'master_data_id' => 19,
                    'user_group_id' => 1,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'user_id' => 1,
                ]
            ]

        );
    }
}
