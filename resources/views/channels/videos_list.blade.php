@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $title }}</h2>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Video Title</th>
                <th>Channel Name</th>
                <th>Status</th>
                <th>Uploaded At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($videos as $video)
                <tr>
                    <td>{{ $video['title'] }}</td>
                    <td>{{ $video['channel_name'] }}</td>
                    <td>
                        <form action="{{ route('channels.allVideos', $video['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="pending" {{ $video['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approve" {{ $video['status'] === 'approve' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $video['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="block" {{ $video['status'] === 'block' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </form>
                    </td>
                    <td>{{ $video['uploaded_at'] }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Edit -->
                            {{-- <a href="{{ route('videos.edit', $video['id']) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a> --}}

                            <!-- Delete -->
                            {{-- <form action="{{ route('videos.destroy', $video['id']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form> --}}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No videos found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
