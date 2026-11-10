<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Services\AuthenticatorInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    public function __construct(
        private readonly AuthenticatorInterface $authenticator,
    ) {}

    public function __invoke(Request $request): Response
    {
        $this->authenticator->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
