<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class MasterData extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'master_datas';
    protected $fillable = [
        'id',
        'master_dataname',
        'master_datalink',
        'master_data_group_id',
        'user_id',
    ];
    public function masterDataGroup()
    {
        return $this->belongsTo(MasterDataGroup::class, 'master_data_group_id');
    }
    public function masterDataAccess()
    {
        return $this->hasMany(MasterDataAccess::class, 'master_data_id');
    }
}
