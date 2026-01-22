<?php

namespace App\Models\Pembelian;

use App\Models\Accesses\User;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenerimaanBarangDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'penerimaan_barang_id',
        'product_id',
        'penerimaan_barang_detailproduct_name',
        'penerimaan_barang_detail_sn',
        'penerimaan_barang_detail_ed',
        'penerimaan_barang_detail_qty',
        'penerimaan_barang_detail_price',
        'penerimaan_barang_detail_discounttype',
        'penerimaan_barang_detail_discount',
        'penerimaan_barang_detail_total',
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
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function getTotalAttribute()
    {
        if ($this->penerimaan_barang_detail_discounttype === 'persen') {
            $discountAmount = ($this->penerimaan_barang_detail_qty * $this->penerimaan_barang_detail_price) * ($this->penerimaan_barang_detail_discount / 100);
        } else {
            $discountAmount = $this->penerimaan_barang_detail_discount;
        }
        return $this->penerimaan_barang_detail_qty * $this->penerimaan_barang_detail_price - $discountAmount;
    }
}
