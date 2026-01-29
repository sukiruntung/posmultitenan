<?php

namespace App\Traits;

use App\Models\Accesses\User;

trait HasAuthOutlet
{
    protected function authUserWithOutlet(): ?User
    {
        $user = auth()->user();

        if (!$user || !$user->userOutlet) {
            return null;
        }

        return $user;
    }
}
