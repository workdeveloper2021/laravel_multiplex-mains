@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Banner Details</h2>
            <div class="flex space-x-3">
                <a href="{{ route('banner.edit', $slider->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition">
                    ✏️ Edit
                </a>
                <a href="{{ route('banner.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition">
                    ← Back to List
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ $slider->title }}</h3>
                <p class="text-sm text-gray-500">Slider ID: {{ $slider->slider_id }}</p>
            </div>

            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Image Section -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Banner Image</h4>
                        @if($slider->image_link)
                            <img src="{{ $slider->image_link }}" class="w-full h-48 object-cover rounded-lg" alt="Banner image">
                        @else
                            <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                <span class="text-gray-500">No image available</span>
                            </div>
                        @endif
                    </div>

                    <!-- Details Section -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Action Type</h4>
                            <p class="text-sm text-gray-600">{{ $slider->action_type ?: 'None' }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Button Text</h4>
                            <p class="text-sm text-gray-600">{{ $slider->action_btn_text ?: 'N/A' }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Video Link</h4>
                            @if($slider->video_link)
                                <a href="{{ $slider->video_link }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                    {{ $slider->video_link }}
                                </a>
                            @else
                                <p class="text-sm text-gray-600">N/A</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Display Order</h4>
                            <p class="text-sm text-gray-600">{{ $slider->order }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Status</h4>
                            @if($slider->publication)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                    Unpublished
                                </span>
                            @endif
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Created</h4>
                            <p class="text-sm text-gray-600">{{ $slider->created_at ? $slider->created_at->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Last Updated</h4>
                            <p class="text-sm text-gray-600">{{ $slider->updated_at ? $slider->updated_at->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <form action="{{ route('banner.destroy', $slider->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this banner?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition">
                            🗑️ Delete Banner
                        </button>
                    </form>
                    
                    <a href="{{ route('banner.edit', $slider->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        ✏️ Edit Banner
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
