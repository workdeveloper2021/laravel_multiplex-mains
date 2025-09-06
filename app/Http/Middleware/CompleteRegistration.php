<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CompleteRegistration
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
            
            // Skip check for admin role - they don't need registration completion
            if ($user->role === 'admin') {
                return $next($request);
            }
            
            // Skip check for register-details routes and logout
            $skipRoutes = [
                'register.details',
                'register.details.save', 
                'logout'
            ];
            
            if (!in_array($request->route()->getName(), $skipRoutes)) {
                // Check if registration is incomplete
                if (empty($user->name) || empty($user->phone)) {
                    return redirect()->route('register.details');
                }
            }
        }
        
        return $next($request);
    }
}
