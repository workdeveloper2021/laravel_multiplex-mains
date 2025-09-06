@extends('layouts.app')

@section('content')
    <div class="container mt-5 max-w-3xl mx-auto">
        <h1 class="text-center text-3xl font-semibold mb-6">Edit Genre</h1>

        <form action="{{ route('genre.update', $genre->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT') <!-- This is for PUT requests -->

            <!-- Genre Name -->
            <div class="form-group">
                <label for="name" class="block text-sm font-medium text-gray-700">Genre Name</label>
                <input type="text" class="form-control w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" id="name" name="name" value="{{ old('name', $genre->name) }}" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea class="form-control w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" id="description" name="description" rows="3" required>{{ old('description', $genre->description) }}</textarea>
            </div>

            <!-- Slug -->
            <div class="form-group">
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" class="form-control w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" id="slug" name="slug" value="{{ old('slug', $genre->slug) }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="featured">Featured</label>
                <select name="featured" class="form-control" required>
                    <option value="1" {{ old('featured', $genre->featured) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('featured', $genre->featured) == 0 ? 'selected' : '' }}>No</option>
                </select>
                @error('featured') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- URL -->
            <div class="form-group">
                <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                <input type="text" class="form-control w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" id="url" name="url" value="{{ old('url', $genre->url) }}">
            </div>

            <!-- Image URL Field (File Upload) -->
            <div class="form-group">
                <label for="image_url" class="block text-sm font-medium text-gray-700">Image URL (Optional)</label>
                <input type="file" class="form-control w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" id="image_url" name="image_url">

                <!-- Display Current Image Inline -->
                @if ($genre->image_url)
                    <div class="mt-3">
                        <p class="text-sm text-gray-700">Current Image:</p>
                         <img src="{{ $genre->image_url }}" alt="Current Genre Image" class="w-32 h-32 object-cover rounded-md">
                    </div>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="w-full py-3 px-6 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Update Genre</button>
            </div>
        </form>
    </div>
@endsection
