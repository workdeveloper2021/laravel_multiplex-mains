x
@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Channel List</h2>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Channel Name</th>
                <th>User</th>
                <th>Mobile</th>
                <th>Address</th>
                <th>Organization</th>
                <th>Status</th>
                <th>Join Date</th>
                <th>Last Login</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($channels as $channel)
                <tr>
                    <td>{{ $channel['channel_name'] }}</td>
                    <td>{{ $channel['user'] }}</td>
                    <td>{{ $channel['mobile_number'] }}</td>
                    <td>{{ $channel['address'] }}</td>
                    <td>{{ $channel['organization_name'] }}</td>
                    <td>
                        <form action="{{ route('channels.update', $channel['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="pending" {{ $channel['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approve" {{ $channel['status'] === 'approve' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $channel['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="block" {{ $channel['status'] === 'block' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </form>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($channel['join_date'])->format('d-m-Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($channel['last_login'])->format('d-m-Y H:i') }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Edit Button -->
                            <a href="{{ route('channels.edit', $channel['id']) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('channels.destroy', $channel['id']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this channel?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
