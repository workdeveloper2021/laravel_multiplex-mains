@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Episode
                </h4>
                <small class="opacity-75">{{ $episode->title }}</small>
            </div>
            <a href="{{ route('content.seasons.episodes.index', $episode->seasonId) }}" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-1"></i>Back to Episodes
            </a>
        </div>
        <div class="card-body">

            <form id="episode-form" action="{{ route('content.episodes.update', $episode->_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="title" class="form-label">Episode Title *</label>
                    <input type="text" name="title" id="title" class="form-control"
                           value="{{ old('title', $episode->title) }}" required>
                    @error('title')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="episode_number" class="form-label">Episode Number *</label>
                    <input type="number" name="episode_number" id="episode_number" class="form-control"
                           value="{{ old('episode_number', $episode->episode_number) }}" min="1" required>
                    @error('episode_number')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="duration" class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration" id="duration" class="form-control"
                           value="{{ old('duration', $episode->duration) }}" min="1">
                    @error('duration')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Current Video</label>
                    @if($episode->videoContent_id)
                        <div class="alert alert-info">
                            <i class="fas fa-video me-2"></i>
                            Video is already uploaded and available.
                            @if($episode->video_url)
                                <br><small class="text-muted">Stream ID: {{ $episode->videoContent_id }}</small>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No video uploaded for this episode.
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="file" class="form-label">Replace Video File (Optional)</label>
                    <input type="file" name="file" id="file" class="form-control"
                           accept="video/mp4,video/avi,video/mov,video/wmv">
                    <small class="text-muted">
                        Leave empty to keep current video. Supported formats: MP4, AVI, MOV, WMV. Max size: 1GB
                    </small>
                    
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
                    
                    @error('file')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Channel Selection - Only for Admin Users --}}

                    {{-- Channel users automatically use their own channel --}}
                    <div class="mb-4">
                        <label for="channel_id" class="form-label">Channel</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }} (Your Channel)" disabled>
                        <input type="hidden" name="channel_id" value="{{ Auth::user()->_id }}">
                        <small class="text-muted">Your Channel ID: {{ Auth::user()->_id }}</small>
                    </div>

                <div class="mb-4">
                    <label for="enable_download" class="form-label">Enable Download</label>
                    <select name="enable_download" id="enable_download" class="form-control">
                        <option value="0" {{ old('enable_download', $episode->enable_download) == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('enable_download', $episode->enable_download) == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" id="submit-btn" class="btn btn-primary">
                        <span id="submit-text">Update Episode</span>
                        <span id="submit-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                    <a href="{{ route('content.seasons.episodes.index', $episode->seasonId) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // File upload progress tracking
    $('#file').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            $('#upload-status').html(`📎 Selected: ${file.name} (${fileSize} MB)`);
        }
    });

    // Form submission with progress
    $('#episode-form').on('submit', function(e) {
        const videoFile = $('#file')[0].files[0];
        
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
        submitSpinner.removeClass('d-none');
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
                uploadStatus.html('✅ Episode updated successfully!');
                submitText.text('Completed!');
                
                // Wait 1 second then redirect
                setTimeout(function() {
                    submitText.text('Redirecting...');
                    window.location.href = "{{ route('content.seasons.episodes.index', $episode->seasonId) }}";
                }, 1000);
            },
            error: function(xhr) {
                progressContainer.hide();
                submitBtn.prop('disabled', false);
                submitText.text('Update Episode');
                submitSpinner.addClass('d-none');
                
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

<style>
.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.progress-bar {
    transition: width 0.3s ease;
}

#upload-status {
    min-height: 20px;
}
</style>
@endsection
