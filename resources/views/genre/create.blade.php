@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Create New Genre</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('genre.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="name">Genre Name</label>
                        <input type="text" name="name" class="form-control" required>
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Description (optional)</label>
                        <textarea name="description" class="form-control"></textarea>
                        @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" class="form-control" required>
                        @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="publication">Publication</label>
                        <select name="publication" class="form-control" required>
                            <option value="1">Published</option>
                            <option value="0">Unpublished</option>
                        </select>
                        @error('publication') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="featured">Featured</label>
                        <select name="featured" class="form-control" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        @error('featured') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Upload Genre Image</label>
                        <input type="file" name="image" class="form-control" id="imageInput" required onchange="previewImage(event)">
                        @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Image preview section -->
                    <div class="form-group mb-3" id="imagePreviewContainer" style="display: none;">
                        <label for="imagePreview">Image Preview</label>
                        <img id="imagePreview" src="#" alt="Image Preview" class="img-fluid" style="max-width: 200px;">
                    </div>

                    <button type="submit" class="btn btn-primary">Create Genre</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function previewImage(event) {
                var reader = new FileReader();
                var imagePreviewContainer = document.getElementById('imagePreviewContainer');
                var imagePreview = document.getElementById('imagePreview');

                reader.onload = function() {
                    imagePreviewContainer.style.display = 'block'; // Show the preview section
                    imagePreview.src = reader.result; // Set the preview image to the selected file
                }

                reader.readAsDataURL(event.target.files[0]); // Read the selected file as a DataURL
            }
        </script>
    @endpush
@endsection
