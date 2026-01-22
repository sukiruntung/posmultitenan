<?php

namespace App\Models\Mitra;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'marketing_team_name',
        'marketing_id',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function marketing()
    {
        return $this->belongsTo(Marketing::class);
    }
}
