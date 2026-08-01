<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to the 'admin' role (users and settings), on top of
 * EnsureAdminAccess which already allows 'admin' and 'editor' into /admin.
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only administrators can access this section.');
        }

        return $next($request);
    }
}
