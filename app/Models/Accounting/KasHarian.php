<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasHarian extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'outlet_id',
        'kasir_id',
        'kas_harian_tanggalbuka',
        'kas_harian_tanggaltutup',
        'kas_harian_saldoawal',
        'kas_harian_saldoakhir',
        'kas_harian_saldoseharusnya',
        'kas_harian_selisih',
        'kas_harian_status',
        'kas_harian_notes',
        'user_id',
    ];
}
