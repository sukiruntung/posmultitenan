<?php

namespace Database\Seeders;

use App\Models\Accesses\MasterData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterData::insert([
            [
                'id' => 1,
                'master_dataname' => 'Kelompok Product',
                'master_datalink' => 'products/kelompok-products',
                'master_data_group_id' => 1,
                'user_id' => 1,
            ],
            [
                'id' => 2,
                'master_dataname' => 'Satuan',
                'master_datalink' => 'products/satuans',
                'master_data_group_id' => 1,
                'user_id' => 1,
            ],
            [
                'id' => 3,
                'master_dataname' => 'Product',
                'master_datalink' => 'products/products',
                'master_data_group_id' => 1,
                'user_id' => 1,
            ],
            [
                'id' => 5,
                'master_dataname' => 'User Group',
                'master_datalink' => 'accesses/user-groups',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 6,
                'master_dataname' => 'User',
                'master_datalink' => 'accesses/users',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 7,
                'master_dataname' => 'Master Data Group',
                'master_datalink' => 'accesses/master-data-groups',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 8,
                'master_dataname' => 'Master Data',
                'master_datalink' => 'accesses/master-datas',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 9,
                'master_dataname' => 'Master Data Access',
                'master_datalink' => 'accesses/master-data-accesses',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 10,
                'master_dataname' => 'Merk',
                'master_datalink' => 'products/merks',
                'master_data_group_id' => 1,
                'user_id' => 1,
            ],
            [
                'id' => 11,
                'master_dataname' => 'Stock Awal',
                'master_datalink' => 'products/product-stocks',
                'master_data_group_id' => 1,
                'user_id' => 1,
            ],
            [
                'id' => 12,
                'master_dataname' => 'Customer',
                'master_datalink' => 'mitra/customers',
                'master_data_group_id' => 3,
                'user_id' => 1,
            ],
            [
                'id' => 13,
                'master_dataname' => 'Supplier',
                'master_datalink' => 'mitra/suppliers',
                'master_data_group_id' => 3,
                'user_id' => 1,
            ],
            [
                'id' => 14,
                'master_dataname' => 'Marketing',
                'master_datalink' => 'mitra/marketings',
                'master_data_group_id' => 3,
                'user_id' => 1,
            ],
            [
                'id' => 15,
                'master_dataname' => 'Team Marketing',
                'master_datalink' => 'mitra/marketing-teams',
                'master_data_group_id' => 3,
                'user_id' => 1,
            ],
            [
                'id' => 16,
                'master_dataname' => 'Menu',
                'master_datalink' => 'accesses/menus',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 17,
                'master_dataname' => 'Menu Access',
                'master_datalink' => 'accesses/menu-accesses',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 18,
                'master_dataname' => 'Menu Dashboard',
                'master_datalink' => 'accesses/system-dashboards',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ],
            [
                'id' => 19,
                'master_dataname' => 'Dashbord Access',
                'master_datalink' => 'accesses/dashboard-accesses',
                'master_data_group_id' => 2,
                'user_id' => 1,
            ]
        ]);
    }
}
