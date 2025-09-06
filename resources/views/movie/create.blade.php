@extends('layouts.app')

@section('content')
<style>
    /* Custom styles for image upload sections */
    .image-upload-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .image-upload-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0,123,255,0.1);
    }

    .ratio {
        position: relative;
        width: 100%;
    }

    .ratio::before {
        display: block;
        padding-top: var(--bs-aspect-ratio);
        content: "";
    }

    .ratio > * {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .ratio-16x9 {
        --bs-aspect-ratio: calc(9 / 16 * 100%);
    }

    /* Custom poster ratio */
    .poster-ratio {
        position: relative;
        width: 100%;
    }

    .poster-ratio::before {
        display: block;
        padding-top: 150%; /* 3/2 * 100% = 150% */
        content: "";
    }

    .poster-ratio > * {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    /* Image preview enhancements */
    .image-preview-container {
        transition: transform 0.2s ease;
    }

    .image-preview-container:hover {
        transform: scale(1.02);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .thumbnail-preview-container,
        .poster-preview-container {
            max-width: 250px !important;
        }

        .card-body {
            padding: 1rem;
        }
    }

    /* File input styling */
    .form-control[type="file"] {
        border: 2px dashed #dee2e6;
        padding: 10px;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .form-control[type="file"]:hover {
        border-color: #007bff;
        background-color: #e3f2fd;
    }

    .form-control[type="file"]:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    /* Alert styling */
    .alert-info {
        background-color: #e3f2fd;
        border-color: #bbdefb;
        color: #1565c0;
    }
</style>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header"><h4>Add New Movie</h4></div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('movies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label>Title<span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                    </div>

                    {{--  <div class="mb-4">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter movie description...">{{ old('description') }}</textarea>
                    </div>  --}}

                    <div class="mb-4">
                        <label>Genre<span class="text-danger">*</span></label>
                        <select id="genre-select" name="genre[]" class="form-control select2" multiple required>
                            <option value="all">All</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}"
                                    {{ collect(old('genre'))->contains($genre->id) ? 'selected' : '' }}>
                                    {{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Countries<span class="text-danger">*</span></label>
                        <select id="country-select" name="country[]" class="form-control select2" multiple required>
                            <option value="all">All Countries</option>
                            @foreach($countries as $country)
                                @php
                                    $countryName = isset($country['iso_code'])
                                        ? $country['country']." [".$country['iso_code']."]"
                                        : $country['country'];
                                @endphp
                                <option
                                    value="{{ $country['id'] }}"
                                    data-currency="{{ $country['symbol'] ?? '' }}"
                                    data-iso="{{ $country['iso_code'] ?? '' }}"
                                    {{ collect(old('country'))->contains($country['id']) ? 'selected' : '' }}>
                                    {{ $countryName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Languages<span class="text-danger">*</span></label>
                        <select id="language-select" name="language[]" class="form-control select2" multiple required>
                            <option value="all">All Languages</option>
                            <option value="1" {{ collect(old('language'))->contains('1') ? 'selected' : '' }}>English</option>
                            <option value="2" {{ collect(old('language'))->contains('2') ? 'selected' : '' }}>Hindi</option>
                            <option value="3" {{ collect(old('language'))->contains('3') ? 'selected' : '' }}>Bhojpuri</option>
                            <option value="4" {{ collect(old('language'))->contains('4') ? 'selected' : '' }}>Marathi</option>
                            <option value="5" {{ collect(old('language'))->contains('5') ? 'selected' : '' }}>Gujarati</option>
                            <option value="6" {{ collect(old('language'))->contains('6') ? 'selected' : '' }}>Bengali</option>
                            <option value="7" {{ collect(old('language'))->contains('7') ? 'selected' : '' }}>Punjabi</option>
                            <option value="8" {{ collect(old('language'))->contains('8') ? 'selected' : '' }}>Tamil</option>
                            <option value="9" {{ collect(old('language'))->contains('9') ? 'selected' : '' }}>Kannada</option>
                            <option value="10" {{ collect(old('language'))->contains('10') ? 'selected' : '' }}>Telugu</option>
                            <option value="11" {{ collect(old('language'))->contains('11') ? 'selected' : '' }}>Malayalam</option>
                            <option value="12" {{ collect(old('language'))->contains('12') ? 'selected' : '' }}>Assamese</option>
                            <option value="13" {{ collect(old('language'))->contains('13') ? 'selected' : '' }}>Rajasthani</option>
                            <option value="14" {{ collect(old('language'))->contains('14') ? 'selected' : '' }}>Chhattisgarhi</option>
                        </select>
                    </div>

                    {{-- Dynamic Price Section - Only for Channel Users --}}
                    @if(Auth::user()->role === 'channel')
                        <div class="mb-4">
                            <label>Price (per country or common)<span class="text-danger">*</span></label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-info" onclick="testPriceFields()">Test Price Fields</button>
                                <small class="text-muted">Click to test price field generation</small>
                            </div>
                            <div id="price-container" class="border rounded p-3" style="min-height: 60px; background-color: #f8f9fa;">
                                <small class="text-muted">Please select countries above to set prices for each country.</small>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="price" value="100">
                    @endif

                    <div class="mb-4">
                        <label>Release Date</label>
                        <input type="date" name="release" class="form-control" value="{{ old('release', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-4">
                        <label>Is Paid</label>
                        <select name="is_paid" class="form-control">
                            <option value="1" {{ old('is_paid') == '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ old('is_paid') == '0' ? 'selected' : '' }}>Free</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Publication</label>
                        <select name="publication" class="form-control">
                            <option value="1" {{ old('publication') == '1' ? 'selected' : '' }}>Publish</option>
                            <option value="0" {{ old('publication') == '0' ? 'selected' : '' }}>Unpublish</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Trailer Link (YouTube)</label>
                        <input type="url" name="trailer_link" class="form-control" value="{{ old('trailer_link') }}">
                    </div>

                    {{-- Rich Text Description --}}
                    <div class="mb-4">
                        <label>Description <span class="text-danger">*</span></label>

                        {{-- Toolbar --}}
                        <div class="border rounded p-2 mb-2" style="background:#f8f9fa;">
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('bold')"><b>B</b></button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('italic')"><i>I</i></button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('underline')"><u>U</u></button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('insertUnorderedList')">• List</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('insertOrderedList')">1. List</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('justifyLeft')">Left</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('justifyCenter')">Center</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('justifyRight')">Right</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('createLink', prompt('Enter link URL:'))">🔗</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="execCmd('unlink')">❌ Link</button>
                        </div>

                        {{-- Editable Area --}}
                        <div id="description-editor"
                            contenteditable="true"
                            class="form-control"
                            style="height:200px; overflow:auto;">{!! old('description') !!}</div>

                        {{-- Hidden Input to store value --}}
                        <input type="hidden" name="description" id="description-input">
                    </div>

                    <div class="row mb-4">
                        <!-- Thumbnail Section (16:9 Aspect Ratio) -->
                      <!-- Poster Section (ab Thumbnail ka size use karega) -->
<div class="col-lg-6 mb-4">
    <div class="card h-100 image-upload-card">
        <div class="card-header bg-light">
            <h6 class="mb-0">🎞️ Poster Image (Vertical)</h6>
            <small class="text-muted">Recommended: 1200x1800px (2:3 ratio)</small>
        </div>
        <div class="card-body text-center">
            <!-- Poster Preview Container (thumbnail ka 300px size use) -->
            <div class="poster-preview-container image-preview-container mb-3" style="max-width: 300px; margin: 0 auto;">
                <div class="ratio ratio-16x9 border rounded overflow-hidden bg-light">
                    <img id="poster_preview"
                         src="https://via.placeholder.com/320x180/f8f9fa/6c757d?text=Poster+Preview"
                         alt="Poster Preview"
                         class="img-fluid"
                         style="object-fit: cover; width: 100%; height: 100%;" />
                </div>
            </div>

            <!-- File Input -->
            <div class="mb-3">
                <input type="file"
                       id="poster-input"
                       name="poster"
                       class="form-control"
                       accept="image/*"
                       style="max-width: 300px; margin: 0 auto;">
            </div>

            <!-- Guidelines -->
            <div class="alert alert-info py-2" role="alert">
                <small>
                    <strong>Guidelines:</strong><br>
                    • Aspect ratio: 2:3 (portrait)<br>
                    • Format: JPG, PNG<br>
                    • Max size: 5MB
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Thumbnail Section (ab Poster ka size use karega) -->
<div class="col-lg-6 mb-4">
    <div class="card h-100 image-upload-card">
        <div class="card-header bg-light">
            <h6 class="mb-0">🖼️ Thumbnail Image (Horizontal)</h6>
            <small class="text-muted">Recommended: 1920x1080px (16:9 ratio)</small>
        </div>
        <div class="card-body text-center">
            <!-- Thumbnail Preview Container (poster ka 200px size use) -->
            <div class="thumbnail-preview-container image-preview-container mb-3" style="max-width: 200px; margin: 0 auto;">
                <div class="poster-ratio border rounded overflow-hidden bg-light" style="aspect-ratio: 2/3;">
                    <img id="thumbnail_preview"
                         src="https://via.placeholder.com/200x300/f8f9fa/6c757d?text=Thumbnail+Preview"
                         alt="Thumbnail Preview"
                         class="img-fluid"
                         style="object-fit: cover; width: 100%; height: 100%;" />
                </div>
            </div>

            <!-- File Input -->
            <div class="mb-3">
                <input type="file"
                       id="thumbnail-input"
                       name="thumbnail"
                       class="form-control"
                       accept="image/*"
                       style="max-width: 200px; margin: 0 auto;">
            </div>

            <!-- Guidelines -->
            <div class="alert alert-info py-2" role="alert">
                <small>
                    <strong>Guidelines:</strong><br>
                    • Aspect ratio: 16:9 (landscape)<br>
                    • Format: JPG, PNG<br>
                    • Max size: 5MB
                </small>
            </div>
        </div>
    </div>
</div>

                    </div>

                    {{--  <div class="mb-4">
                        <label>Video File</label>
                        <input type="file" name="file" class="form-control" accept="video/*">
                    </div>  --}}

                    <div class="mb-4">
                        <label>Enable Download</label>
                        <select name="enable_download" class="form-control">
                            <option value="1" {{ old('enable_download') == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ old('enable_download') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Movie</button>
                </form>
            </div>
        </div>
    </div>

    {{-- jQuery and Select2 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    {{-- Rich Text Editor Functions --}}
    <script>
        // Rich text editor commands
        function execCmd(command, value = null) {
            document.execCommand(command, false, value);
            // Update hidden input
            document.getElementById('description-input').value = document.getElementById('description-editor').innerHTML;
        }

        // Update hidden input on content change
        document.getElementById('description-editor').addEventListener('input', function() {
            document.getElementById('description-input').value = this.innerHTML;
        });

        // Test function for debugging price fields
        function testPriceFields() {
            console.log('Testing price fields...');
            const selectedCountries = $('#country-select').val() || [];
            console.log('Currently selected countries:', selectedCountries);

            if (selectedCountries.length === 0) {
                alert('Please select some countries first');
                return;
            }

            updatePriceFields(selectedCountries, selectedCountries.includes('all'));
        }

        $(document).ready(function () {
            // Replace 'all' in dropdowns with all actual IDs
            $('form').on('submit', function () {
                // Update description hidden input before submit
                $('#description-input').val($('#description-editor').html());
                // Handle country selection
                const $countrySelect = $('#country-select');
                const selectedCountries = $countrySelect.val();
                if (selectedCountries && selectedCountries.includes('all')) {
                    const allCountryIDs = [];
                    $countrySelect.find('option').each(function () {
                        const val = $(this).val();
                        if (val !== 'all') allCountryIDs.push(val);
                    });
                    $countrySelect.val(allCountryIDs).trigger('change.select2');
                }

                // Handle language selection
                const $languageSelect = $('#language-select');
                const selectedLanguages = $languageSelect.val();
                if (selectedLanguages && selectedLanguages.includes('all')) {
                    const allLanguageIDs = [];
                    $languageSelect.find('option').each(function () {
                        const val = $(this).val();
                        if (val !== 'all') allLanguageIDs.push(val);
                    });
                    $languageSelect.val(allLanguageIDs).trigger('change.select2');
                }

                // Handle genre selection
                const $genreSelect = $('#genre-select');
                const selectedGenres = $genreSelect.val();
                if (selectedGenres && selectedGenres.includes('all')) {
                    const allGenreIDs = [];
                    $genreSelect.find('option').each(function () {
                        const val = $(this).val();
                        if (val !== 'all') allGenreIDs.push(val);
                    });
                    $genreSelect.val(allGenreIDs).trigger('change.select2');
                }
            });

            // Initialize Select2 with delay to ensure DOM is ready
            setTimeout(function() {
                $('.select2').select2({
                    placeholder: "Select options",
                    closeOnSelect: false,
                    width: '100%'
                });

                // Trigger country change event after initialization for channel users
                @if(Auth::user()->role === 'channel')
                    $('#country-select').trigger('change');
                @endif
            }, 100);

            function handleSelectAll(selectId) {
                const $select = $(`#${selectId}`);
                $select.on('change', function () {
                    let selected = $(this).val() || [];
                    if (selected.includes('all')) {
                        let allValues = [];
                        $select.find('option').each(function () {
                            const val = $(this).val();
                            if (val !== 'all') allValues.push(val);
                        });
                        $select.val(allValues).trigger('change.select2');
                        setTimeout(() => $select.select2('close'), 100);
                        if (selectId === 'country-select' && '{{ Auth::user()->role }}' === 'channel') {
                            updatePriceFields(allValues, true);
                        }
                    } else {
                        if (selectId === 'country-select' && '{{ Auth::user()->role }}' === 'channel') {
                            updatePriceFields(selected, false);
                        }
                    }
                });
            }
            handleSelectAll('genre-select');
            handleSelectAll('country-select');
            handleSelectAll('language-select');

            function updatePriceFields(selectedCountries, allSelected = false) {
                const container = $('#price-container');
                container.empty();

                console.log('Updating price fields for countries:', selectedCountries);
                console.log('All selected:', allSelected);

                if (allSelected || (selectedCountries.includes && selectedCountries.includes('all'))) {
                    container.html(`
                        <div class="mb-2">
                            <label for="common-price">Common Price for All Countries <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="common-price" class="form-control"
                                   placeholder="Enter common price for all countries" required min="0" step="0.01" />
                            <small class="text-muted">This price will apply to all selected countries.</small>
                        </div>
                    `);
                } else if (selectedCountries && selectedCountries.length > 0) {
                    let priceFieldsHtml = '<div class="price-fields-container">';

                    selectedCountries.forEach(id => {
                        if (id !== 'all') {
                            const $option = $('#country-select option[value="' + id + '"]');
                            const countryName = $option.text();
                            const currency = $option.data('currency') || '$';
                            const isoCode = $option.data('iso') || '';

                            priceFieldsHtml += `
                                <div class="mb-3 p-2 border rounded" style="background: white;">
                                    <label for="price_${id}" class="form-label">${countryName} Price (${currency}) <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-8">
                                            <input type="number" name="prices[${id}][amount]" id="price_${id}" class="form-control"
                                                   placeholder="Enter price in ${currency}" required min="0" step="0.01" />
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="prices[${id}][currency]" value="${isoCode}" class="form-control" 
                                                   placeholder="Currency Code" readonly />
                                        </div>
                                    </div>
                                    <small class="text-muted">Currency: ${currency} | ISO: ${isoCode}</small>
                                </div>
                            `;
                        }
                    });

                    priceFieldsHtml += '</div>';
                    container.html(priceFieldsHtml);
                } else {
                    container.html('<small class="text-muted">Please select countries above to set prices for each country.</small>');
                }
            }

            // Enhanced image preview with validation
            $('#thumbnail-input').on('change', function (event) {
                handleImagePreview(event, 'thumbnail_preview', {
                    maxSize: 5 * 1024 * 1024, // 5MB
                    recommendedRatio: 16/9,
                    type: 'Thumbnail'
                });
            });

            $('#poster-input').on('change', function (event) {
                handleImagePreview(event, 'poster_preview', {
                    maxSize: 5 * 1024 * 1024, // 5MB
                    recommendedRatio: 2/3,
                    type: 'Poster'
                });
            });

            function handleImagePreview(event, previewId, options) {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select a valid image file (JPG, PNG, etc.)');
                    event.target.value = '';
                    return;
                }

                // Validate file size
                if (file.size > options.maxSize) {
                    alert(`${options.type} file size should be less than ${options.maxSize / (1024 * 1024)}MB`);
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const actualRatio = this.width / this.height;
                        const expectedRatio = options.recommendedRatio;
                        const tolerance = 0.1;

                        // Show aspect ratio warning if too different
                        if (Math.abs(actualRatio - expectedRatio) > tolerance) {
                            const expectedText = options.type === 'Thumbnail' ? '16:9 (landscape)' : '2:3 (portrait)';
                            console.warn(`${options.type} aspect ratio is ${actualRatio.toFixed(2)}, recommended: ${expectedText}`);
                        }

                        // Update preview
                        $('#' + previewId).attr('src', e.target.result);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            // Initialize price fields for channel users
            @if(Auth::user()->role === 'channel')
                // Initial load - set up price fields if countries are pre-selected
                let initialCountries = $('#country-select').val() || [];
                if (initialCountries.length > 0) {
                    const wasAllSelected = initialCountries.includes('all');
                    updatePriceFields(initialCountries, wasAllSelected);
                }

                // Debug: Add console logs to check if functions are working
                console.log('Channel user detected, price fields should be available');
                console.log('Selected countries:', initialCountries);
            @endif

            // Debug: Log user role
            console.log('User role: {{ Auth::user()->role }}');
        });
    </script>
@endsection
