<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        // Check if role matches
        if ($user->role !== $role) {
            return response()->json([
                'message' => 'Forbidden: You do not have the required role (' . $role . ').'
            ], 403);
        }

        // Special check for socio: Must be active
        if ($role === 'socio') {
            $business = \App\Models\Negocio::where('user_id', $user->id)->first();
            if (!$business || $business->estado === 'pendiente') {
                return response()->json([
                    'message' => 'Tu negocio todavía está en revisión. El administrador lo activará pronto.',
                    'status' => 'pending_approval'
                ], 403);
            }
        }

        return $next($request);
    }
}
