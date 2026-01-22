<?php

namespace App\Models\Penjualan;

use App\Models\Accesses\User;
use App\Models\Mitra\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'sales_orders_tanggal',
        'customer_id',
        'sales_order_no',
        'notes',
        'user_id',
    ];
    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function salesOrderDetails()
    {
        return $this->hasMany(SalesOrderDetail::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
