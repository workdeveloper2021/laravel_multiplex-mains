<?php

// app/Http/Middleware/DetectCountry.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DetectCountry
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Highest priority: user selected preference
        if ($pref = $request->user()?->country_code) {
            session(['country_code' => strtoupper($pref)]);
            return $next($request);
        }

        // 2) Cloudflare country header
        $cf = $request->server('HTTP_CF_IPCOUNTRY'); // e.g., 'IN'
        if ($cf && strlen($cf) === 2) {
            session(['country_code' => strtoupper($cf)]);
            return $next($request);
        }

        // 3) Fallback (set below via GeoIP or default)
        return $next($request);
    }
}
