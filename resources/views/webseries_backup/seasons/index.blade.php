@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Seasons List</h1>

        <a href="{{ route('seasons.create') }}" class="btn btn-primary mb-3">Add New Seasons</a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>

            @foreach($seasons as $s)
                <tr>
                    <td>{{ $s->title }}</td>
                    <td>{{ $s->description }}</td>
                    <td><img src="{{ asset($s->image_url) }}" width="100"></td>
                    <td>
                        <a href="{{ route('seasons.edit', $s->_id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('seasons.destroy', $s->_id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
