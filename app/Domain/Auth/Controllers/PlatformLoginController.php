<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Auth\Requests\PlatformLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlatformLoginController extends Controller
{
    public function login(PlatformLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $guard       = Auth::guard('platform');
        $provider    = $guard->getProvider();
        $user        = $provider->retrieveByCredentials($credentials);

        if (! $user || ! Hash::check($credentials['password'], $user->getAuthPassword())) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $tokenResult = $user->createToken('platform-token');

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $tokenResult->accessToken,
            'user'    => $user,
        ]);
    }
}
