<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Container\Attributes\Auth as AuthAttribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Invalidate old sessions for non-admin users
     */
    private function invalidateOldSessions($user)
    {
        // Skip for admin users
        if ($user->role === 'admin') {
            return;
        }
        
        $userId = $user->_id ?? $user->id;
        $currentSessionId = session()->getId();
        
        // Delete all other sessions for this user
        DB::table('sessions')
            ->where('user_id', (string) $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
    public function redirectToGoogle()
    {
        // Store which route initiated the Google login
        $previousUrl = url()->previous();
        if (str_contains($previousUrl, 'user-login')) {
            session(['google_login_source' => 'user-login']);
        } else {
            session(['google_login_source' => 'login']);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     * Supports both channel and user login with different redirects.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            // $googleUser = Socialite::driver('google')->stateless()->user();
            $googleUser = Socialite::driver('google')->user();
            // dd($googleUser); // Ye lagane se redirect nahi hoga

            $email = $googleUser->getEmail();

            // Debug log
            Log::info('Google callback - Email: ' . $email);

            // Check if user exists in User table
            $user = User::where('email', $email)->first();
            // dd($user);
            if ($user) {
                // Existing user found
                // Check if coming from user-login route first (before login)
                $fromUserLogin = session('google_login_source') === 'user-login';
                session()->forget('google_login_source'); // Clear the session data

                if ($user->role === 'admin') {
                    Auth::login($user);
                    return redirect()->intended('/dashboard');
                }
                
                // Check for incomplete registration (skip for admin)
                if (empty($user->name) || empty($user->phone)) {
                    Auth::login($user);
                    return redirect()->route('register.details');
                }

                if ($fromUserLogin) {
                    // User-login route (Google login) - no status check needed
                    Auth::login($user);
                    $this->invalidateOldSessions($user); // Invalidate old sessions
                    if ($user->role === 'channel') {
                        return redirect()->intended('/front-movies');
                    } else {
                        return redirect()->intended('/dashboard');
                    }
                }

                // Login route (Google login) - check channel status BEFORE login
                if ($user->role === 'channel') {
                    $channel = \App\Models\Channel::where('user_id', (string) $user->_id)->first();
                    if ($channel) {
                        switch ($channel->status) {
                            case 'approve':
                                Auth::login($user); // Login only if approved
                                $this->invalidateOldSessions($user); // Invalidate old sessions
                                return redirect()->intended('/dashboard');
                            case 'pending':
                                return redirect()->route('login')->with('error', 'Your account is pending approval.');
                            case 'rejected':
                                return redirect()->route('login')->with('error', 'Your account has been rejected.');
                            case 'block':
                                return redirect()->route('login')->with('error', 'Your account has been blocked.');
                            default:
                                return redirect()->route('login')->with('error', 'Unknown channel status.');
                        }
                    } else {
                        return redirect()->route('login')->with('error', 'Channel record not found.');
                    }
                }

                // Regular user from login route
                Auth::login($user);
                $this->invalidateOldSessions($user); // Invalidate old sessions
                return redirect()->intended('/dashboard');
            } else {
                // New user - create account and redirect to register-details
                try {
                    $newUser = User::create([
                        'email' => $email,
                        'name' => $googleUser->getName(),
                        'role' => 'user', // Default role for new Google users
                        'join_date' => now(),
                        'status' => 'approve', // Auto-approve new users
                        'password' => '',
                    ]);

                    Auth::login($newUser);
                    return redirect()->route('register.details');
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), 'E11000')) {
                        return redirect()->route('login')->with('error', 'Email already registered.');
                    }
                    return redirect()->route('login')->with('error', 'Something went wrong.');
                }
            }
        } catch (\Exception $e) {
            // Debug log the exception
            Log::error('Google callback exception: ' . $e->getMessage());
            Log::error('Google callback trace: ' . $e->getTraceAsString());
            return redirect()->route('login')->with('error', 'Google login failed: ' . $e->getMessage());
        }
    }

    public function showForm()
    {
        return view('delete-user');
    }

    // Called via AJAX with email from frontend
    public function deleteByEmail(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email is required'], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !isset($user->_id)) {
            return response()->json(['success' => false, 'message' => 'User not found or _id missing.'], 404);
        }

        $userId = $user->_id;

        $deleteResponse = Http::get("https://multiplexplay.com/nodeapi/rest-api/v130/channel/removeuser", [
            'headers' => [
                'api-key' => 'ec8590cb04e0e37c6706ab6c',
            ],
            'user_id' => $userId,
        ]);

        if ($deleteResponse->body() === "true") {
            // Optional: delete from local DB too
            $user->delete();

            return response()->json(['success' => true, 'message' => 'User successfully deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete user from external system.'], 500);
    }
}
