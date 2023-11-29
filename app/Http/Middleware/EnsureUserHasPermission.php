<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        /** @var Collection<Role> $roles */
        $roles = $request->user()->roles()->get();
        $role_with_permission = $roles->first(fn(Role $role) => $role->hasPermission($permission));
        if ($role_with_permission) {
            return $next($request);
        }

        abort(403, 'You do not have the permission ' . $permission . ' to access this page.');
    }
}
