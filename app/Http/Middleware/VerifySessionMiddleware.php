<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySessionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $secret = $request->header('X-SESSION-SECRET');

        if (!$secret) {
            return response()->json([
                'message' => 'Session service secret missing'
            ], 401);
        }

        if ($secret !== env('JWT_SECRET')) {
            return response()->json([
                'message' => 'Invalid session service secret'
            ], 401);
        }

        return $next($request);
    }
}
