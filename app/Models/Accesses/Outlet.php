<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'outlet_name',
        'outlet_address',
        'owner_user_id',
        'outlet_logo',
        'outlet_hp',
        'user_id'
    ];
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function userOutlet()
    {
        return $this->hasMany(UserOutlet::class, 'outlet_id');
    }
}
