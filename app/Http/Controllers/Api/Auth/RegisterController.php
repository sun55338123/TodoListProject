<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Services\AuthenticatorInterface;
use App\Contracts\Services\UserRegistrationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private readonly UserRegistrationServiceInterface $registration,
        private readonly AuthenticatorInterface $authenticator,
    ) {}

    /**
     * 註冊後直接登入。
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->registration->register($request->validated());

        $this->authenticator->login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(201);
    }
}
