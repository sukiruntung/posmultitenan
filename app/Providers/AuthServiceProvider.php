<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Accesses\Menu;
use App\Policies\MenuPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // ...existing mappings...
        // Menu::class => MenuPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
