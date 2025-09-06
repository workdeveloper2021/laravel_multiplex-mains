@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h4>Countries</h4>
            <a href="{{ route('countries.create') }}" class="btn btn-primary">Add Country</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered text-center">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Country</th>
                <th>Currency</th>
                <th>Symbol</th>
                <th>ISO Code</th>
                <th>Exchange Rate</th>
                <th>Status</th>
                <th>Default</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($countries as $key => $country)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $country->country }}</td>
                    <td>{{ $country->currency }}</td>
                    <td>{{ $country->symbol }}</td>
                    <td>{{ $country->iso_code }}</td>
                    <td>{{ $country->exchange_rate }}</td>
                    <td>{{ $country->status ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $country->default ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('countries.edit', $country->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('countries.destroy', $country->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No countries available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
