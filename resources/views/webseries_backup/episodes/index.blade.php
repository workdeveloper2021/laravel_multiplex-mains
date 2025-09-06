@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Webseries List</h1>

        <a href="{{ route('webseries.create') }}" class="btn btn-primary mb-3">Add New Webseries</a>

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

            @foreach($webseries as $ws)
                <tr>
                    <td>{{ $ws->title }}</td>
                    <td>{{ $ws->description }}</td>
                    <td><img src="{{ asset($ws->image_url) }}" width="100"></td>
                    <td>
                        <a href="{{ route('webseries.edit', $ws->_id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('webseries.destroy', $ws->_id) }}" method="POST" style="display:inline;">
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
