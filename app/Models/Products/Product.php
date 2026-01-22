<?php

namespace App\Models\Products;

use App\Models\Accesses\User;
use App\Models\Mitra\SupplierProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'product_kode',
        'product_catalog',
        'product_name',
        'product_slug',
        'product_minstock',
        'kelompok_product_id',
        'hargajualgrosir',
        'hargajual1',
        'hargajual2',
        'hargajual3',
        'satuan_id',
        'user_id',

    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function kelompokProduct(): BelongsTo
    {
        return $this->belongsTo(KelompokProduct::class, 'kelompok_product_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
    public function merk(): BelongsTo
    {
        return $this->belongsTo(Merk::class, 'merk_id');
    }
    public function productStock()
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }
}
