@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-play-circle me-2"></i>Episodes Management
                </h4>
                <small class="opacity-75">{{ $webseries->title }} / Season {{ $season->season_number }}</small>
            </div>
            <div>
                <a href="{{ route('content.seasons.episodes.create', $season->_id) }}" class="btn btn-light me-2">
                    <i class="fas fa-plus me-1"></i>Add New Episode
                </a>
                <a href="{{ route('content.webseries.seasons.index', $webseries->_id) }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Back to Seasons
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif



            @if($episodes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Episode #</th>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Video Status</th>
                                <th>Download</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($episodes as $episode)
                                <tr>
                                    <td>{{ $episode->episode_number }}</td>
                                    <td>{{ $episode->title }}</td>
                                    <td>
                                        @if($episode->duration)
                                            {{ $episode->duration }} min
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($episode->videoContent_id)
                                            <span class="badge bg-success">Uploaded</span>
                                        @else
                                            <span class="badge bg-warning">No Video</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($episode->enable_download == '1')
                                            <span class="badge bg-info">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td>{{ $episode->createdAt ? $episode->createdAt->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('content.episodes.edit', $episode->_id) }}"
                                               class="btn btn-sm btn-outline-primary">Edit</a>

                                            <form action="{{ route('content.episodes.destroy', $episode->_id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this episode?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">No episodes found for this season.</p>
                    <a href="{{ route('content.seasons.episodes.create', $season->_id) }}" class="btn btn-primary">
                        Add First Episode
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
