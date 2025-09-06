@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h4>Add Country</h4>
        <form action="{{ route('countries.store') }}" method="POST">
            @csrf
            @include('country.countries')
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('countries.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
