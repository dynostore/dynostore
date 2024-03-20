<?php

namespace App\Http\Middleware;

use Closure;
use Http;
use Illuminate\Http\Request;

if (!defined('AUTH')) {
    define('AUTH', env('AUTH_HOST'));
}

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $tokenuser = $request->route()->parameter('tokenuser');
        $url = "http://" . AUTH . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        return $next($request);
    }
}
