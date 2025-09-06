<?php
// app/Http/Middleware/GeoIpCountry.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class GeoIpCountry
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('country_code')) {
            $ip = $request->ip(); // production: trust proxy
            try {
                if ($position = Location::get($ip)) {
                    $code = strtoupper($position->countryCode ?? '');
                    if ($code && strlen($code) === 2) {
                        session(['country_code' => $code]);
                    }
                }
            } catch (\Throwable $e) {
                // silent fail
            }
        }

        // ultimate default
        if (!session('country_code')) {
            session(['country_code' => 'IN']);
        }

        return $next($request);
    }
}
