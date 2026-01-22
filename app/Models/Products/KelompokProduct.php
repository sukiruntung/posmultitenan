<?php

namespace App\Models\Products;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelompokProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'kelompok_productname',
        'user_id',

    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
