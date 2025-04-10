<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // If no roles are specified, continue
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has any of the specified roles
        if ($request->user()->hasAnyRole($roles)) {
            return $next($request);
        }

        // If none of the roles match, redirect or abort
        abort(403, 'Unauthorized action.');
    }
}
