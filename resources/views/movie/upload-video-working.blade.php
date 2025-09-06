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
        box-shadow: 0 4px 12px rgba(0,123,255,0.15);
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

    .optimization-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1rem;
        margin: 1rem 0;
    }

    .speed-indicator {
        background: #28a745;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        margin: 0.5rem 0;
    }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Optimization Info Banner -->
            <div class="optimization-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-rocket fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">🚀 Optimized Upload System</h5>
                        <p class="mb-0">
                            Fast direct upload to Cloudflare with progress tracking | 
                            Automatic fallback system | Large file support
                        </p>
                        <div class="speed-indicator mt-2">
                            ⚡ Supports up to 3GB files with real-time progress
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movie Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-film me-2"></i>Movie Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($movie->poster_image)
                                <img src="{{ asset('storage/' . $movie->poster_image) }}" 
                                     alt="{{ $movie->title }}" class="img-fluid rounded">
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
                                @if(!empty($movie->status))
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'uploading' => 'info',
                                            'processing' => 'info',
                                            'ready' => 'success',
                                            'approved' => 'success', 
                                            'rejected' => 'danger',
                                            'blocked' => 'secondary'
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
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Current Video Status -->
                    @if($movie->video_file || $movie->video_url)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            A video file is already uploaded for this movie. Uploading a new file will replace the existing one.
                            <br>
                            <strong>Current status:</strong> {{ ucfirst($movie->status ?? 'Pending') }}
                            @if($movie->video_url)
                                <br><strong>Stream URL:</strong> Available
                            @endif
                        </div>
                    @endif

                    <!-- Upload Form -->
                    <form action="{{ route('content.movies.store-video', $movie->_id) }}" method="POST" 
                          enctype="multipart/form-data" id="videoUploadForm">
                        @csrf
                        <input type="hidden" name="session_id" id="sessionId" value="{{ session()->getId() }}">
                        
                        <div class="upload-card text-center p-5 mb-4">
                            <div class="video-icon mb-3">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h5 class="mb-3">Select Video File</h5>
                            <p class="text-muted mb-4">
                                Supported formats: MP4, AVI, MKV, MOV, WMV, FLV
                                <br>
                                <strong>Maximum file size: 3GB</strong>
                                <br>
                                <small class="text-success">✅ Optimized upload with progress tracking</small>
                            </p>
                            
                            <input type="file" 
                                   class="form-control" 
                                   id="video_file" 
                                   name="video_file" 
                                   accept="video/mp4,video/avi,video/mkv,video/mov,video/wmv,video/flv"
                                   required>
                        </div>

                        <!-- Selected File Info -->
                        <div id="fileInfo" class="file-info" style="display: none;">
                            <h6><i class="fas fa-file-video me-2"></i>Selected File</h6>
                            <div id="fileName" class="fw-bold"></div>
                            <div id="fileSize" class="text-muted"></div>
                            <div id="fileType" class="text-muted"></div>
                            <div id="uploadStrategy" class="text-success"></div>
                        </div>

                        <!-- Upload Progress -->
                        <div class="upload-progress" id="uploadProgress">
                            <div class="d-flex justify-content-between mb-2">
                                <span id="uploadStage">Uploading...</span>
                                <span id="progressPercent">0%</span>
                            </div>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                     role="progressbar" id="progressBar" style="width: 0%"></div>
                            </div>
                            <div id="uploadStatus" class="text-muted small"></div>
                            <div class="row mt-2">
                                <div class="col-4">
                                    <small class="text-muted">Speed: <span id="uploadSpeed">0 MB/s</span></small>
                                </div>
                                <div class="col-4 text-center">
                                    <small class="text-muted">Stage: <span id="currentStage">Server</span></small>
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
                                <a href="{{ route('content.movies.edit', $movie->_id) }}" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-edit me-2"></i>Edit Movie Details
                                </a>
                                
                                <button type="submit" class="btn btn-success" id="uploadBtn" disabled>
                                    <i class="fas fa-upload me-2"></i>Start Upload
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
    const fileType = document.getElementById('fileType');
    const uploadStrategy = document.getElementById('uploadStrategy');
    const uploadBtn = document.getElementById('uploadBtn');
    const progressContainer = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const uploadStatus = document.getElementById('uploadStatus');
    const uploadSpeed = document.getElementById('uploadSpeed');
    const currentStage = document.getElementById('currentStage');
    const uploadETA = document.getElementById('uploadETA');
    const uploadForm = document.getElementById('videoUploadForm');

    let selectedFile = null;
    let uploadStartTime = null;

    // File selection handler
    fileInput.addEventListener('change', function(e) {
        selectedFile = e.target.files[0];
        if (selectedFile) {
            displayFileInfo(selectedFile);
            uploadBtn.disabled = false;
        } else {
            fileInfo.style.display = 'none';
            uploadBtn.disabled = true;
        }
    });

    function displayFileInfo(file) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        
        fileName.textContent = file.name;
        fileSize.textContent = `Size: ${formatFileSize(file.size)} (${fileSizeMB} MB)`;
        fileType.textContent = `Type: ${file.type}`;
        
        let strategy = '';
        if (file.size < 100 * 1024 * 1024) {
            strategy = `⚡ Small file: Fast upload expected`;
        } else if (file.size < 1024 * 1024 * 1024) {
            strategy = `🚀 Medium file: Optimized chunked upload`;
        } else {
            strategy = `💪 Large file: Two-stage upload process`;
        }
        
        uploadStrategy.textContent = strategy;
        fileInfo.style.display = 'block';

        // Validate file
        if (!isValidVideoFile(file)) {
            alert('Please select a valid video file (MP4, AVI, MKV, MOV, WMV, FLV)');
            fileInput.value = '';
            fileInfo.style.display = 'none';
            uploadBtn.disabled = true;
            return;
        }

        if (file.size > 3 * 1024 * 1024 * 1024) {
            alert('File size must be less than 3GB. Your file is: ' + formatFileSize(file.size));
            fileInput.value = '';
            fileInfo.style.display = 'none';
            uploadBtn.disabled = true;
            return;
        }
    }

    function isValidVideoFile(file) {
        const allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
        const allowedExtensions = ['.mp4', '.avi', '.mkv', '.mov', '.wmv', '.flv'];
        
        return allowedTypes.includes(file.type) || 
               allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
    }

    // Form submission with progress tracking
    uploadForm.addEventListener('submit', function(e) {
        if (!selectedFile) {
            e.preventDefault();
            alert('Please select a video file to upload');
            return;
        }

        // Show progress bar immediately
        progressContainer.style.display = 'block';
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        
        // Initialize progress
        progressBar.style.width = '5%';
        progressPercent.textContent = '5%';
        uploadStatus.textContent = 'Starting upload...';
        uploadStartTime = Date.now();
        
        console.log('🚀 Starting upload for file:', selectedFile.name);
        
        // Start progress polling immediately
        const sessionId = document.getElementById('sessionId').value;
        startProgressPolling(sessionId);
        
        // Let form submit normally
    });

    function startProgressPolling(sessionId) {
        console.log('📊 Starting progress polling for session:', sessionId);
        
        let pollInterval = 1000; // Start with 1 second
        let consecutiveErrors = 0;
        let lastProgress = 0;
        
        const progressTimer = setInterval(function() {
            fetch(`{{ route('content.movies.upload-progress') }}?session_id=${sessionId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Progress data:', data);
                    consecutiveErrors = 0;
                    
                    if (data.percent !== undefined) {
                        updateProgress(data);
                        lastProgress = data.percent;
                        
                        // Upload completed
                        if (data.status === 'completed' || data.percent >= 100) {
                            clearInterval(progressTimer);
                            handleUploadComplete();
                        }
                        
                        // Adjust polling based on progress
                        if (data.percent > 0 && data.percent < 90) {
                            pollInterval = 2000; // Slow down when actively uploading
                        }
                    }
                })
                .catch(error => {
                    console.error('Progress polling error:', error);
                    consecutiveErrors++;
                    
                    // Stop polling after too many errors
                    if (consecutiveErrors > 10) {
                        clearInterval(progressTimer);
                        console.log('Stopped progress polling due to errors');
                    } else {
                        // Increase interval on errors
                        pollInterval = Math.min(pollInterval * 1.2, 5000);
                    }
                });
        }, pollInterval);
        
        // Stop polling after 30 minutes
        setTimeout(() => {
            clearInterval(progressTimer);
            console.log('Progress polling timeout');
        }, 30 * 60 * 1000);
    }

    function updateProgress(data) {
        const percent = Math.max(5, Math.round(data.percent || 0));
        
        // Update progress bar
        progressBar.style.width = percent + '%';
        progressPercent.textContent = percent + '%';
        
        // Update stage based on progress
        if (data.stage === 'server' || percent <= 50) {
            currentStage.textContent = 'Server';
            progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
        } else if (data.stage === 'cloudflare' || percent > 50) {
            currentStage.textContent = 'Cloudflare';
            progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-success';
        }
        
        // Update status message
        if (data.message) {
            uploadStatus.textContent = data.message;
        } else {
            uploadStatus.textContent = `Uploading... ${percent}%`;
        }
        
        // Update speed if available
        if (data.speed) {
            uploadSpeed.textContent = formatSpeed(data.speed);
        } else if (uploadStartTime) {
            const elapsed = (Date.now() - uploadStartTime) / 1000;
            const estimatedSpeed = (data.uploaded || 0) / elapsed / (1024 * 1024);
            uploadSpeed.textContent = estimatedSpeed.toFixed(2) + ' MB/s';
        }
        
        // Update ETA
        if (data.eta) {
            uploadETA.textContent = formatTime(data.eta);
        } else if (data.uploaded && data.total && data.speed) {
            const remaining = data.total - data.uploaded;
            const eta = remaining / data.speed;
            uploadETA.textContent = formatTime(eta);
        }
    }

    function handleUploadComplete() {
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        progressBar.className = 'progress-bar bg-success';
        uploadStatus.textContent = '✅ Upload completed successfully!';
        currentStage.textContent = 'Complete';
        
        console.log('🎉 Upload completed successfully');
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = '{{ route("content.movies.index") }}';
        }, 3000);
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
        if (!seconds || seconds < 0) return '--';
        
        if (seconds < 60) {
            return Math.round(seconds) + 's';
        } else if (seconds < 3600) {
            return Math.round(seconds / 60) + 'm';
        } else {
            return Math.round(seconds / 3600) + 'h';
        }
    }

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
