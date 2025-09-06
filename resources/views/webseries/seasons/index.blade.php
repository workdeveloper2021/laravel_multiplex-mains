@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>Seasons Management
                    </h4>
                    <small class="opacity-75">Web Series: {{ $webseries->title ?? 'Unknown' }}</small>
                </div>
                <div>
                    <a href="{{ route('content.webseries.seasons.create', $webseries->_id) }}" class="btn btn-light me-2">
                        <i class="fas fa-plus me-1"></i>Add New Season
                    </a>
                    <a href="{{ route('content.webseries.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Back to Web Series
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($seasons && count($seasons) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Season Title</th>
                                <th class="text-center">Season Number</th>
                                <th class="text-center">Episodes</th>
                                <th class="text-center">Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($seasons as $index => $season)
                                <tr>
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong class="text-primary">{{ $season->title ?? 'Untitled Season' }}</strong>
                                            <br>
                                            <small class="text-muted">Web Series: {{ $webseries->title }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">Season {{ $season->season_number ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if(!empty($season->episodesId))
                                            <span class="badge bg-success">{{ count($season->episodesId) }} Episode(s)</span>
                                        @else
                                            <span class="badge bg-warning text-dark">No Episodes</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            {{ $season->createdAt ? \Carbon\Carbon::parse($season->createdAt)->format('d M Y') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Episodes button - main navigation as per your requirement -->
                                            <a href="{{ route('content.seasons.episodes.index', $season->_id) }}"
                                               class="btn btn-primary btn-sm"
                                               data-bs-toggle="tooltip" title="Manage Episodes">
                                                <i class="fas fa-play-circle"></i> Episodes
                                            </a>

                                            <!-- Edit season button -->
                                            <a href="{{ route('content.seasons.edit', $season->_id) }}"
                                               class="btn btn-warning btn-sm"
                                               data-bs-toggle="tooltip" title="Edit Season">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('content.seasons.destroy', $season->_id) }}" method="POST"
                                                  class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this season and all its episodes?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm"
                                                        data-bs-toggle="tooltip" title="Delete Season">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No seasons available</h5>
                        <p class="text-muted">Start by creating the first season for "{{ $webseries->title }}"!</p>
                        <a href="{{ route('content.webseries.seasons.create', $webseries->_id) }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>Create Season
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Initialize tooltips if Bootstrap is available
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof bootstrap !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
@endsection
