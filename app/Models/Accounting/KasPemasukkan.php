<?php

namespace App\Models\Accounting;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasPemasukkan extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'kas_harian_id',
        'kasir_id',
        'kas_pemasukkan_jenis',
        'kas_pemasukkan_jumlah',
        'kas_pemasukkan_sumber',
        'kas_pemasukkan_notes',
        'kas_pemasukkan_reference',
        'kas_pemasukkan_notransaksi',
        'user_id',
    ];

    public function kas_harian()
    {
        return $this->belongsTo(KasHarian::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function kasPemasukkanSumber()
    {
        return $this->morphTo();
    }
}
