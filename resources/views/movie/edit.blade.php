@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h4>Edit Movie</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('movies.update', $movie->id) }}" method="POST" enctype="multipart/form-data" id="movie-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title', $movie->title) }}" hidden>
                    </div>

                    {{--  <div class="mb-4">
                        <label for="genre">Genre</label>
                        <select name="genre" class="form-control" required>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}" {{ $movie->genre_id == $genre->id ? 'selected' : '' }}>
                                    {{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>  --}}

                    {{--  <div class="mb-4">
                        <label for="language">Languages</label>
                        <select id="language-select" name="language[]" class="form-control select2" multiple="multiple" required>
                            <option value="all">All</option>
                            @foreach($languages as $language)
                                <option value="{{ $language['_id'] }}"
                                    {{ in_array($language['_id'], $movie->languages ?? []) ? 'selected' : '' }}>
                                    {{ $language['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="country">Countries</label>
                        <select id="country-select" name="country[]" class="form-control select2" multiple="multiple" required>
                            <option value="all">All</option>
                            @foreach($countries as $country)
                                <option value="{{ $country['id'] }}"
                                    {{ in_array($country['id'], $movie->countries ?? []) ? 'selected' : '' }}>
                                    {{ $country['country'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="channel_id">Channel</label>
                        <select name="channel_id" class="form-control" required>
                            @foreach($channels as $channel)
                                <option value="{{ $channel['id'] }}" {{ $movie->channel_id == $channel['id'] ? 'selected' : '' }}>
                                    {{ $channel['channel_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>  --}}

                    {{--  <div class="mb-4">
                        <label for="release">Release Date</label>
                        <input type="date" name="release" class="form-control" value="{{ old('release', $movie->release) }}">
                    </div>  --}}

                    {{--  <div class="mb-4">
                        <label for="price">Price</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $movie->price) }}">
                    </div>  --}}

                    {{--  <div class="mb-4">
                        <label for="is_paid">Is Paid</label>
                        <select name="is_paid" class="form-control">
                            <option value="1" {{ $movie->is_paid == '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ $movie->is_paid == '0' ? 'selected' : '' }}>Free</option>
                        </select>
                    </div>  --}}

                    {{--  <div class="mb-4">
                        <label for="publication">Publication</label>
                        <select name="publication" class="form-control">
                            <option value="1" {{ $movie->publication == '1' ? 'selected' : '' }}>Publish</option>
                            <option value="0" {{ $movie->publication == '0' ? 'selected' : '' }}>Unpublish</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="trailer_link">Trailer Link (YouTube)</label>
                        <input type="url" name="trailer_link" class="form-control" value="{{ old('trailer_link', $movie->trailer_link) }}">
                    </div>  --}}

                    {{--  <div class="mb-4">
                        <label for="thumbnail_image">🖼️ Thumbnail Image</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        @if($movie->thumbnail)
                            <img src="{{ asset('storage/' . $movie->thumbnail) }}" class="img-fluid mt-2" style="max-height: 120px;">
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="poster_image">🎞️ Poster Image</label>
                        <input type="file" name="poster" class="form-control" accept="image/*">
                        @if($movie->poster)
                            <img src="{{ asset('storage/' . $movie->poster) }}" class="img-fluid mt-2" style="max-height: 120px;">
                        @endif
                    </div>  --}}

                    <div class="mb-4">
                        <label for="file">Upload Video File (Optional)</label>
                        <input type="file" name="file" id="video-file" class="form-control" accept="video/*">

                        <!-- Progress Bar -->
                        <div class="progress mt-3" id="upload-progress" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar"
                                 id="progress-bar"
                                 style="width: 0%"
                                 aria-valuenow="0"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>

                        <!-- Upload Status -->
                        <div class="mt-2">
                            <small id="upload-status" class="text-muted"></small>
                        </div>
                    </div>

                    {{--  <div class="mb-4">
                        <label for="enable_download">Enable Download</label>
                        <select name="enable_download" class="form-control">
                            <option value="1" {{ $movie->enable_download == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ $movie->enable_download == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>  --}}

                    <button type="submit" class="btn btn-success" id="submit-btn">
                        <span id="submit-text">Update Movie</span>
                        <span id="submit-spinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;"></span>
                    </button>
                    <a href="{{ route('movies.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "Select options",
                allowClear: true,
                closeOnSelect: false
            });

            $('#language-select, #country-select').on('change', function () {
                const allValue = 'all';
                const selected = $(this).val();

                if (selected.includes(allValue)) {
                    $(this).find('option').prop('selected', true);
                    $(this).find('option[value="all"]').prop('selected', false);
                    $(this).trigger('change');
                }
            });

            // File upload progress tracking
            $('#video-file').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    $('#upload-status').html(`📎 Selected: ${file.name} (${fileSize} MB)`);
                }
            });

            // Form submission with progress
            $('#movie-form').on('submit', function(e) {
                const videoFile = $('#video-file')[0].files[0];

                // Only show progress if video file is selected
                if (!videoFile) {
                    return; // Let normal form submission happen
                }

                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = $('#submit-btn');
                const submitText = $('#submit-text');
                const submitSpinner = $('#submit-spinner');
                const progressContainer = $('#upload-progress');
                const progressBar = $('#progress-bar');
                const progressText = $('#progress-text');
                const uploadStatus = $('#upload-status');

                // Show progress and disable button
                progressContainer.show();
                submitBtn.prop('disabled', true);
                submitText.text('Uploading...');
                submitSpinner.show();
                uploadStatus.html('📤 Uploading video file...');

                // Upload with progress
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();

                        // Upload progress
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                // Show real upload progress (10%, 20%, 30%, etc.)
                                const percentComplete = Math.round((evt.loaded / evt.total) * 100);

                                progressBar.css('width', percentComplete + '%');
                                progressBar.attr('aria-valuenow', percentComplete);
                                progressText.text(percentComplete + '%');

                                const uploadedMB = (evt.loaded / 1024 / 1024).toFixed(2);
                                const totalMB = (evt.total / 1024 / 1024).toFixed(2);

                                uploadStatus.html(`📤 Uploading: ${uploadedMB} / ${totalMB} MB (${percentComplete}%)`);
                            }
                        }, false);

                        // When upload completes, show processing status
                        xhr.upload.addEventListener('load', function() {
                            uploadStatus.html(`⏳ Processing video on Cloudflare... Please wait`);
                            submitText.text('Processing...');
                        }, false);

                        return xhr;
                    },
                    success: function(response) {
                    // Animate to 100% when server responds
                    progressBar.css('width', '100%');
                    progressBar.attr('aria-valuenow', 100);
                    progressText.text('100%');
                    uploadStatus.html('✅ Upload completed successfully!');
                    submitText.text('Completed!');

                    // Wait 1 second then redirect
                        setTimeout(function() {
                            submitText.text('Redirecting...');
                            window.location.href = "{{ route('movies.index') }}";
                        }, 1000);
                    },
                    error: function(xhr) {
                        progressContainer.hide();
                        submitBtn.prop('disabled', false);
                        submitText.text('Update Movie');
                        submitSpinner.hide();

                        let errorMessage = 'Upload failed. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        uploadStatus.html(`❌ ${errorMessage}`);
                        uploadStatus.addClass('text-danger');

                        // Show error alert
                        $('<div class="alert alert-danger alert-dismissible fade show mt-3">' +
                          '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                          '<strong>Upload Error:</strong> ' + errorMessage +
                          '</div>').prependTo('.card-body');
                    }
                });
            });
        });
    </script>
@endsection
