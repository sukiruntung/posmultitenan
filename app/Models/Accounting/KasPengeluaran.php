<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasPengeluaran extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'kas_pengeluaran_tanggal',
        'kas_harian_id',
        'kasir_id',
        'kategori_pengeluaran_id',
        'kas_pengeluaran_notes',
        'kas_pengeluaran_jumlah',
        'kas_pengeluaran_notransaksi',
        'user_id'
    ];

    public function kategoriPengeluaran()
    {
        return $this->belongsTo(KategoriPengeluaran::class);
    }
    public function kasPemasukkan()
    {
        return $this->morphMany(KasPemasukkan::class, 'kasPemasukkanSumber');
    }
}
