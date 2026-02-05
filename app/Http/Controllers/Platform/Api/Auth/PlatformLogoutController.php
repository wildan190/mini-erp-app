<?php

namespace App\Http\Controllers\Platform\Api\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\Auth\PlatformLogoutRequest;


class PlatformLogoutController extends Controller
{
    public function logout(PlatformLogoutRequest $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
