<?php

namespace App\Services;

use App\Contracts\Services\AuthenticatorInterface;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;

/**
 * 以 Session（web guard）管理登入狀態，供 Sanctum SPA 驗證使用。
 */
class SessionAuthenticator implements AuthenticatorInterface
{
    private readonly StatefulGuard $guard;

    public function __construct(AuthFactory $auth)
    {
        $guard = $auth->guard('web');
        assert($guard instanceof StatefulGuard);

        $this->guard = $guard;
    }

    public function attempt(array $credentials): ?User
    {
        if (! $this->guard->attempt($credentials)) {
            return null;
        }

        $user = $this->guard->user();
        assert($user instanceof User);

        return $user;
    }

    public function login(User $user): void
    {
        $this->guard->login($user);
    }

    public function logout(): void
    {
        $this->guard->logout();
    }
}
