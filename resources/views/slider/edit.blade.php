@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Edit Banner</h2>
            <a href="{{ route('banner.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition">
                ← Back to List
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('banner.update', $slider->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="slider_id" class="block text-sm font-medium text-gray-700">Slider ID</label>
                <input type="text" name="slider_id" id="slider_id" value="{{ old('slider_id', $slider->slider_id) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $slider->title) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div class="mb-4">
                <label for="image_link" class="block text-sm font-medium text-gray-700">Upload Image</label>
                <input type="file" name="image_link" id="image_link" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @if($slider->image_link)
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Current Image:</p>
                        <img src="{{ $slider->image_link }}" class="h-20 w-auto rounded mt-1" alt="Current banner image">
                    </div>
                @endif
                <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
            </div>

            <div class="mb-4">
                <label for="action_type" class="block text-sm font-medium text-gray-700">Action Type</label>
                <select name="action_type" id="action_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="none" {{ old('action_type', $slider->action_type) == 'none' ? 'selected' : '' }}>None</option>
                    <option value="link" {{ old('action_type', $slider->action_type) == 'link' ? 'selected' : '' }}>Link</option>
                    <option value="video" {{ old('action_type', $slider->action_type) == 'video' ? 'selected' : '' }}>Video</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="action_btn_text" class="block text-sm font-medium text-gray-700">Button Text</label>
                <input type="text" name="action_btn_text" id="action_btn_text" value="{{ old('action_btn_text', $slider->action_btn_text) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="mb-4">
                <label for="video_link" class="block text-sm font-medium text-gray-700">Video Link (Optional)</label>
                <input type="url" name="video_link" id="video_link" value="{{ old('video_link', $slider->video_link) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="https://example.com/video.mp4">
            </div>

            <div class="mb-4">
                <label for="order" class="block text-sm font-medium text-gray-700">Display Order</label>
                <input type="number" name="order" id="order" value="{{ old('order', $slider->order) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div class="mb-4">
                <label for="publication" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="publication" id="publication" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="1" {{ old('publication', $slider->publication) == '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ old('publication', $slider->publication) == '0' ? 'selected' : '' }}>Unpublished</option>
                </select>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('banner.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                    💾 Update Banner
                </button>
            </div>
        </form>
    </div>
@endsection
