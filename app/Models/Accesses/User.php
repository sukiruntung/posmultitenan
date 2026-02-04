<?php

namespace App\Models\Accesses;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasAuthOutlet;
use App\Traits\HasDashboardAccess;
use App\Traits\HasLaporanAccess;
use App\Traits\HasMasterDataAccess;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\HasMenuAccess;
use Illuminate\Testing\Fluent\Concerns\Has;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasMenuAccess, HasMasterDataAccess, HasDashboardAccess, HasLaporanAccess, HasAuthOutlet;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_group_id',
        'is_kasir',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function hasAccess(int $masterDataID, int $groupID, string $permission): bool
    {
        $result = MasterDataAccess::query()
            ->where('master_data_id', $masterDataID)
            ->where('user_group_id', $groupID)
            ->where($permission, true)
            ->exists();

        return $result;
    }

    public function hasMenuAccesses(int $menuID, int $groupID, string $permission): bool
    {
        $result = MenuAccess::query()
            ->where('menu_id', $menuID)
            ->where('user_group_id', $groupID)
            ->where($permission, true)
            ->exists();
        return $result;
    }
    public function dashboardAcceses(int $masterDataID, int $groupID, string $permission): bool
    {
        $result = DashboardAccess::query()
            ->where('id', $masterDataID)
            ->where('user_group_id', $groupID)
            ->where($permission, true)
            ->exists();

        return $result;
    }
    public function masterDataAccesses()
    {
        return $this->hasMany(MasterDataAccess::class, 'user_group_id');
    }

    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function userOutlet()
    {
        return $this->hasOne(UserOutlet::class, 'user_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(Outlet::class, 'owner_id');
    }
}
