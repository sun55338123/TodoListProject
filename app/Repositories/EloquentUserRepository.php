<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $attributes): User
    {
        // 密碼由 User 模型的 hashed cast 負責雜湊。
        return User::create($attributes);
    }
}
