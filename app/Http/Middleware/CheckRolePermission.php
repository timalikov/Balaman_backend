<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();

        // Check if the user has a role and if the role has the specified permission
        if (!$user || !$user->role || !$user->role->hasPermission($permission)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
