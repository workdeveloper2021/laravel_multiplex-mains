@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header"><h4>Your Profile</h4></div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('update.profile') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-4">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" class="form-control">
                    </div>

                    @if ($user->profile_image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $user->profile_image) }}" class="h-20 w-auto rounded" alt="Current Profile Image">
                        </div>
                    @endif

                    <div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="{{ route('show.profile') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
