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

                <div class="flex justify-between items-center mb-6">
                    <a href="{{ route('edit.profile') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition">
                        Edit Profile
                    </a>
                </div>

                <div class="mb-4">
                    <label>Name<span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" readonly>
                </div>
                @if ($user->profile_image)
                    <label for="profile_image">Profile Image</label>
                    <img src="{{ asset('storage/' . $user->profile_image) }}" class="h-20 w-auto rounded" alt="profile_image">
                @else
                    <div class="mb-4">
                        <label><strong>Please upload profile image, using click on edit profile button.</strong></label>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
