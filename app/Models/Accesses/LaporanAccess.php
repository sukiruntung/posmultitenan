<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanAccess extends Model
{

    use HasFactory, SoftDeletes;
    protected $fillable = [
        'laporan_id',
        'user_group_id',
        'user_id',
    ];


    public function laporan()
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }
}
