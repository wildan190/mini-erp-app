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

        // Eager-load roles with their permissions so FE can apply RBAC immediately
        $user->load('roles.permissions');

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $tokenResult->accessToken,
            'user'    => $user,
            'roles'   => $user->roles->map(fn ($r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'slug'        => $r->slug,
                'permissions' => $r->permissions->map(fn ($p) => [
                    'slug'   => $p->slug,
                    'module' => $p->module,
                ]),
            ]),
        ]);
    }
}
