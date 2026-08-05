<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlatformLogoutController extends Controller
{
    public function logout(Request $request)
    {
        $token = $request->user('platform')->token();

        if ($token) {
            $token->revoke();
        }

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
