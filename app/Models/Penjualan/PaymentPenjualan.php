<?php

namespace App\Models\Penjualan;

use App\Models\Accesses\User;
use App\Models\Accounting\KasPemasukkan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPenjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'penjualan_barang_id',
        'payment_penjualan_tanggal',
        'payment_penjualan_metode',
        'payment_penjualan_status',
        'payment_penjualan_jumlah',
        'payment_penjualan_grandtotal',
        'payment_penjualan_bankname',
        'payment_penjualan_accountnumber',
        'payment_penjualan_approvalcode',
        'payment_penjualan_referenceid',
        'payment_penjualan_checkquenumber',
        'payment_penjualan_jatuhtempo',
        'payment_penjualan_notes',
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
    public function kasPemasukkan()
    {
        return $this->morphMany(KasPemasukkan::class, 'kasPemasukkanSumber');
    }
}
