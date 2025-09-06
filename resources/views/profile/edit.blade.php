@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Update Profile</h2>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Common Fields -->
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Mobile</label>
            <input type="text" name="mobile" value="{{ old('mobile', $user->phone) }}" class="form-control" required>
        </div>

        @if($user->role === 'channel')
        <!-- Channel Specific Fields -->
        <div class="form-group">
            <label>Channel Name</label>
            <input type="text" name="channel_name" value="{{ old('channel_name', $channel->channel_name ?? '') }}" class="form-control">
        </div>

        <div class="form-group">
            <label>Organization Name</label>
            <input type="text" name="organization_name" value="{{ old('organization_name', $channel->organization_name ?? '') }}" class="form-control">
        </div>

        <div class="form-group">
            <label>Organization Address</label>
            <input type="text" name="organization_address" value="{{ old('organization_address', $channel->organization_address ?? '') }}" class="form-control">
        </div>

        <div class="form-group">
            <label>Update Document (optional)</label>
            <input type="file" name="document" class="form-control">
        </div>
        @endif

        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
