<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SingleSession
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
            
            // Skip single session check for admin role
            if ($user->role === 'admin') {
                return $next($request);
            }
            
            $currentSessionId = Session::getId();
            $userId = $user->_id ?? $user->id;
            
            // Check if user has an active session in database
            $existingSession = DB::table('sessions')
                ->where('user_id', (string) $userId)
                ->where('id', '!=', $currentSessionId)
                ->first();
            
            if ($existingSession) {
                // Delete the old session(s)
                DB::table('sessions')
                    ->where('user_id', (string) $userId)
                    ->where('id', '!=', $currentSessionId)
                    ->delete();
            }
            
            // Update current session with user_id
            DB::table('sessions')
                ->where('id', $currentSessionId)
                ->update([
                    'user_id' => (string) $userId,
                    'last_activity' => time()
                ]);
        }
        
        return $next($request);
    }
}
