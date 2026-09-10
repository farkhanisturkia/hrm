<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            };
        };

        if (in_array($user->role, ['engineer', 'communicator', 'designer'])) {
            return to_route('task.list');
        } elseif (in_array($user->role, ['manager', 'leader'])) {
            return to_route('dashboard');
        };

        abort(403, 'Unauthorized action.');
    }
}
