<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ForAuthUserGroupScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $builder->where('user_group_id', Auth::user()->user_group_id)
                ->where('outlet_id', Auth::user()->userOutlet->outlet_id);
        }
    }
}
