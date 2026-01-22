<?php

namespace App\Models\Mitra;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marketing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'marketing_name',
        'marketing_email',
        'marketing_address',
        'marketing_phone1',
        'marketing_phone2',
        'marketing_team_id',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function marketingTeam()
    {
        return $this->belongsTo(MarketingTeam::class);
    }
    public function customerMarketing()
    {
        return $this->hasMany(CustomerMarketing::class, 'marketing_id', 'id');
    }
}
