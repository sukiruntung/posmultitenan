<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'id',
        'menu_name',
        'menu_link',
        'menu_icon',
        'user_id',
    ];
    public function menuAccesses()
    {
        return $this->hasMany(MenuAccess::class, 'menu_id');
    }
}
