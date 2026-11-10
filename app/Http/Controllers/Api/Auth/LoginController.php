<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Services\AuthenticatorInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthenticatorInterface $authenticator,
    ) {}

    public function __invoke(LoginRequest $request): UserResource
    {
        $user = $this->authenticator->attempt($request->validated());

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return new UserResource($user);
    }
}
