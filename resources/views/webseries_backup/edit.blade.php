@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2>Edit Web Series</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('webseries.update', $webseries->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ $webseries->title }}" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" required>{{ $webseries->description }}</textarea>
            </div>

            <div class="mb-3">
                <label>Current Image</label><br>
                <img src="{{ asset('storage/' . $webseries->image_url) }}" alt="Web Series Image" width="150">
            </div>

            <div class="mb-3">
                <label>Change Image (optional)</label>
                <input type="file" name="image_url" class="form-control">
            </div>

            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
@endsection
