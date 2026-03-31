<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // ❌ No autenticado
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }
            // 🔴 Usuario inactivo (🔥 ACÁ VA)
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Usuario inactivo'
            ], 403);
        }


        // ❌ Sin rol
        if (!$user->role) {
            return response()->json([
                'message' => 'Usuario sin rol asignado'
            ], 403);
        }

        // ❌ No tiene el rol requerido
        if (!in_array($user->role->name, $roles)) {
            return response()->json([
                'message' => 'No tienes permisos'
            ], 403);
        }

        return $next($request);
    }
}
