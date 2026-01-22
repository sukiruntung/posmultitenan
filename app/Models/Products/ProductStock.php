<?php

namespace App\Models\Products;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_stock_sn',
        'product_stock_ed',
        'stock',
        'user_id',

    ];
    protected $casts = [
        'stock' => 'integer',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function productStockHistories()
    {
        return $this->hasMany(ProductStockHistories::class, 'product_stock_id');
    }
}
