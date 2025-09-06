@extends('layouts.app')

@section('content')
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #ff5722 50%, #e0e0e0 50%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .form-control {
            height: 45px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .btn-orange {
            background-color: #ff5722;
            border: none;
            height: 45px;
        }

        .btn-orange:hover {
            background-color: #e64a19;
        }

        .logo {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #fff;
            letter-spacing: 2px;
        }

        .form-check-input {
            margin-right: 8px;
        }

        .hidden {
            display: none;
        }

        .file-upload {
            border: 2px dashed #ddd;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
            cursor: pointer;
            /* make it clear it's clickable */
            user-select: none;
        }

        .file-upload.dragover {
            border-color: #ff5722;
            background-color: #fff5f5;
        }
    </style>

    <div class="logo mt-5">MULTIPLEX PLAY</div>

    <div class="register-container">
        <div class="register-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0"><i class="fas fa-user-plus"></i> Complete Your Profile</h2>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.details.save') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                    @error('name')
                        <span class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobile Number *</label>
                    <input id="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror"
                        name="mobile" value="{{ old('mobile') }}" required>
                    @error('mobile')
                        <span class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_channel" name="is_channel"
                            {{ old('is_channel') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_channel">
                            Register as Channel Partner
                        </label>
                    </div>
                    <small class="text-muted">Check this if you want to upload and manage content</small>
                </div>

                <!-- Channel Fields (Hidden by default) -->
                <div id="channel_fields" class="hidden">
                    <div class="mb-3">
                        <label for="channel_name" class="form-label">Channel Name *</label>
                        <input id="channel_name" type="text"
                            class="form-control @error('channel_name') is-invalid @enderror" name="channel_name"
                            value="{{ old('channel_name') }}">
                        @error('channel_name')
                            <span class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="organization_name" class="form-label">Organization Name *</label>
                        <input id="organization_name" type="text"
                            class="form-control @error('organization_name') is-invalid @enderror" name="organization_name"
                            value="{{ old('organization_name') }}">
                        @error('organization_name')
                            <span class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="organization_address" class="form-label">Organization Address</label>
                        <textarea id="organization_address" class="form-control @error('organization_address') is-invalid @enderror"
                            name="organization_address" rows="3">{{ old('organization_address') }}</textarea>
                        @error('organization_address')
                            <span class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3" id="file_upload_field">
                        <label for="document" class="form-label">Upload Document (Optional)</label>
                        <div class="file-upload" id="file_drop_area" tabindex="0" role="button"
                            aria-label="Upload document">
                            <input type="file" id="document" name="document"
                                class="form-control @error('document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png"
                                hidden>
                            <div id="file_display">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-0">Drag and drop your document here or
                                    <a href="#" id="file_browse">browse files</a>
                                </p>
                                <small class="text-muted">Supported formats: PDF, JPG, PNG (Max 10MB)</small>
                            </div>
                        </div>
                        @error('document')
                            <span class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                </div>

                <button type="submit" class="btn btn-orange w-100 text-white">
                    Complete Registration
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isChannelCheckbox = document.getElementById('is_channel');
            const channelFields = document.getElementById('channel_fields');
            const channelNameField = document.getElementById('channel_name');
            const organizationNameField = document.getElementById('organization_name');

            const fileUploadField = document.getElementById('file_upload_field');
            const fileInput = document.getElementById('document');
            const fileDropArea = document.getElementById('file_drop_area');
            const fileBrowse = document.getElementById('file_browse');
            const fileDisplay = document.getElementById('file_display');

            const MAX_MB = 10;

            // --- Helpers ---
            function bytesToMB(bytes) {
                return (bytes / 1024 / 1024).toFixed(2);
            }

            function showFileInfo(file) {
                fileDisplay.innerHTML = `
                    <i class="fas fa-file fa-2x text-success mb-2"></i>
                    <p class="mb-0">${file.name}</p>
                    <small class="text-muted">${bytesToMB(file.size)} MB</small>
                `;
            }

            function validateSize(file) {
                if (file.size > MAX_MB * 1024 * 1024) {
                    fileInput.value = '';
                    fileDisplay.innerHTML = `
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                        <p class="mb-0">File too large. Max ${MAX_MB} MB allowed.</p>
                    `;
                    return false;
                }
                return true;
            }

            // --- Toggle channel fields ---
            function toggleChannelFields() {
                if (isChannelCheckbox.checked) {
                    channelFields.classList.remove('hidden');
                    channelNameField.required = true;
                    organizationNameField.required = true;
                } else {
                    channelFields.classList.add('hidden');
                    channelNameField.required = false;
                    organizationNameField.required = false;
                    channelNameField.value = '';
                    organizationNameField.value = '';
                    // reset file input when hiding
                    if (fileInput) fileInput.value = '';
                    if (fileDisplay) {
                        fileDisplay.innerHTML = `
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Drag and drop your document here or
                                <a href="#" id="file_browse">browse files</a>
                            </p>
                            <small class="text-muted">Supported formats: PDF, JPG, PNG (Max 10MB)</small>
                        `;
                        // re-bind browse link after reset
                        const newBrowse = fileDisplay.querySelector('#file_browse');
                        if (newBrowse) {
                            newBrowse.addEventListener('click', function(e) {
                                e.preventDefault();
                                fileInput.click();
                            });
                        }
                    }
                }
            }

            isChannelCheckbox.addEventListener('change', toggleChannelFields);

            // --- File upload: open dialog from link + entire area ---
            if (fileBrowse) {
                fileBrowse.addEventListener('click', function(e) {
                    e.preventDefault();
                    fileInput.click();
                });
            }

            if (fileDropArea) {
                fileDropArea.addEventListener('click', function(e) {
                    // avoid double trigger if user clicked the link inside
                    if (!e.target.closest('#file_browse')) {
                        fileInput.click();
                    }
                });

                // keyboard accessible
                fileDropArea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        fileInput.click();
                    }
                });
            }

            // --- On file choose ---
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        const file = this.files[0];
                        if (!validateSize(file)) return;
                        showFileInfo(file);
                    }
                });
            }

            // --- Drag and drop UX ---
            if (fileDropArea) {
                fileDropArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                fileDropArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                fileDropArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');

                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        const file = files[0];
                        if (!validateSize(file)) return;
                        // Assign dropped files to input for form submit
                        fileInput.files = files;
                        showFileInfo(file);
                    }
                });
            }

            // Initialize (respect old('is_channel'))
            toggleChannelFields();
        });
    </script>
@endsection
