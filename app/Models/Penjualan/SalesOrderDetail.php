<?php

namespace App\Models\Penjualan;

use App\Models\Accesses\User;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'sales_order_detailproduct_name',
        'sales_order_detail_qty',
        'sales_order_detail_qtyterpenuhi',
        'user_id',
    ];
    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
