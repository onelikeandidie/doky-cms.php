<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if ($request->user()->roles()->where('name', $role)->exists()) {
            return $next($request);
        }

        abort(403);
    }
}
