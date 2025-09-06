<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
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
    // Show Login Form
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard')->with('message', 'You are already logged in.');
        }

        return view('auth.login');
    }

    public function showUserLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard')->with('message', 'You are already logged in.');
        }

        return view('auth.UserLogin');
    }

    /**
     * Handle user-login route normal login
     */
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // User doesn't exist - show message, no auto-register
            return redirect()->back()->with('error', 'Mobile app se login karein, fir yahan login karein');
        }

        if (!$user || md5($request->password) !== $user->password) {
            return redirect()->back()->withErrors(['email' => 'Invalid email or password.']);
        }

        // User exists - render FrontendPlayer/index.blade.php
        Auth::login($user);
        $this->invalidateOldSessions($user); // Invalidate old sessions
        return view('FrontendPlayer.index');
    }

    public function sendOtpToUser(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20'
        ]);

        // Step 1: Send user data to API to request OTP
        $response = Http::withHeaders([
            'api-key' => env('NODE_API_KEY'),
            'Accept' => 'application/json',
        ])->post('https://multiplexplay.com/nodeapi/rest-api/v130/reguser', [
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // Step 2: Check if OTP sent
        if ($response->successful() && isset($response['user']['otp'])) {
            $otp = $response['user']['otp'];
            $user_id = $response['user']['user_id'] ?? null;

            // Optional: Store data in session (recommended for verification step)
            session([
                'user_id' => $user_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'otp' => $otp,
            ]);

            // Redirect to OTP page
            return redirect()->route('verify')->with('success', 'OTP sent successfully!');
        }

        return back()->with('error', 'Unable to send OTP. Please try again.');
    }


    public function showOtpForm()
    {
        return view('auth.otp');
    }
    public function verifyOtp(Request $request)
    {
        // Step 1: Merge OTP boxes into one string
        $otp = is_array($request->otp) ? implode('', $request->otp) : $request->otp;

        // Step 2: Send OTP + phone/email to API
        $response = Http::withHeaders([
            'api-key' => env('NODE_API_KEY'),
            'Accept' => 'application/json',
        ])->post(env('NODE_API_URL') . '/reguser/verify-otp', [
            'otp' => $otp,
            'user_id' => $request->user_id,
        ]);

        // dd($response);
        // Step 3: Check response
        if ($response->successful() && isset($response['userId'])) {
            $user = $response['userId'];

            Auth::loginUsingId($user);
            return redirect()->intended('/front-movies');
            //            return view('FrontendPlayer.index', compact('user'));
        } else {
            return back()->with('error', $response['message']);
        }

        return back()->with('error', 'OTP verification failed.');
    }



    // Show Registration Form
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->back()->with('message', 'You are already registered and logged in.');
        }
        return view('auth.register');
    }

    // Handle Login Request
    // public function login(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();
    //     // dd($request->email);
    //     // dd($user->id);
    //     // if(!$user) {
    //     //     return redirect()->back()->withErrors(['email' => 'Invalid email or password.']);
    //     // }
    //     $channel_user = Channel::where('user_id', (string) $user->id)->first();
    //     // if(!$channel_user) {
    //     //     return redirect()->back()->withErrors(['email' => 'Invalid email or password.']);
    //     // }

    //     if (!$user || !md5($request->password, $user->password)) {
    //         return redirect()->back()->withErrors(['email' => 'Invalid email or password.']);
    //     }
    //     //        dd($user->role);
    //     if ($user->role === 'admin') {
    //         Auth::login($user);
    //         return redirect()->intended('/dashboard');
    //     }

    //     if ($user->role === 'channel') {
    //         // dd($channel_user);
    //         switch ($channel_user->status) {

    //             case 'approve':
    //                 // dd($channel_user);
    //                 Auth::login($user);
    //                 return redirect()->intended('/dashboard');

    //             case 'pending':
    //                 return redirect()->back()->withErrors(['email' => 'Your account is pending approval. Please wait for admin confirmation.']);

    //             case 'rejected':
    //                 return redirect()->back()->withErrors(['email' => 'Your account has been rejected. Contact support for more information.']);

    //             case 'block':
    //                 return redirect()->back()->withErrors(['email' => 'Your account has been blocked. Please contact admin.']);

    //             default:
    //                 return redirect()->back()->withErrors(['email' => 'Unknown account status.']);
    //         }
    //     }
    //     return redirect()->back()->withErrors(['email' => 'You are  not authorized']);
    // }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // User doesn't exist in login route - redirect to register
            return redirect()->route('register');
        }

        if (md5($request->password) !== $user->password) {
            return redirect()->back()->withErrors(['email' => 'Invalid email or password.']);
        }

        // Fetch channel if channel user
        $channel_user = Channel::where('user_id', $user->id)->first();

        if ($user->role === 'admin') {
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        if ($user->role === 'channel') {
            if (!$channel_user) {
                return redirect()->back()->withErrors(['email' => 'Channel record not found.']);
            }

            switch ($channel_user->status ?? null) {
                case 'approve':
                    Auth::login($user, true); // Remember user
                    $this->invalidateOldSessions($user); // Invalidate old sessions
                    return redirect()->intended('/dashboard');

                case 'pending':
                    return redirect()->back()->withErrors(['email' => 'Your account is pending approval. Please wait for admin confirmation.']);

                case 'rejected':
                    return redirect()->back()->withErrors(['email' => 'Your account has been rejected. Contact support for more information.']);

                case 'block':
                    return redirect()->back()->withErrors(['email' => 'Your account has been blocked. Please contact admin.']);

                default:
                    return redirect()->back()->withErrors(['email' => 'Unknown account status or channel not found.']);
            }
        }

        if ($user->role === 'user') {
            Auth::login($user, true);
            $this->invalidateOldSessions($user); // Invalidate old sessions
            return redirect()->intended('/dashboard');
        }

        return redirect()->back()->withErrors(['email' => 'You are not authorized']);
    }




    // Handle Registration Request
    //    public function register(Request $request)
    //    {
    ////        dd($request->toArray());
    //        $this->validateRegistration($request);
    //
    //        $user = User::create([
    //            'email' => $request->email,
    //            'password' => md5($request->password), // Use MD5 hashing for password
    //        ]);
    //        Auth::login($user);
    //
    //        return redirect('/dashboard');
    //    }

    /**
     * Show the register detail form after successful Google login,
     * only if the user's name and phone are not filled yet.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showRegisterDetailForm()
    {
        $user = Auth::user();


        /* if ($user->name && $user->phone) {
            return redirect('/dashboard');
        } */

        return view('auth.register-detail');
    }

    public function register(Request $request)
    {
        // Validate input fields including the file
        $request->validate([
            'channel_name' => 'nullable|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'organization_name' => 'required|string|max:255',
            'organization_address' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB, allowed file types
        ]);

        if (User::where('email', $request->email)->exists()) {
            return back()
                ->withErrors(['email' => 'The email has already been taken.'])
                ->withInput();
        }

        $isChannel = $request->filled('channel_name');
        $role = $isChannel ? 'channel' : 'user'; // Handle file upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('channel_documents', 'public');
            $documentPath = asset('storage/' . $path); // This will generate full URL
        }

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->mobile,
            'password' => md5($request->password),
            'role' => $role,
            'join_date' => now(),
            'status' => $isChannel ? 'pending' : 'approve',
        ]);
        // If channel registration, create Channel record with document path
        if ($isChannel) {
            $channel = Channel::create([
                'channel_name' => $request->channel_name,
                'user_id' => (string) $user->_id, // Assuming MongoDB _id, cast to string
                'email' => $request->email,
                'mobile_number' => $request->mobile,
                'address' => $request->address,
                'organization_name' => $request->organization_name,
                'organization_address' => $request->organization_address,
                'status' => 'pending',
                'img' => 'https://multiplexplay.com/office/uploads/cuser_image/11.jpg', // default image
                'document_path' => $documentPath,  // Save file path here
                'join_date' => now(),
                'last_login' => now(),
            ]);
            // Update User with channel_id
            $user->channel_id = (string) $channel->_id;
            $user->save();

            Auth::logout();
            return redirect()->route('login')->with('success', 'Channel registration submitted successfully. Please wait for admin approval.');
        }

        return redirect('/dashboard');
    }

    /**
     * Store the user's additional registration details after Google login.
     * Creates a channel if `channel_name` is provided.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRegisterDetail(Request $request)
    {
        $request->validate([
            'channel_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'mobile' => 'required|string|max:20',
            'organization_name' => 'required|string|max:255',
            'organization_address' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('channel_documents', 'public');
            $documentPath = asset('storage/' . $path);
        }

        $isChannel = $request->filled('channel_name');
        $role = $isChannel ? 'channel' : 'user';

        // If user is logged in
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            // Create new user if not authenticated
            $user = new User();
            $user->email = $request->email;
            $user->password = $request->password ? md5($request->password) : '';
        }

        // Common fields for both cases
        $user->name = $request->name ?? $user->name;
        $user->phone = $request->mobile;
        $user->role = $role;
        $user->join_date = now();
        $user->status = $isChannel ? 'pending' : 'approve';
        $user->save();

        // Create channel entry if needed
        if ($isChannel) {
            $channel = Channel::create([
                'channel_name' => $request->channel_name,
                'user_id' => $user->_id,
                'email' => $user->email,
                'mobile_number' => $request->mobile,
                'address' => $request->address ?? '',
                'organization_name' => $request->organization_name,
                'organization_address' => $request->organization_address,
                'status' => 'pending',
                'img' => 'https://multiplexplay.com/office/uploads/cuser_image/11.jpg',
                'document_path' => $documentPath,
                'join_date' => now(),
                'last_login' => now(),
            ]);

            $user->channel_id = (string) $channel->_id;
            $user->save();

            if (Auth::check()) {
                Auth::logout();
            }

            return redirect()->route('login')->with('success', 'Channel registration submitted successfully. Please wait for admin approval.');
        }

        // If new user (non-auth) then login after creation
        if (!Auth::check()) {
            Auth::login($user);
        }

        return redirect('/dashboard');
    }



    // Handle Logout Request
    public function logout()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to logout.');
        }

        Auth::logout();
        return redirect('/');
    }

    /**
     * Show register-details form for Google users
     */
    public function showRegisterDetailsForm()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.register-details');
    }

    /**
     * Store register-details form data
     */
    public function storeRegisterDetails(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isChannel = $request->filled('channel_name');

        if ($isChannel) {
            $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'channel_name' => 'required|string|max:255',
                'organization_name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'organization_address' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
            ]);

            $user->role = 'channel';
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
            ]);

            $user->role = 'user';
        }

        // Update user data
        $user->name = $request->name;
        $user->phone = $request->mobile;
        $user->join_date = now();
        $user->status = $isChannel ? 'pending' : 'approve';
        $user->save();

        if ($isChannel) {
            // Handle document upload
            $documentPath = '';
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $documentPath = $file->storeAs('documents', $filename, 'public');
            }

            // Create channel record
            Channel::create([
                'channel_name' => $request->channel_name,
                'user_id' => $user->_id,
                'email' => $user->email,
                'mobile_number' => $request->mobile,
                'address' => $request->address ?? '',
                'organization_name' => $request->organization_name,
                'organization_address' => $request->organization_address ?? '',
                'status' => 'pending',
                'img' => 'https://multiplexplay.com/office/uploads/cuser_image/11.jpg',
                'document_path' => $documentPath,
                'join_date' => now(),
                'last_login' => now(),
            ]);

            $user->channel_id = $user->_id; // Set channel_id same as user _id
            $user->save();

            return redirect()->route('login')->with('success', 'Channel registration submitted successfully. Please wait for admin approval.');
        }

        // Regular user - redirect to their dashboard
        return redirect()->intended('/front-movies');
    }

    // Custom Validation for Registration
    //    private function validateRegistration(Request $request)
    //    {
    //        $validator = Validator::make($request->all(), [
    //            'email' => 'required|email|unique:users,email',
    //            'password' => 'required|min:6|confirmed',
    //        ]);
    //
    //        $validator->validate();
    //    }

}
