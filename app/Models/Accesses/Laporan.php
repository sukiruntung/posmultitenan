<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporan extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'nama_laporan',
        'kode_laporan',
        'params',
        'path',
        'is_excel',
        'path_excel',
        'user_id',
    ];

    protected $attributes = [
        'params' => '{}',
    ];
    public function laporanAccesses()
    {
        return $this->hasMany(LaporanAccess::class, 'laporan_id');
    }
}
