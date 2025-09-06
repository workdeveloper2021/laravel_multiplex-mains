@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2>Edit Season - {{ $webseries->title }}</h2>

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

        <form action="{{ route('content.seasons.update', $season->_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Season Number</label>
                <input type="number" name="season_number" class="form-control" value="{{ old('season_number', $season->season_number) }}" min="1" required readonly>
                <small class="text-muted">Season number cannot be changed</small>
            </div>

            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $season->title) }}" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter season description (optional)">{{ old('description', $season->description) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Season</button>
                <a href="{{ route('content.webseries.seasons.index', $webseries->_id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
