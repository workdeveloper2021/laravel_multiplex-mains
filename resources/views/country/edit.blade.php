@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h4>Edit Country</h4>
        <form action="{{ route('countries.update', $country->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('country.countries', ['country' => $country])
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('countries.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
