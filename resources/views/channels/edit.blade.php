@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Channel - {{ $channel->channel_name }}</h2>

        <form action="{{ route('channels.update', $channel->_id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="pending" {{ $channel->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approve" {{ $channel->status === 'approve' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $channel->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="block" {{ $channel->status === 'block' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Status</button>
            <a href="{{ route('channels.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
