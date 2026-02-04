<?php

namespace App\Models\Penjualan;

use App\Models\Accesses\Outlet;
use App\Models\Accesses\User;
use App\Models\Mitra\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanBarang extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'penjualan_barang_tanggal',
        'penjualan_barang_tanggaljth',
        'customer_id',
        'penjualan_barang_no',
        'penjualan_barang_total',
        'penjualan_barang_discounttype',
        'penjualan_barang_discount',
        'penjualan_barang_ppn',
        'penjualan_barang_ongkir',
        'penjualan_barang_grandtotal',
        'penjualan_barang_jumlahpayment',
        'notes',
        'penjualan_barang_validatedby',
        'penjualan_barang_validatedat',
        'penjualan_barang_status',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function penjualanBarangDetail()
    {
        return $this->hasMany(PenjualanBarangDetail::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
    public function paymentPenjualan()
    {
        return $this->hasMany(PaymentPenjualan::class);
    }
}
