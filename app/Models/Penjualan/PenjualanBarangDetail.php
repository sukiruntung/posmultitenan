<?php

namespace App\Models\Penjualan;

use App\Models\Accesses\User;
use App\Models\Products\Product;
use App\Models\Products\ProductStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanBarangDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'penjualan_barang_id',
        'product_stock_id',
        'product_id',
        'penjualan_barang_detailproduct_name',
        'penjualan_barang_detail_sn',
        'penjualan_barang_detail_ed',
        'penjualan_barang_detail_qty',
        'penjualan_barang_detail_price',
        'penjualan_barang_detail_discounttype',
        'penjualan_barang_detail_discount',
        'penjualan_barang_detail_total',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function penjualanBarang()
    {
        return $this->belongsTo(PenjualanBarang::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function productStock()
    {
        return $this->belongsTo(ProductStock::class);
    }
    public function salesOrderDetail()
    {
        return $this->belongsTo(SalesOrderDetail::class);
    }
}
