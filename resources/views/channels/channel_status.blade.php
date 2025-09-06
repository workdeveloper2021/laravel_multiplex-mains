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
                    <td class="badge bg-success text-uppercase text-white">{{ $channel['status'] }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($channel['join_date'])->format('d-m-Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($channel['last_login'])->format('d-m-Y H:i') }}</td>
                    <td>
                        {{-- Optional: More actions like delete/edit --}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
