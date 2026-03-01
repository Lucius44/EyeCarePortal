<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Intercept the Throttle exception to keep our UI clean
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            // If it's a standard web request (not an API call), redirect back with an error
            if (! $request->expectsJson()) {
                
                // Extract the exact seconds remaining from the exception headers
                $headers = $e->getHeaders();
                $retryAfterSeconds = $headers['Retry-After'] ?? 3600; // Default to 1 hour if missing
                
                // Convert seconds to minutes (rounding up so it doesn't say 0 minutes)
                $minutes = ceil((int) $retryAfterSeconds / 60);
                
                // Format the wording properly for grammar (1 minute vs X minutes)
                $timeString = $minutes > 1 ? "{$minutes} minutes" : "1 minute";

                return back()->with('error', "Too many upload attempts. Please wait {$timeString} before trying again.");
            }
        });

    })->create();