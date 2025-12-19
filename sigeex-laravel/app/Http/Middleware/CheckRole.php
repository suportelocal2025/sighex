<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        if (!in_array($request->user()->papel, $roles)) {
            abort(403, 'Acesso não autorizado');
        }

        return $next($request);
    }
}
