<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     *
     * @return View|RedirectResponse
    */
    public function show()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login.');
        }

        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     *
     * @return View
    */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.editProfile', compact('user'));
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param Request $request
     * @return RedirectResponse
    */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = Auth::user();
        $user->name = $request->name;

        $channel = Channel::where('user_id', $user->id)->first();
        // dd($channel);
        if ($request->hasFile('profile_image')) {
            // Purani image delete kar do (agar hai)
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('public/profile_images', $imageName);

            // relative path user ke liye
            $imageUrl = 'profile_images/' . $imageName;
            $user->profile_image = $imageUrl;

            // channel ke liye full URL store karo
            if ($channel) {
                $channel->img = asset('storage/' . $imageUrl);
                $channel->save();
            }
        }

        $user->save();

        return redirect()->route('show.profile')->with('success', 'Profile updated successfully.');
    }

}
