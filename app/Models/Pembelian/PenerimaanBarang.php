<?php

namespace App\Models\Pembelian;

use App\Models\Accesses\User;
use App\Models\Mitra\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenerimaanBarang extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'penerimaan_barang_tanggal',
        'supplier_id',
        'penerimaan_barang_invoicenumber',
        'penerimaan_barang_total',
        'penerimaan_barang_discounttype',
        'penerimaan_barang_discount',
        'penerimaan_barang_ppn',
        'penerimaan_barang_grandtotal',
        'notes',
        'penerimaan_barang_validatedby',
        'penerimaan_barang_validatedat',
        'penerimaan_barang_status',
        'penerimaan_barang_jumlahpayment',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function penerimaanBarangDetail()
    {
        return $this->hasMany(PenerimaanBarangDetail::class);
    }
    public function paymentPenerimaan()
    {
        return $this->hasMany(PaymentPenerimaan::class);
    }
    public function getGrandtotalAttribute()
    {
        $total = $this->penerimaan_barang_total;
        if ($this->penerimaan_barang_discounttype === 'persen') {
            $discountAmount = $total * ($this->penerimaan_barang_discount / 100);
        } else {
            $discountAmount = $this->penerimaan_barang_discount;
        }
        $total -= $discountAmount;

        return $total;
    }
}
