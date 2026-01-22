<?php

namespace App\Models\Pembelian;

use App\Models\Accesses\User;
use App\Models\Accounting\KasPemasukkan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPenerimaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'penerimaan_barang_id',
        'payment_penerimaan_tanggal',
        'payment_penerimaan_metode',
        'payment_penerimaan_status',
        'payment_penerimaan_jumlah',
        'payment_penerimaan_grandtotal',
        'payment_penerimaan_bankname',
        'payment_penerimaan_accountnumber',
        'payment_penerimaan_approvalcode',
        'payment_penerimaan_referenceid',
        'payment_penerimaan_checkquenumber',
        'payment_penerimaan_jatuhtempo',
        'payment_penerimaan_notes',
        'user_id',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }
    public function kasPemasukkan()
    {
        return $this->morphMany(KasPemasukkan::class, 'kasPemasukkanSumber');
    }
}
