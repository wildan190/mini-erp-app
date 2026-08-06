<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Auth\Requests\PlatformRegisterRequest;
use App\Models\User;

class PlatformRegisterController extends Controller
{
    public function register(PlatformRegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $tokenResult = $user->createToken('platform-token');

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token'   => $tokenResult->accessToken,
            'user'    => $user,
        ], 201);
    }
}
