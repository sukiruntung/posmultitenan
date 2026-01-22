<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriPengeluaran extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'id',
        'outlet_id',
        'kategori_pengeluaran_kode',
        'kategori_pengeluaran_name',
        'user_id',
    ];

    public function kasPengeluaran()
    {
        return $this->hasMany(KasPengeluaran::class);
    }
}
