<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        // Support multiple permissions separated by pipe '|' or comma ',' (OR condition)
        $perms = preg_split('/[,|]/', $permission);
        $hasAccess = false;

        foreach ($perms as $p) {
            $trimmed = trim($p);
            if ($trimmed && $user->hasPermission($trimmed)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => "Forbidden. You do not have permission to perform this action ({$permission})."
            ], 403);
        }

        return $next($request);
    }
}
