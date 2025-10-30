<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // usage: ->middleware('role:admin|manager|cashier')
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401); // not logged in
        }

        // normalize both sides to lowercase
        $userRole = strtolower((string)($user->role ?? ''));

        // allow "role:admin|manager|cashier" OR arrays passed by the router
        $allowed = array_map(
            fn ($r) => strtolower(trim($r)),
            is_array($roles) ? $roles : explode('|', $roles)
        );

        if (!in_array($userRole, $allowed, true)) {
            abort(403); // logged in but not allowed
        }

        return $next($request);
    }
}
