<?php

namespace App\Services;

use App\Models\Accesses\User;
use App\Models\System\TextQueue;


class TextQueueService
{
    public function forUser(User $user): ?TextQueue
    {
        return TextQueue::orderBy('priority', 'desc')->first();
    }
}
