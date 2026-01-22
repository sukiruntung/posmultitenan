<?php

namespace App\Models\Mitra;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'first_product_stock_id',
        'harga_jual',
        'frekuensi'
    ];
}
