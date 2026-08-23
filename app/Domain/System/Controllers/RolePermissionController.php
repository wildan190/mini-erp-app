<?php

namespace App\Domain\System\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\System\Models\Role;
use App\Domain\System\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "System RBAC", description: "Role and Permission management endpoints")]
class RolePermissionController extends Controller
{
    public function indexRoles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        return response()->json(['success' => true, 'data' => $roles]);
    }

    public function storeRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|unique:roles,slug|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,uuid',
        ]);

        $role = Role::create([
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $permIds = Permission::whereIn('uuid', $validated['permissions'])->pluck('id');
            $role->permissions()->sync($permIds);
        }

        return response()->json(['success' => true, 'data' => $role->load('permissions')], 201);
    }

    public function indexPermissions(): JsonResponse
    {
        $permissions = Permission::all()->groupBy('module');
        return response()->json(['success' => true, 'data' => $permissions]);
    }

    public function assignUserRole(Request $request, string $userUuid): JsonResponse
    {
        $validated = $request->validate([
            'roles'   => 'required|array',
            'roles.*' => 'exists:roles,slug',
        ]);

        $user = User::where('uuid', $userUuid)->firstOrFail();

        // Safety prevention: If user is super-admin, ensure 'super-admin' role is maintained
        if ($user->hasRole('super-admin') && !in_array('super-admin', $validated['roles'])) {
            $validated['roles'][] = 'super-admin';
        }

        $roleIds = Role::whereIn('slug', $validated['roles'])->pluck('id');
        
        $user->roles()->sync($roleIds);

        return response()->json(['success' => true, 'message' => 'Roles assigned successfully', 'data' => $user->load('roles')]);
    }
}
