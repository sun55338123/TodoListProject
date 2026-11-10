<?php

namespace App\Contracts\Services;

use App\Models\User;

interface UserRegistrationServiceInterface
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User;
}
