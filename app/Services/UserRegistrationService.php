<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserRegistrationServiceInterface;
use App\Models\User;

class UserRegistrationService implements UserRegistrationServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function register(array $data): User
    {
        return $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }
}
