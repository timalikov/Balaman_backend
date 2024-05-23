<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     operationId="getUsers",
     *     tags={"Admin"},
     *     summary="Retrieve all users with their roles",
     *     description="Returns a list of users with simplified role details.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index()
    {
        $users = User::with('roles:id,name')->get();

        $simplifiedUsers = $users->map(function ($user) {
            $role = $user->roles->first();
            $user->role_id = $role ? $role->id : 2;
            $user->role_name = $role ? $role->name : user;

            unset($user->roles);

            return $user;
        });

        return $simplifiedUsers;
    }

    /**
     * @OA\Get(
     *     path="/admin/roles",
     *     operationId="getRoles",
     *     tags={"Admin"},
     *     summary="Retrieve all roles",
     *     description="Returns a list of all roles.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function showRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return $roles;
    }

    /**
     * @OA\Post(
     *     path="/admin/users/{user}/assign-role",
     *     operationId="assignRole",
     *     tags={"Admin"},
     *     summary="Assign role to a user",
     *     description="Assigns a specified role to the selected user.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pass role details",
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function assignRole(Request $request, User $user)
    {
        try {
            $request->validate([
                'role' => 'required|numeric|exists:roles,id'  
            ]);

            $role = Role::findOrFail($request->role);
            $user->roles()->detach();

            $user->assignRole($role->name);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            $user->refresh();  
            
            $check = User::find($user->id);
            $check->refresh();
            
            return response()->json(['success' => 'Role assigned successfully.'], 200);
        } catch (\Exception $e) {
            \Log::error("Failed to assign role: " . $e->getMessage());
            return response()->json(['error' => 'Role assignment failed.'], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/admin/users/{user}/remove-role",
     *     operationId="removeRole",
     *     tags={"Admin"},
     *     summary="Remove roles from a user",
     *     description="Removes all roles from the selected user and assigns a default 'user' role.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles removed successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function removeRole(Request $request, User $user)
    {
        try {
            $user->roles()->detach();
            $user->assignRole('user');
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $user->refresh();

            return response()->json(['success' => 'Role removed successfully.'], 200);
        } catch (\Exception $e) {
            \Log::error("Failed to remove role: " . $e->getMessage());
            return response()->json(['error' => 'Role removal failed.'], 500);}
    }

}
