@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Channel Details</h2>
        <table class="table">
            <tr><th>Channel Name</th><td>{{ $channel->channel_name }}</td></tr>
            <tr><th>User</th><td>{{ $channel->user?->name ?? 'Unknown' }}</td></tr>
            <tr><th>Mobile Number</th><td>{{ $channel->mobile_number }}</td></tr>
            <tr><th>Address</th><td>{{ $channel->address }}</td></tr>
            <tr><th>Organization</th><td>{{ $channel->organization_name }}</td></tr>
            <tr><th>Status</th><td>{{ ucfirst($channel->status) }}</td></tr>
            <tr><th>Join Date</th><td>{{ \Carbon\Carbon::parse($channel->join_date)->format('d-m-Y H:i') }}</td></tr>
            <tr><th>Last Login</th><td>{{ \Carbon\Carbon::parse($channel->last_login)->format('d-m-Y H:i') }}</td></tr>
        </table>
        <a href="{{ route('channels.index') }}" class="btn btn-primary">Back to List</a>
    </div>
@endsection
