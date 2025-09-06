@extends('layouts.app')

@section('content')
    <style>
        .upload-card {
            transition: all 0.3s ease;
            border: 2px dashed #007bff;
            background: #f8f9fa;
        }

        .upload-card:hover {
            border-color: #0056b3;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
            background: #e7f1ff;
        }

        .upload-progress {
            display: none;
            margin-top: 1rem;
        }

        .file-info {
            background: #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .video-icon {
            font-size: 3rem;
            color: #007bff;
        }
    </style>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Movie Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-film me-2"></i>Movie Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                @if ($movie->poster_image)
                                    <img src="{{ asset('storage/' . $movie->poster_image) }}" alt="{{ $movie->title }}"
                                        class="img-fluid rounded">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                        style="height: 200px;">
                                        <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h3 class="text-primary">{{ $movie->title ?? 'Untitled Movie' }}</h3>
                                <p class="text-muted mb-2"><strong>Description:</strong>
                                    {{ !empty($movie->description) ? $movie->description : 'No description provided' }}
                                </p>
                                <p class="text-muted mb-2"><strong>Genre:</strong>
                                    {{ $movie->genre_names ?? 'Not specified' }}
                                </p>
                                <p class="text-muted mb-2"><strong>Language:</strong>
                                    {{ $movie->language_names ?? 'Not specified' }}
                                </p>
                                <p class="text-muted mb-2"><strong>Country:</strong>
                                    {{ $movie->country_names ?? 'Not specified' }}
                                </p>
                                <p class="text-muted mb-2"><strong>Status:</strong>
                                    @if (!empty($movie->status))
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'blocked' => 'secondary',
                                            ];
                                            $statusColor = $statusColors[$movie->status] ?? 'primary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($movie->status) }}</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video Upload Card -->
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-video me-2"></i>Upload Video File</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Current Video Status -->
                        @if ($movie->video_file)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                A video file is already uploaded for this movie. Uploading a new file will replace the
                                existing one.
                                <br>
                                <strong>Current file:</strong> {{ basename($movie->video_file) }}
                            </div>
                        @endif

                        <!-- Upload Form -->
                        <form action="{{ route('content.movies.store-video', $movie->_id) }}" method="POST"
                            enctype="multipart/form-data" id="videoUploadForm">
                            @csrf
                            <input type="hidden" name="session_id" value="{{ session()->getId() }}">

                            <div class="upload-card text-center p-5 mb-4">
                                <div class="video-icon mb-3">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h5 class="mb-3">Upload Video File</h5>
                                <p class="text-muted mb-4">
                                    Select a video file to upload. Supported formats: MP4, AVI, MKV, MOV, WMV, FLV
                                    <br>
                                    <strong>Maximum file size: 2GB (optimized for fast uploads)</strong>
                                </p>

                                <input type="file" class="form-control" id="video_file" name="video_file"
                                    accept="video/mp4,video/avi,video/mkv,video/mov,video/wmv,video/flv" required>
                            </div>

                            <!-- Selected File Info -->
                            <div id="fileInfo" class="file-info" style="display: none;">
                                <h6><i class="fas fa-file-video me-2"></i>Selected File</h6>
                                <div id="fileName" class="fw-bold"></div>
                                <div id="fileSize" class="text-muted"></div>
                                <div id="fileDuration" class="text-muted"></div>
                            </div>

                            <!-- Upload Progress -->
                            <div class="upload-progress">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Fast Upload to Cloudflare...</span>
                                    <span id="progressPercent">0%</span>
                                </div>
                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        id="progressBar" style="width: 0%"></div>
                                </div>
                                <div id="uploadStatus" class="text-muted small"></div>
                                <div class="row mt-2">
                                    <div class="col-4">
                                        <small class="text-muted">Speed: <span id="uploadSpeed">0 MB/s</span></small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <small class="text-muted">Chunk: <span id="currentChunk">1</span></small>
                                    </div>
                                    <div class="col-4 text-end">
                                        <small class="text-muted">ETA: <span id="uploadETA">--</span></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('content.movies.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Movies
                                </a>

                                <div>
                                    <a href="{{ route('content.movies.edit', $movie->_id) }}"
                                        class="btn btn-outline-primary me-2">
                                        <i class="fas fa-edit me-2"></i>Edit Movie Details
                                    </a>

                                    <button type="submit" class="btn btn-success" id="uploadBtn">
                                        <i class="fas fa-upload me-2"></i>Upload Video
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('video_file');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const uploadForm = document.getElementById('videoUploadForm');
            const uploadBtn = document.getElementById('uploadBtn');
            const progressContainer = document.querySelector('.upload-progress');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            const uploadStatus = document.getElementById('uploadStatus');

            // File selection handler
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Show file info
                    fileName.textContent = file.name;
                    fileSize.textContent = `Size: ${formatFileSize(file.size)}`;
                    fileInfo.style.display = 'block';

                    // Validate file type
                    const allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
                    if (!allowedTypes.includes(file.type) && !file.name.match(
                            /\.(mp4|avi|mkv|mov|wmv|flv)$/i)) {
                        alert('Please select a valid video file (MP4, AVI, MKV, MOV, WMV, FLV)');
                        fileInput.value = '';
                        fileInfo.style.display = 'none';
                        return;
                    }

                    // Validate file size (2GB with buffer)
                    if (file.size > 2.5 * 1024 * 1024 * 1024) {
                        alert('File size must be less than 2GB. Your file is: ' + formatFileSize(file
                        .size));
                        fileInput.value = '';
                        fileInfo.style.display = 'none';
                        return;
                    }
                } else {
                    fileInfo.style.display = 'none';
                }
            });

            // Form submission with AJAX and progress tracking
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent normal form submission
                
                const file = fileInput.files[0];
                if (!file) {
                    alert('Please select a video file to upload');
                    return;
                }

                // Show progress bar
                progressContainer.style.display = 'block';
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading to Cloudflare...';

                // Immediately show initial progress
                progressBar.style.width = '1%';
                progressPercent.textContent = '1%';
                uploadStatus.textContent = 'Starting upload to Cloudflare...';

                // Start AJAX upload
                const sessionId = '{{ session()->getId() }}';
                startUpload(file, sessionId);
            });

            async function startUpload(file, sessionId) {
                try {
                    const formData = new FormData();
                    formData.append('video_file', file);
                    formData.append('session_id', sessionId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    // Start progress polling
                    startProgressPolling(sessionId);

                    // Upload video via AJAX
                    const response = await fetch('{{ route("content.movies.store-video", $movie->_id) }}', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.error || 'Upload failed');
                    }

                } catch (error) {
                    alert('Upload failed: ' + error.message);
                    resetUploadForm();
                }
            }

            function startProgressPolling(sessionId) {
                console.log('Starting progress tracking for session:', sessionId);

                const progressTimer = setInterval(() => {
                    fetch(`{{ route('content.movies.upload-progress') }}?session_id=${sessionId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Progress data received:', data);
                        updateProgressBar(data);

                        // Check if completed
                        if (data.status === 'completed' && data.percent >= 100) {
                            clearInterval(progressTimer);
                            
                            if (data.stage === 'processing') {
                                uploadBtn.innerHTML = '<i class="fas fa-cog fa-spin me-2"></i>Processing Video...';
                                uploadStatus.textContent = 'Video uploaded successfully! Processing...';
                            } else {
                                uploadBtn.innerHTML = '<i class="fas fa-check me-2"></i>Upload Complete!';
                                uploadStatus.textContent = 'Video upload and processing completed!';
                                
                                setTimeout(() => {
                                    window.location.href = '{{ route('content.movies.index') }}';
                                }, 2000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Progress polling error:', error);
                    });
                }, 1000); // Poll every 1 second

                // Stop polling after 30 minutes
                setTimeout(() => {
                    clearInterval(progressTimer);
                    console.log('Progress polling stopped after timeout');
                }, 30 * 60 * 1000);
            }

            function updateProgressBar(data) {
                console.log('Updating progress bar with data:', data);
                
                const percent = Math.round(data.percent || 0);
                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent + '%';

                // Show status based on stage
                if (data.stage === 'processing' && data.status === 'completed') {
                    uploadStatus.textContent = 'Upload completed! Processing video...';
                } else if (data.message) {
                    uploadStatus.textContent = data.message;
                } else if (data.uploaded && data.total) {
                    uploadStatus.textContent =
                        `Uploaded ${formatFileSize(data.uploaded)} of ${formatFileSize(data.total)}`;
                } else {
                    uploadStatus.textContent = `Fast uploading... ${percent}%`;
                }

                // Update speed, ETA and chunk info if available
                if (data.speed && data.speed > 0) {
                    document.getElementById('uploadSpeed').textContent = formatSpeed(data.speed);
                } else if (data.status === 'completed') {
                    document.getElementById('uploadSpeed').textContent = 'Complete';
                }

                if (data.eta && data.eta > 0) {
                    document.getElementById('uploadETA').textContent = formatTime(data.eta);
                } else if (data.status === 'completed') {
                    document.getElementById('uploadETA').textContent = 'Done';
                }

                if (data.current_chunk) {
                    document.getElementById('currentChunk').textContent = data.current_chunk;
                }

                // Update progress bar color based on completion
                if (percent >= 100) {
                    progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                    progressBar.classList.add('bg-success');
                }
            }

            function formatSpeed(bytesPerSecond) {
                if (bytesPerSecond >= 1024 * 1024 * 1024) {
                    return (bytesPerSecond / (1024 * 1024 * 1024)).toFixed(2) + ' GB/s';
                } else if (bytesPerSecond >= 1024 * 1024) {
                    return (bytesPerSecond / (1024 * 1024)).toFixed(2) + ' MB/s';
                } else if (bytesPerSecond >= 1024) {
                    return (bytesPerSecond / 1024).toFixed(2) + ' KB/s';
                } else {
                    return bytesPerSecond + ' B/s';
                }
            }

            function formatTime(seconds) {
                if (seconds < 60) {
                    return Math.round(seconds) + 's';
                } else if (seconds < 3600) {
                    return Math.round(seconds / 60) + 'm';
                } else {
                    return Math.round(seconds / 3600) + 'h';
                }
            }

            function resetUploadForm() {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Video';
                progressContainer.style.display = 'none';
                progressBar.style.width = '0%';
                progressPercent.textContent = '0%';
                uploadStatus.textContent = '';
            }

            // File size formatter
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
    </script>
@endsection
