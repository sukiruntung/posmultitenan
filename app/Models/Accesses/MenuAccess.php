<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuAccess extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'outlet_id',
        'menu_id',
        'user_group_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'can_validate',
        'can_unvalidate',
        'can_print1',
        'can_print2',
        'can_ppn',
        'ppn_rate',
        'can_ongkir',
        'can_hargapembelian',
        'user_id',
    ];
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id');
    }
}
