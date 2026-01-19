<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // OLD: if (Auth::check() && Auth::user()->role === 'admin')
        // NEW:
        if (Auth::check() && Auth::user()->role === UserRole::Admin) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'You do not have admin access.');
    }
}
