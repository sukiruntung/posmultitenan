<?php

namespace App\Models\Mitra;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'first_product_stock_id',
        'harga_beli',
        'frekuensi'
    ];
}
