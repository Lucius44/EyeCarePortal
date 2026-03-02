<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserStatus;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. If the active user was banned, kick them out immediately
            if ($user->account_status === UserStatus::Banned) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been permanently deactivated due to a policy violation. Please contact the clinic for more information.'
                ]);
            }

            // 2. Race Condition Guard: If the user is restricted but somehow tries to submit a booking
            if ($user->account_status === UserStatus::Restricted && $request->routeIs('appointments.store')) {
                return back()->with('error', 'Your account is currently restricted. You cannot book new appointments.');
            }
        }

        return $next($request);
    }
}