@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Banner List</h2>
        <a href="{{ route('home-banner.create') }}" class="btn btn-primary mb-3">+ Add Banner</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>SLIDER ID</th>
                    <th>TITLE</th>
                    <th>IMAGE</th>
                    <th>TYPE</th>
                    <th>BUTTON</th>
                    <th>VIDEO</th>
                    <th>ORDER</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
            @foreach($banners as $banner)
                <tr>
                    <td>{{ $banner->id }}</td>
                    <td>{{ $banner->title }}</td>
                    <td><img src="{{ asset('uploads/banners/' . $banner->image) }}" width="100"></td>
                    <td>{{ $banner->type }}</td>
                    <td>{{ $banner->button ?? 'N/A' }}</td>
                    <td>{{ $banner->video ?? 'N/A' }}</td>
                    <td>{{ $banner->order }}</td>
                    <td>
                        <span class="badge bg-success">{{ $banner->status }}</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
