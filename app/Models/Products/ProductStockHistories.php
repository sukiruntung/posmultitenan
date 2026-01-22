<?php

namespace App\Models\Products;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStockHistories extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tanggal',
        'product_stock_id',
        'qty_masuk',
        'qty_keluar',
        'stock_awal',
        'stock_akhir',
        'harga_beli',
        'total_biaya_beli',
        'harga_jual',
        'jenis',
        'keterangan',
        'no_transaksi',
        'user_id',
    ];
    protected $casts = [
        'tanggal' => 'datetime',
    ];
    public function productStock()
    {
        return $this->belongsTo(ProductStock::class, 'product_stock_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
