<?php

namespace App\Models\Accesses;

use App\Models\Accesses\DashboardAccess;
use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class SystemDashboard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'system_dashboardname',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function dashboardAccesses()
    {
        return $this->hasMany(DashboardAccess::class, 'system_dashboard_id');
    }
}
