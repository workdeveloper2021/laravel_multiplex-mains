@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-tv me-2"></i>Web Series Management
                </h4>
                <a href="{{ route('content.webseries.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-1"></i>Add New Web Series
                </a>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($webseries && count($webseries) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th class="text-center">Genres</th>
                                <th class="text-center">Seasons</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($webseries as $index => $ws)
                                <tr>
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        @if($ws->image_url)
                                            <img src="{{ $ws->image_url }}" width="80" height="60" class="rounded shadow-sm"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                            <div class="bg-light rounded align-items-center justify-content-center" 
                                                 style="width: 80px; height: 60px; display: none;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 80px; height: 60px;">
                                                <i class="fas fa-tv text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-primary">{{ $ws->title ?? 'Untitled' }}</strong>
                                            @if(!empty($ws->trailer))
                                                <a href="{{ $ws->trailer }}" target="_blank" class="ms-2" 
                                                   data-bs-toggle="tooltip" title="Watch Trailer">
                                                    <i class="fab fa-youtube text-danger"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($ws->description))
                                            <small class="text-muted">{{ Str::limit(strip_tags($ws->description), 80) }}</small>
                                        @else
                                            <small class="text-muted">No description</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!empty($ws->genre))
                                            <span class="badge bg-secondary">{{ count($ws->genre) }} Genre(s)</span>
                                        @else
                                            <span class="text-muted small">No genres</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!empty($ws->seasonsId))
                                            <span class="badge bg-info">{{ count($ws->seasonsId) }} Season(s)</span>
                                        @else
                                            <span class="badge bg-warning text-dark">No Seasons</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Edit button redirects to seasons management as per your requirement -->
                                            <a href="{{ route('content.webseries.edit', $ws->_id) }}" 
                                               class="btn btn-primary btn-sm" 
                                               data-bs-toggle="tooltip" title="Manage Seasons">
                                                <i class="fas fa-layer-group"></i> Seasons
                                            </a>
                                            
                                            <!-- Actual webseries edit button -->
                                            <a href="{{ route('content.webseries.editWebseries', $ws->_id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               data-bs-toggle="tooltip" title="Edit Web Series">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('content.webseries.destroy', $ws->_id) }}" method="POST" 
                                                  class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this web series and all its seasons/episodes?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="tooltip" title="Delete Web Series">
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
                        <i class="fas fa-tv fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No web series available</h5>
                        <p class="text-muted">Start by creating your first web series!</p>
                        <a href="{{ route('content.webseries.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create Web Series
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
