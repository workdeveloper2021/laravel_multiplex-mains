<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Services\NodeApiService;
use Yajra\DataTables\Facades\DataTables;


class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Determine the page and limit (size)
            $limit = $request->get('length', 10); // Number of records per page (default 10)
            $page = $request->get('start', 0) / $limit + 1; // The current page, calculated from start index

            // Fetch paginated genres from MongoDB
            $genres = Genre::skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format the data for DataTables
            $formattedGenres = $genres->map(function ($genre, $key) {
                return [
                    'DT_RowIndex' => $key + 1,
                    'name' => $genre->name,
                    'description' => $genre->description,
                    'url' => "https://multiplexplay.com/office/genre/{$genre->slug}/action.html", // URL logic
                    'image_url' => $genre->image_url, // Fetch image URL from DB
                    'action' => '
                    <a href="' . route('genre.edit', $genre->id) . '" class="btn btn-warning btn-sm">Edit</a>
                    <form action="' . route('genre.destroy', $genre->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                ', // Add the Edit and Delete buttons
                ];
            });

            // Return the response with pagination data
            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => Genre::count(), // Total records in DB (no pagination)
                'recordsFiltered' => Genre::count(), // You can adjust this based on search filters if needed
                'data' => $formattedGenres,
            ]);
        }


        return view('genre.index');
    }







    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('genre.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:genres,slug',
            'publication' => 'required|boolean',
            'featured' => 'required|boolean',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif', // Validate image file
            'url' => 'nullable|string',
        ]);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Generate a unique filename for the image
            $imageName = time() . '_' . $image->getClientOriginalName();

            // Store the image in the 'public/genres' directory (this will create a path under 'storage/app/public/genres')
            $imagePath = $image->storeAs('public/genres', $imageName);

            // Generate the public URL for the image
            $imageUrl = asset('storage/genres/' . $imageName);
        } else {
            // If no image is provided, you can set a default image URL
            $imageUrl = 'https://multiplexplay.com/office/uploads/default_image/genre.png';
        }

        // Create a new genre instance
        $genre = Genre::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => $request->slug,
            'publication' => $request->publication,
            'featured' => $request->featured,
            'image_url' => $imageUrl, // Store the image URL (generated URL or default)
            'url' => $request->url,
        ]);

        // Prepare the response
        $response = [
            'url' => $genre->url,
            'image_url' => $imageUrl, // Image URL from the database
            '_id' => (string) $genre->id, // Assuming you are using an auto-incremented ID, MongoDB would have a _id field
            'genre_id' => $genre->id, // Use the genre's id
            'name' => $genre->name,
            'description' => $genre->description,
            'slug' => $genre->slug,
            'publication' => $genre->publication,
            'featured' => $genre->featured,
        ];

        // Return the response
        return view('genre.index', compact('response'));
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $genre = Genre::findOrFail($id);
        return view('genre.edit', compact('genre'));
    }

    // Handle the update logic
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'slug' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'image_url' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048', // File validation
            'featured' => 'required|in:0,1'
        ]);

        // Find the genre to update
        $genre = Genre::findOrFail($id);

        // Check if an image was uploaded
        if ($request->hasFile('image_url')) {
            // Store the image in the 'public/genres' directory
            $imagePath = $request->file('image_url')->store('genres', 'public');

            // Delete the old image if it exists
            if ($genre->image_url) {
                Storage::disk('public')->delete($genre->image_url);
            }

            // Update image path
            $genre->image_url = $imagePath;
        }

        // Update other fields
        $genre->name = $request->input('name');
        $genre->description = $request->input('description');
        $genre->slug = $request->input('slug');
        $genre->featured = $request->input('featured');
        $genre->url = $request->input('url');

        // Save the genre
        $genre->save();

        // Redirect back with success message
        return redirect()->route('genre.index')->with('success', 'Genre updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $genre = Genre::findOrFail($id);
        $genre->delete();

        return redirect()->route('genre.index')->with('success', 'Genre deleted successfully!');
    }

}
