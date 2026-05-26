<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isEmployee()) {
            return redirect()->route('my.dashboard');
        }

        return $next($request);
    }
}
