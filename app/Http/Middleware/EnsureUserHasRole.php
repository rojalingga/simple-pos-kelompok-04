<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            $required = implode(' atau ', $roles);

            abort(403, "Halaman ini hanya untuk peran {$required}.");
        }

        return $next($request);
    }
}