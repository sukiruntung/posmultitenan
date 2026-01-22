<?php

namespace App\Models\Accesses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DashboardAccess extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'outlet_id',
        'system_dashboard_id',
        'can_view',
        'user_group_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class);
    }
    public function systemDashboard()
    {
        return $this->belongsTo(SystemDashboard::class, 'system_dashboard_id');
    }
}
