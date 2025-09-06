<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Slider;
use App\Models\WebSeries;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Slider::orderBy('order')->get(); // fetch from MongoDB

        return view('slider.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $movies = Movie::all();
        $webseries = WebSeries::all();
        return view('slider.create', compact('movies', 'webseries'));
    }


    /**
     * Store a newly created resource in storage.
     */
        public function store(Request $request)
    {
        $validated = $request->validate([
            'slider_id' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'image_link' => 'nullable|image',
            'action_type' => 'nullable|string',
            'action_btn_text' => 'nullable|string|max:255',
            'videos_id' => 'required|string',
            'order' => 'nullable|integer',
            'publication' => 'required|boolean',
        ]);

        if ($request->hasFile('image_link') && $request->file('image_link')->isValid()) {
            $file = $request->file('image_link');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('banners', $filename, 'public');
            $validated['image_link'] = asset('storage/' . $path);
        }

        Slider::create($validated);

        return redirect()->route('banner.index')->with('success', 'Banner created successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $slider = Slider::findOrFail($id);
        return view('slider.show', compact('slider'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $slider = Slider::findOrFail($id);
        return view('slider.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $slider = Slider::findOrFail($id);

        $validated = $request->validate([
            'slider_id' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'image_link' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'action_type' => 'nullable|string',
            'action_btn_text' => 'nullable|string|max:255',
            'video_link' => 'nullable|url',
            'order' => 'required|integer',
            'publication' => 'required|boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('image_link')) {
            // Delete old image if exists
            if ($slider->image_link && file_exists(public_path($slider->image_link))) {
                unlink(public_path($slider->image_link));
            }

            $file = $request->file('image_link');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('banners', $filename, 'public');
            $validated['image_link'] = '/storage/' . $path;
        }

        $slider->update($validated);

        return redirect()->route('banner.index')->with('success', 'Banner updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);

        // Delete image file if exists
        if ($slider->image_link && file_exists(public_path($slider->image_link))) {
            unlink(public_path($slider->image_link));
        }

        $slider->delete();

        return redirect()->route('banner.index')->with('success', 'Banner deleted successfully!');
    }
}
