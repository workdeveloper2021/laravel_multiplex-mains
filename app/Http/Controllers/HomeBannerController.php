<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use Illuminate\Http\Request;

class HomeBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('home_banner.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\http\Env\Request $request)
    {
        // Validate request data
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'url' => 'nullable|url',
            'order' => 'nullable|integer',
            'publication' => 'required|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('banners', 'public'); // stored at storage/app/public/banners
            $imageUrl = asset('storage/' . $imagePath); // generate public URL
        } else {
            return back()->with('error', 'Image upload failed.');
        }

        // Create and save HomeBanner document
        HomeBanner::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_url' => $imageUrl,
            'url' => $request->url,
            'order' => $request->order ?? 0,
            'publication' => $request->publication,
        ]);

        return back()->with('success', 'Banner added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HomeBanner $homeBanner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HomeBanner $homeBanner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HomeBanner $homeBanner)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomeBanner $homeBanner)
    {
        //
    }


}
