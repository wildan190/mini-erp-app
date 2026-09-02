<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformMeController extends Controller
{
    /**
     * Return the authenticated user's profile along with their roles and permissions.
     * This endpoint is used by the frontend to refresh RBAC data for existing sessions
     * without requiring the user to re-login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // Eager-load roles with their permissions
        $user->load('roles.permissions');

        return response()->json([
            'user'  => $user,
            'roles' => $user->roles->map(fn ($r) => [
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
