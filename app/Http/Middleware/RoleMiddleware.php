<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $sessionRole = strtolower((string) session('role'));
        $allowedRoles = empty($roles)
            ? []
            : array_map('strtolower', $roles);

        if (! empty($allowedRoles) && ! in_array($sessionRole, $allowedRoles, true)) {
            return redirect()->route('login')->withErrors(['unauthorized' => 'Unauthorized access.']);
        }

        return $next($request);
    }
}