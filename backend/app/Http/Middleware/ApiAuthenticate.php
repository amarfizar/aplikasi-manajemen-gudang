<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? ['sanctum', 'web'] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $request->setUserResolver(fn () => Auth::guard($guard)->user());
                return $next($request);
            }
        }

        // For Sanctum token authentication, try to get user from token
        if ($request->bearerToken()) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                $request->setUserResolver(fn () => $user);
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }
}