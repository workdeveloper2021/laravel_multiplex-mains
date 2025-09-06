@extends('layouts.app')

@section('content')
<style>
    .upload-card {
        transition: all 0.3s ease;
        border: 2px dashed #007bff;
        background: #f8f9fa;
        cursor: pointer;
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
        font-size: 4rem;
        color: #007bff;
    }

    .progress-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .progress-bar {
        transition: width 0.3s ease;
    }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Movie Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-film me-2"></i>{{ $movie->title ?? 'Movie Upload' }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="text-muted mb-2"><strong>Status:</strong> 
                                <span class="badge bg-warning">{{ ucfirst($movie->status ?? 'Pending') }}</span>
                            </p>
                            @if($movie->video_url)
                                <p class="text-success mb-2"><i class="fas fa-check-circle"></i> Video already uploaded</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Video to Cloudflare</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Upload Form -->
                    <form action="{{ route('content.movies.store-video', $movie->_id) }}" method="POST" 
                          enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <input type="hidden" name="session_id" id="sessionId" value="{{ session()->getId() }}">
                        
                        <div class="upload-card text-center p-5 mb-4" onclick="document.getElementById('video_file').click()">
                            <div class="video-icon mb-3">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h5 class="mb-3">Click to Select Video File</h5>
                            <p class="text-muted mb-4">
                                <strong>Supported:</strong> MP4, AVI, MKV, MOV, WMV, FLV
                                <br>
                                <strong>Max Size:</strong> 3GB
                            </p>
                            
                            <input type="file" 
                                   class="form-control d-none" 
                                   id="video_file" 
                                   name="video_file" 
                                   accept="video/*"
                                   required>
                        </div>

                        <!-- File Info -->
                        <div id="fileInfo" class="file-info text-center" style="display: none;">
                            <h6><i class="fas fa-file-video me-2"></i>Selected File</h6>
                            <div id="fileName" class="fw-bold text-success"></div>
                            <div id="fileSize" class="text-muted"></div>
                        </div>

                        <!-- Progress -->
                        <div class="upload-progress" id="progressContainer">
                            <div class="progress-container">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-upload"></i> Uploading to Cloudflare</span>
                                    <span id="progressPercent" class="fw-bold">0%</span>
                                </div>
                                <div class="progress mb-3" style="height: 30px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                         id="progressBar" style="width: 0%"></div>
                                </div>
                                <div id="uploadStatus" class="text-center text-muted">Ready to upload...</div>
                                <div class="row mt-3">
                                    <div class="col-6 text-center">
                                        <small class="text-muted">Speed: <span id="uploadSpeed">--</span></small>
                                    </div>
                                    <div class="col-6 text-center">
                                        <small class="text-muted">ETA: <span id="uploadETA">--</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('content.movies.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            
                            <button type="submit" class="btn btn-success btn-lg" id="uploadBtn" disabled>
                                <i class="fas fa-rocket me-2"></i>Upload to Cloudflare
                            </button>
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
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadForm = document.getElementById('uploadForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const uploadStatus = document.getElementById('uploadStatus');
    const uploadSpeed = document.getElementById('uploadSpeed');
    const uploadETA = document.getElementById('uploadETA');

    let selectedFile = null;

    // File selection
    fileInput.addEventListener('change', function(e) {
        selectedFile = e.target.files[0];
        if (selectedFile) {
            // Validate file
            if (!isValidVideo(selectedFile)) {
                alert('Please select a valid video file');
                fileInput.value = '';
                return;
            }

            if (selectedFile.size > 3 * 1024 * 1024 * 1024) {
                alert('File too large! Maximum 3GB allowed');
                fileInput.value = '';
                return;
            }

            // Show file info
            fileName.textContent = selectedFile.name;
            fileSize.textContent = formatFileSize(selectedFile.size);
            fileInfo.style.display = 'block';
            uploadBtn.disabled = false;

            console.log('✅ File selected:', selectedFile.name, formatFileSize(selectedFile.size));
        } else {
            fileInfo.style.display = 'none';
            uploadBtn.disabled = true;
        }
    });

    // Form submission
    uploadForm.addEventListener('submit', function(e) {
        if (!selectedFile) {
            e.preventDefault();
            alert('Please select a video file');
            return;
        }

        // Show progress immediately
        progressContainer.style.display = 'block';
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        
        // Start progress tracking
        startProgressTracking();
        
        console.log('🚀 Starting upload:', selectedFile.name);
    });

    function startProgressTracking() {
        const sessionId = document.getElementById('sessionId').value;
        let progressTimer;
        
        // Update progress every 2 seconds
        progressTimer = setInterval(function() {
            fetch(`{{ route('content.movies.upload-progress') }}?session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    updateProgressDisplay(data);
                    
                    // Stop polling when complete
                    if (data.status === 'completed' || data.percent >= 100) {
                        clearInterval(progressTimer);
                        handleUploadComplete();
                    }
                })
                .catch(error => {
                    console.log('Progress check error:', error);
                });
        }, 2000);

        // Stop after 30 minutes
        setTimeout(() => clearInterval(progressTimer), 30 * 60 * 1000);
    }

    function updateProgressDisplay(data) {
        const percent = Math.round(data.percent || 0);
        
        progressBar.style.width = percent + '%';
        progressPercent.textContent = percent + '%';
        
        if (data.message) {
            uploadStatus.textContent = data.message;
        }

        // Update speed and ETA if available
        if (data.speed) {
            uploadSpeed.textContent = data.speed.toFixed(1) + ' MB/s';
        }

        if (data.eta) {
            uploadETA.textContent = formatTime(data.eta);
        }

        // Color coding based on progress
        if (percent < 30) {
            progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-info';
        } else if (percent < 70) {
            progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
        } else {
            progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-success';
        }

        console.log(`📊 Progress: ${percent}% - ${data.message || 'Uploading...'}`);
    }

    function handleUploadComplete() {
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        progressBar.className = 'progress-bar bg-success';
        uploadStatus.textContent = '🎉 Upload completed successfully!';
        
        console.log('✅ Upload completed!');
        
        // Redirect after 3 seconds
        setTimeout(() => {
            window.location.href = '{{ route("content.movies.index") }}';
        }, 3000);
    }

    function isValidVideo(file) {
        const validTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
        const validExtensions = ['.mp4', '.avi', '.mkv', '.mov', '.wmv', '.flv'];
        
        return validTypes.includes(file.type) || 
               validExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatTime(seconds) {
        if (seconds < 60) return Math.round(seconds) + 's';
        if (seconds < 3600) return Math.round(seconds / 60) + 'm';
        return Math.round(seconds / 3600) + 'h';
    }
});
</script>
@endsection
