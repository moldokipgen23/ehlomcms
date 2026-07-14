<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            $roles = ['admin', 'staff'];
        }

        if (!$user->role || !in_array($user->role->name, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
