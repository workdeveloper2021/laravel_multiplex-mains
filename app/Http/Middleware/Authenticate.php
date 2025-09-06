<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

//        $path = $request->path();
//
//        if (str_contains($path, 'user-login')) {
//            return redirect()->route('user-login');
//        }
//
//        if (str_contains($path, 'login')) {
//            return redirect()->route('login'); // assuming 'login' is for admin
//        }

//        return $next($request);

        return $next($request);
    }
}
