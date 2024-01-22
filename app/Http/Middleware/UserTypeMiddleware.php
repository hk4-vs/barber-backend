<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$userTypes)
    {
        $user = Auth::guard('api')->user();

        if ($user && in_array($user->type, $userTypes)) {
            return $next($request);
        }

        // User type not allowed, redirect or return an error response
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
