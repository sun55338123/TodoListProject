<?php

namespace App\Contracts\Services;

use App\Models\User;

/**
 * 使用者登入狀態的管理。Session 的重新產生屬於 HTTP 層，由 Controller 負責。
 */
interface AuthenticatorInterface
{
    /**
     * 驗證帳號密碼並登入，成功時回傳使用者，失敗時回傳 null。
     *
     * @param  array{email: string, password: string}  $credentials
     */
    public function attempt(array $credentials): ?User;

    public function login(User $user): void;

    public function logout(): void;
}
