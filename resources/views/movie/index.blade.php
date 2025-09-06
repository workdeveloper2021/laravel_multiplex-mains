@extends('layouts.app')

@section('content')
    @php
        $languageNames = [
            1 => 'English',
            2 => 'Hindi',
            3 => 'Bhojpuri',
            4 => 'Marathi',
            5 => 'Gujarati',
            6 => 'Bengali',
            7 => 'Punjabi',
            8 => 'Tamil',
            9 => 'Kannada',
            10 => 'Telugu',
            11 => 'Malayalam',
            12 => 'Assamese',
            13 => 'Rajasthani',
            14 => 'Chhattisgarhi'
        ];
    @endphp

    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-film me-2"></i>Movies Management
                </h4>
                <a href="{{ route('movies.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-1"></i>Add New Movie
                </a>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search movies by title...">
                        </div>
                    </div>
                    {{--  <div class="col-md-2">
                        <select id="languageFilter" class="form-select">
                            <option value="">All Languages</option>
                            @foreach($languageNames as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>  --}}
                    <div class="col-md-2">
                        <select id="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            <option value="paid">Paid</option>
                            <option value="free">Free</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>
                </div>

                @if($movies && count($movies) > 0)
                    <!-- Movies Count -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing <span id="currentCount">{{ count($movies) }}</span> of <span id="totalCount">{{ count($movies) }}</span> movies
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group" aria-label="View options">
                                <input type="radio" class="btn-check" name="viewOptions" id="tableViewRadio" autocomplete="off" checked>
                                <label class="btn btn-outline-primary btn-sm" for="tableViewRadio" id="tableView">
                                    <i class="fas fa-table"></i> Table
                                </label>

                                <input type="radio" class="btn-check" name="viewOptions" id="gridViewRadio" autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm" for="gridViewRadio" id="gridView">
                                    <i class="fas fa-th"></i> Grid
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Table View -->
                    <div class="table-responsive" id="tableViewContainer">
                        <table class="table table-hover align-middle" id="moviesTable">
                            <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                {{--  <th class="text-center">Poster</th>  --}}
                                <th class="text-center">Thumbnail</th>
                                <th>Title</th>
                                {{--  <th class="text-center">Genre</th>  --}}
                                {{--  <th class="text-center">Languages</th>  --}}
                                <th class="text-center">Release Date</th>
                                {{--  <th class="text-center">Countries</th>  --}}
                                <th class="text-center">Download</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody id="moviesTableBody">
                            @foreach ($movies as $index => $movie)
                                @php
                                    $title = $movie['title'] ?? 'Untitled';
                                    $languages = is_array($movie['language'] ?? null) ? array_filter($movie['language']) : [];
                                    $genres = is_array($movie['genre'] ?? null) ? array_filter($movie['genre']) : [];
                                    $countries = is_array($movie['country'] ?? null) ? array_filter($movie['country']) : [];
                                    $isPaid = $movie['is_paid'] ?? false;
                                    $enableDownload = $movie['enable_download'] ?? false;
                                    $publication = $movie['publication'] ?? false;
                                    $posterUrl = $movie['poster_url'] ?? '';
                                    $thumbnailUrl = $movie['thumbnail_url'] ?? '';
                                    $description = $movie['description'] ?? '';
                                    $trailerLink = $movie['trailer_link'] ?? '';
                                    $releaseDate = $movie['release'] ?? $movie['release_date'] ?? '';
                                @endphp

                                <tr class="movie-row"
                                    data-title="{{ strtolower(strip_tags($title)) }}"
                                    data-languages="{{ implode(',', $languages) }}"
                                    data-type="{{ $isPaid ? 'paid' : 'free' }}"
                                    data-status="{{ $publication ? 'published' : 'draft' }}"
                                    data-original-index="{{ $index }}">

                                    <td class="text-center fw-bold row-number">{{ $index + 1 }}</td>

                                    <!-- Poster -->
                                    {{--  <td class="text-center">
                                        @if(!empty($posterUrl))
                                            <img src="{{ $posterUrl }}" width="50" height="70" class="rounded shadow-sm"
                                                 data-bs-toggle="tooltip" title="Poster"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                            <div class="bg-light rounded align-items-center justify-content-center"
                                                 style="width: 50px; height: 70px; display: none;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 50px; height: 70px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>  --}}

                                    <!-- Thumbnail -->
                                    <td class="text-center">
                                        @if(!empty($thumbnailUrl))
                                            <img src="{{ $thumbnailUrl }}" width="70" height="50" class="rounded shadow-sm"
                                                 data-bs-toggle="tooltip" title="Thumbnail"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                            <div class="bg-light rounded align-items-center justify-content-center"
                                                 style="width: 70px; height: 50px; display: none;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 70px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Title -->
                                    <td>
                                        <div>
                                            <strong class="text-primary">{{ $title }}</strong>
                                            @if(!empty($trailerLink))
                                                <a href="{{ $trailerLink }}" target="_blank" class="ms-2"
                                                   data-bs-toggle="tooltip" title="Watch Trailer">
                                                    <i class="fab fa-youtube text-danger"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if(!empty($description))
                                            <small class="text-muted">{{ Str::limit(strip_tags($description), 50) }}</small>
                                        @endif
                                    </td>

                                    <!-- Genre -->
                                    {{--  <td class="text-center">
                                        @forelse($genres as $genre)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $genre }}</span>
                                        @empty
                                            <span class="text-muted small">No genre</span>
                                        @endforelse
                                    </td>  --}}

                                    <!-- Languages -->
                                    {{--  <td class="text-center">
                                        @forelse($languages as $languageId)
                                            @if(is_numeric($languageId) && isset($languageNames[(int)$languageId]))
                                                <span class="badge bg-info me-1 mb-1">{{ $languageNames[(int)$languageId] }}</span>
                                            @endif
                                        @empty
                                            <span class="text-muted small">No language</span>
                                        @endforelse
                                    </td>  --}}

                                    <!-- Release Date -->
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            @if(!empty($releaseDate))
                                                {{ $releaseDate }}
                                            @else

                                            @endif
                                        </span>
                                    </td>

                                    <!-- Countries -->
                                    {{--  <td class="text-center">
                                        @forelse($countries as $country)
                                            <span class="badge bg-primary me-1 mb-1">{{ $country }}</span>
                                        @empty
                                            <span class="text-muted small">No country</span>
                                        @endforelse
                                    </td>  --}}

                                    <!-- Download -->
                                    <td class="text-center">
                                        <span class="badge {{ $enableDownload ? 'bg-success' : 'bg-danger' }}">
                                            <i class="fas {{ $enableDownload ? 'fa-download' : 'fa-ban' }} me-1"></i>
                                            {{ $enableDownload ? 'Yes' : 'No' }}
                                        </span>
                                    </td>

                                    <!-- Type -->
                                    <td class="text-center">
                                        <span class="badge {{ $isPaid ? 'bg-warning text-dark' : 'bg-success' }}">
                                            <i class="fas {{ $isPaid ? 'fa-dollar-sign' : 'fa-gift' }} me-1"></i>
                                            {{ $isPaid ? 'Paid' : 'Free' }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="text-center">
                                        <span class="badge {{ $publication ? 'bg-success' : 'bg-warning text-dark' }}">
                                            <i class="fas {{ $publication ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>
                                            {{ $publication ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-center">
                                        <div class="d-flex" style="gap: 10px;">
                                            @if(isset($movie['id']) || isset($movie['_id']))
                                                <a href="{{ route('movies.edit', $movie['id'] ?? $movie['_id']) }}"
                                                class="btn btn-warning btn-sm"
                                                data-bs-toggle="tooltip" title="Edit Movie">
                                                    Edit <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="{{ route('movies.destroy', $movie['id'] ?? $movie['_id']) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this movie?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm"
                                                            data-bs-toggle="tooltip" title="Delete Movie">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">No actions</span>
                                            @endif
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Grid View -->
                    <div class="row" id="gridViewContainer" style="display: none;">
                        @foreach ($movies as $index => $movie)
                            @php
                                $title = $movie['title'] ?? 'Untitled';
                                $languages = is_array($movie['language'] ?? null) ? array_filter($movie['language']) : [];
                                $genres = is_array($movie['genre'] ?? null) ? array_filter($movie['genre']) : [];
                                $countries = is_array($movie['country'] ?? null) ? array_filter($movie['country']) : [];
                                $isPaid = $movie['is_paid'] ?? false;
                                $enableDownload = $movie['enable_download'] ?? false;
                                $publication = $movie['publication'] ?? false;
                                $posterUrl = $movie['poster_url'] ?? '';
                                $thumbnailUrl = $movie['thumbnail_url'] ?? '';
                                $description = $movie['description'] ?? '';
                                $trailerLink = $movie['trailer_link'] ?? '';
                                $releaseDate = $movie['release'] ?? $movie['release_date'] ?? '';
                            @endphp

                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 movie-card"
                                 data-title="{{ strtolower(strip_tags($title)) }}"
                                 data-languages="{{ implode(',', $languages) }}"
                                 data-type="{{ $isPaid ? 'paid' : 'free' }}"
                                 data-status="{{ $publication ? 'published' : 'draft' }}"
                                 data-original-index="{{ $index }}">

                                <div class="card h-100 shadow-sm">
                                    <!-- Movie Poster -->
                                    <div class="position-relative">
                                        @if(!empty($posterUrl))
                                            <img src="{{ $posterUrl }}" class="card-img-top" style="height: 300px; object-fit: cover;"
                                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4='" />
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                                                <i class="fas fa-film fa-3x text-muted"></i>
                                            </div>
                                        @endif

                                        <!-- Status Badges -->
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge {{ $publication ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $publication ? 'Published' : 'Draft' }}
                                            </span>
                                        </div>

                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge {{ $isPaid ? 'bg-warning text-dark' : 'bg-success' }}">
                                                {{ $isPaid ? 'Paid' : 'Free' }}
                                            </span>
                                        </div>

                                        @if(!empty($trailerLink))
                                            <div class="position-absolute bottom-0 end-0 m-2">
                                                <a href="{{ $trailerLink }}" target="_blank" class="btn btn-danger btn-sm">
                                                    <i class="fab fa-youtube"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-body">
                                        <h6 class="card-title text-primary fw-bold">{{ $title }}</h6>

                                        @if(!empty($description))
                                            <p class="card-text text-muted small">{{ Str::limit(strip_tags($description), 80) }}</p>
                                        @endif

                                        {{--  <!-- Genres -->
                                        @if(!empty($genres))
                                            <div class="mb-2">
                                                @foreach($genres as $genre)
                                                    <span class="badge bg-secondary me-1 mb-1 small">{{ $genre }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Languages -->
                                        @if(!empty($languages))
                                            <div class="mb-2">
                                                @foreach($languages as $languageId)
                                                    @if(is_numeric($languageId) && isset($languageNames[(int)$languageId]))
                                                        <span class="badge bg-info me-1 mb-1 small">{{ $languageNames[(int)$languageId] }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Release Date -->
                                        @if(!empty($releaseDate))
                                            <p class="card-text small text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                @try
                                                    {{ \Carbon\Carbon::parse($releaseDate)->format('d M Y') }}
                                                @catch(Exception $e)
                                                    {{ is_string($releaseDate) ? $releaseDate : 'N/A' }}
                                                @endtry
                                            </p>
                                        @endif
                                    </div>  --}}

                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Download Status -->
                                            <small class="text-{{ $enableDownload ? 'success' : 'danger' }}">
                                                <i class="fas {{ $enableDownload ? 'fa-download' : 'fa-ban' }}"></i>
                                                {{ $enableDownload ? 'Download' : 'No Download' }}
                                            </small>

                                            <!-- Action Buttons -->
                                            <div class="btn-group btn-group-sm">
                                                @if(isset($movie['id']) || isset($movie['_id']))
                                                    <a href="{{ route('movies.edit', $movie['id'] ?? $movie['_id']) }}"
                                                       class="btn btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger"
                                                            onclick="deleteMovie('{{ $movie['id'] ?? $movie['_id'] }}')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                                <option value="10">10 per page</option>
                                <option value="25" selected>25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>

                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination">
                                <!-- Pagination will be generated by JavaScript -->
                            </ul>
                        </nav>
                    </div>

                @else
                    <div class="text-center py-5">
                        <i class="fas fa-film fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No movies available</h5>
                        <p class="text-muted">Start by adding your first movie!</p>
                        <a href="{{ route('movies.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Movie
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize tooltips
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Pagination variables
        let currentPage = 1;
        let rowsPerPage = 25;
        let filteredRows = [];
        let filteredCards = [];
        let allRows = [];
        let allCards = [];
        let currentView = 'table';

        // Initialize
        function initializeTable() {
            allRows = $('.movie-row').toArray();
            allCards = $('.movie-card').toArray();
            filteredRows = [...allRows];
            filteredCards = [...allCards];
            updateDisplay();
        }

        // Get filtered items based on current filters
        function applyFilters() {
            const searchTerm = $('#searchInput').val().toLowerCase().trim();
            const languageFilter = $('#languageFilter').val();
            const typeFilter = $('#typeFilter').val();
            const statusFilter = $('#statusFilter').val();

            const filterFunction = (item) => {
                const $item = $(item);
                const title = ($item.data('title') || '').toString().toLowerCase();
                const languages = ($item.data('languages') || '').toString().split(',').filter(lang => lang.trim() !== '');
                const type = ($item.data('type') || '').toString();
                const status = ($item.data('status') || '').toString();

                let matches = true;

                // Search filter
                if (searchTerm && !title.includes(searchTerm)) {
                    matches = false;
                }

                // Language filter
                if (languageFilter && !languages.includes(languageFilter)) {
                    matches = false;
                }

                // Type filter
                if (typeFilter && type !== typeFilter) {
                    matches = false;
                }

                // Status filter
                if (statusFilter && status !== statusFilter) {
                    matches = false;
                }

                return matches;
            };

            filteredRows = allRows.filter(filterFunction);
            filteredCards = allCards.filter(filterFunction);

            currentPage = 1;
            updateDisplay();
        }

        // Update display and pagination
        function updateDisplay() {
            if (currentView === 'table') {
                updateTable();
            } else {
                updateGrid();
            }
            updatePagination();
            updateCount();
        }

        // Update table rows
        function updateTable() {
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;

            // Hide all rows
            $(allRows).hide();

            // Show filtered rows for current page
            const pageRows = filteredRows.slice(startIndex, endIndex);
            pageRows.forEach((row, index) => {
                $(row).show();
                // Update row number
                $(row).find('.row-number').text(startIndex + index + 1);
            });
        }

        // Update grid cards
        function updateGrid() {
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;

            // Hide all cards
            $(allCards).hide();

            // Show filtered cards for current page
            const pageCards = filteredCards.slice(startIndex, endIndex);
            pageCards.forEach((card) => {
                $(card).show();
            });
        }

        // Update pagination controls
        function updatePagination() {
            const totalItems = currentView === 'table' ? filteredRows.length : filteredCards.length;
            const totalPages = Math.ceil(totalItems / rowsPerPage);
            const $pagination = $('#pagination');

            $pagination.empty();

            if (totalPages <= 1) return;

            // Previous button
            $pagination.append(`
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                </li>
            `);

            // Page numbers
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                $pagination.append('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
                if (startPage > 2) {
                    $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                $pagination.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
                $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
            }

            // Next button
            $pagination.append(`
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                </li>
            `);
        }

        // Update count display
        function updateCount() {
            const currentCount = currentView === 'table' ? filteredRows.length : filteredCards.length;
            const totalCount = currentView === 'table' ? allRows.length : allCards.length;
            $('#currentCount').text(currentCount);
            $('#totalCount').text(totalCount);
        }

        // Event handlers
        $('#searchInput').on('input', debounce(applyFilters, 300));
        $('#languageFilter, #typeFilter, #statusFilter').on('change', applyFilters);

        $('#perPageSelect').on('change', function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updateDisplay();
        });

        $(document).on('click', '#pagination a.page-link', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (page && page !== currentPage && page > 0) {
                currentPage = page;
                updateDisplay();
                // Scroll to top of table
                $('#moviesTable')[0].scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#searchInput').val('');
            $('#languageFilter').val('');
            $('#typeFilter').val('');
            $('#statusFilter').val('');
            applyFilters();
        });

        // View toggle functionality
        $('#tableViewRadio, #gridViewRadio').on('change', function() {
            if ($('#tableViewRadio').is(':checked')) {
                currentView = 'table';
                $('#tableViewContainer').show();
                $('#gridViewContainer').hide();
            } else {
                currentView = 'grid';
                $('#tableViewContainer').hide();
                $('#gridViewContainer').show();
            }
            currentPage = 1;
            updateDisplay();
        });

        // Debounce function for search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Delete movie function for grid view
        window.deleteMovie = function(movieId) {
            if (confirm('Are you sure you want to delete this movie?')) {
                // Create a temporary form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/movies/${movieId}`;
                form.style.display = 'none';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        };

        // Initialize the table
        initializeTable();
    });
    </script>
@endsection
