<?php

namespace App\Policies;

use App\Models\DailyTask;
use App\Models\User;

class DailyTaskPolicy
{
    public function update(User $user, DailyTask $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function delete(User $user, DailyTask $task): bool
    {
        return $task->user_id === $user->id;
    }
}
