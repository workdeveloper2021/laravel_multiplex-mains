@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2>Create New Web Series Seasons</h2>

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

        <form action="{{ route('content.webseries.seasons.store', $webseries->_id) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Season Number</label>
                <input type="number" name="season_number" class="form-control" value="{{ $nextSeasonNumber }}" min="1" required readonly>
                <small class="text-muted">Auto-generated based on existing seasons</small>
            </div>

            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="Season {{ $nextSeasonNumber }}" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter season description (optional)"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Season</button>
                <a href="{{ route('content.webseries.seasons.index', $webseries->_id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
