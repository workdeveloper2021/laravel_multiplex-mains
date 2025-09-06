@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Add New Banner</h2>
        <form action="{{ route('home-banner.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="image" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="tvseries" required>
            </div>

            <div class="mb-3">
                <label>Button Text</label>
                <input type="text" name="button" class="form-control" value="Play">
            </div>

            <div class="mb-3">
                <label>Video</label>
                <input type="text" name="video" class="form-control">
            </div>

            <div class="mb-3">
                <label>Order</label>
                <input type="number" name="order" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Published">Published</option>
                    <option value="Draft">Draft</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Create Banner</button>
        </form>
    </div>
@endsection
