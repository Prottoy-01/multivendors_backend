<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VendorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized. Vendors only'], 403);
        }
        return $next($request);
    }
}
