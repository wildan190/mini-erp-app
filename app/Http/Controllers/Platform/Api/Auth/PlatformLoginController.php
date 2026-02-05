<?php

namespace App\Http\Controllers\Platform\Api\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\Auth\PlatformLoginRequest;
use Illuminate\Support\Facades\Auth;


class PlatformLoginController extends Controller
{
    public function login(PlatformLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('platform-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }
}
