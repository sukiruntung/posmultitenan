<?php

namespace App\Models\Accesses;

use App\Scopes\ForAuthUserGroupScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDataAccess extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'outlet_id',
        'master_data_id',
        'user_group_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'user_id',

    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ForAuthUserGroupScope());
    }
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id');
    }
    public function masterData()
    {
        return $this->belongsTo(MasterData::class, 'master_data_id');
    }
}
