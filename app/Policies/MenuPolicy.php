<?php

namespace App\Policies;

use App\Models\Accesses\Menu;
use App\Models\Accesses\MenuAccess;
use App\Models\Accesses\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Cache;

class MenuPolicy
{
    use HandlesAuthorization;

    // optional: super-admin bypass (sesuaikan dengan aplikasi)
    public function before(User $user, $ability)
    {
        // if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
        //     return true;
        // }
    }

    public function view(User $user, int $id): bool
    {
        return $this->check($user,  $id, 'can_view');
    }

    public function create(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_create');
    }

    public function edit(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_edit');
    }

    public function delete(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_delete');
    }
    public function validate(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_validate');
    }
    public function unvalidate(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_unvalidate');
    }
    public function can_hargapembelian(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_hargapembelian');
    }
    public function print1(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_print1');
    }
    public function print2(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_print2');
    }
    public function ppn(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_ppn');
    }
    public function ongkir(User $user, int $id): bool
    {
        return $this->check($user, $id, 'can_ongkir');
    }

    protected function check(User $user, int $id, string $ability): bool
    {
        if (! $user) {
            return false;
        }

        $access = $user->getCachedMenuAccess($id);

        return $access ? (bool) ($access->{$ability} ?? false) : false;
    }
}
