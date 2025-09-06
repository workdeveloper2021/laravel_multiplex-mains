@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header"><h4>Edit WebSeries - {{ $webseries->title }}</h4></div>
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

                <form action="{{ route('content.webseries.update', $webseries->_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title', $webseries->title) }}">
                    </div>

                    <div class="mb-4">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter webseries description...">{{ old('description', $webseries->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label>Genre<span class="text-danger">*</span></label>
                        <select id="genre-select" name="genre[]" class="form-control select2" multiple required>
                            <option value="all">All</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}"
                                    {{ collect(old('genre', $webseries->genre ?? []))->contains($genre->id) ? 'selected' : '' }}>
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
                                <option value="{{ $country['id'] }}"
                                    data-currency="{{ $country['symbol'] ?? '' }}"
                                    {{ collect(old('country', $webseries->country ?? []))->contains($country['id']) ? 'selected' : '' }}>
                                    {{ $countryName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Languages<span class="text-danger">*</span></label>
                        <select id="language-select" name="language[]" class="form-control select2" multiple required>
                            <option value="all">All Languages</option>
                            <option value="1" {{ collect(old('language', $webseries->language ?? []))->contains('1') ? 'selected' : '' }}>English</option>
                            <option value="2" {{ collect(old('language', $webseries->language ?? []))->contains('2') ? 'selected' : '' }}>Hindi</option>
                            <option value="3" {{ collect(old('language', $webseries->language ?? []))->contains('3') ? 'selected' : '' }}>Bhojpuri</option>
                            <option value="4" {{ collect(old('language', $webseries->language ?? []))->contains('4') ? 'selected' : '' }}>Marathi</option>
                            <option value="5" {{ collect(old('language', $webseries->language ?? []))->contains('5') ? 'selected' : '' }}>Gujarati</option>
                            <option value="6" {{ collect(old('language', $webseries->language ?? []))->contains('6') ? 'selected' : '' }}>Bengali</option>
                            <option value="7" {{ collect(old('language', $webseries->language ?? []))->contains('7') ? 'selected' : '' }}>Punjabi</option>
                            <option value="8" {{ collect(old('language', $webseries->language ?? []))->contains('8') ? 'selected' : '' }}>Tamil</option>
                            <option value="9" {{ collect(old('language', $webseries->language ?? []))->contains('9') ? 'selected' : '' }}>Kannada</option>
                            <option value="10" {{ collect(old('language', $webseries->language ?? []))->contains('10') ? 'selected' : '' }}>Telugu</option>
                            <option value="11" {{ collect(old('language', $webseries->language ?? []))->contains('11') ? 'selected' : '' }}>Malayalam</option>
                            <option value="12" {{ collect(old('language', $webseries->language ?? []))->contains('12') ? 'selected' : '' }}>Assamese</option>
                            <option value="13" {{ collect(old('language', $webseries->language ?? []))->contains('13') ? 'selected' : '' }}>Rajasthani</option>
                            <option value="14" {{ collect(old('language', $webseries->language ?? []))->contains('14') ? 'selected' : '' }}>Chhattisgarhi</option>
                        </select>
                    </div>

                    {{-- Dynamic Price Section - Only for Channel Users --}}
                    @if(Auth::user()->role === 'channel')
                        <div class="mb-4">
                            <label>Price (per country or common)</label>
                            <div id="price-container"></div>
                        </div>
                    @else
                        <input type="hidden" name="price" value="{{ old('price', $webseries->price ?? 100) }}">
                    @endif

                    {{-- Channel Selection - Only for Admin Users --}}

                    {{-- Channel users automatically use their own channel --}}
                        <div class="mb-4">
                            <label>Channel</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->name }} (Your Channel)" disabled>
                            <input type="hidden" name="channel_id" value="{{ Auth::user()->_id }}">
                            <small class="text-muted">Your Channel ID: {{ Auth::user()->_id }}</small>
                        </div>


                    <div class="mb-4">
                        <label>Release Date</label>
                        <input type="date" name="release" class="form-control" value="{{ old('release', $webseries->release) }}">
                    </div>

                    <div class="mb-4">
                        <label>Is Paid</label>
                        <select name="is_paid" class="form-control">
                            <option value="1" {{ old('is_paid', $webseries->is_paid) == '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ old('is_paid', $webseries->is_paid) == '0' ? 'selected' : '' }}>Free</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Publication</label>
                        <select name="publication" class="form-control">
                            <option value="1" {{ old('publication', $webseries->publication) == '1' ? 'selected' : '' }}>Publish</option>
                            <option value="0" {{ old('publication', $webseries->publication) == '0' ? 'selected' : '' }}>Unpublish</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Trailer Link (YouTube)</label>
                        <input type="url" name="trailer_link" class="form-control" value="{{ old('trailer_link', $webseries->trailer) }}">
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 text-center">
                            <label for="thumbnail_image" class="fw-bold text-primary">🖼️ Thumbnail (Horizontal - 16:9)</label>
                            <div class="border rounded p-2 mb-2 bg-light" style="width: 320px; height: 180px; display: flex; justify-content: center; align-items: center; margin: 0 auto; border: 2px dashed #007bff;">
                                <img id="thumbnail_preview" src="{{ $webseries->poster_url ?? asset('images/default-thumbnail.png') }}" style="max-width: 300px; max-height: 160px; object-fit: contain;" />
                            </div>
                            <input type="file" id="thumbnail-input" name="thumbnail" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended: 1920x1080 or 16:9 ratio</small>
                        </div>

                        <div class="col-md-6 text-center">
                            <label for="poster_image" class="fw-bold text-success">🎞️ Poster (Vertical - 2:3)</label>
                            <div class="border rounded p-2 mb-2 bg-light" style="width: 120px; height: 180px; display: flex; justify-content: center; align-items: center; margin: 0 auto; border: 2px dashed #28a745;">
                                <img id="poster_preview" src="{{ $webseries->thumbnail_url ?? asset('images/default-poster.png') }}" style="max-width: 100px; max-height: 160px; object-fit: contain;" />
                            </div>
                            <input type="file" id="poster-input" name="poster" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended: 600x900 or 2:3 ratio</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label>Enable Download</label>
                        <select name="enable_download" class="form-control">
                            <option value="1" {{ old('enable_download', $webseries->enable_download) == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ old('enable_download', $webseries->enable_download) == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update WebSeries</button>
                        <a href="{{ route('content.webseries.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- jQuery and Select2 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Replace 'all' in dropdowns with all actual IDs
            $('form').on('submit', function () {
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

            $('.select2').select2({ placeholder: "Select options", closeOnSelect: false });

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
                if (allSelected) {
                    container.append(`
                        <input type="number" name="price" id="common-price" class="form-control" placeholder="Enter common price for all countries" value="{{ old('price', $webseries->price ?? '') }}" required />
                    `);
                } else if (selectedCountries.length > 0) {
                    selectedCountries.forEach(id => {
                        const $option = $('#country-select option[value="' + id + '"]');
                        const countryName = $option.text();
                        const currency = $option.data('currency') || '';
                        container.append(`
                            <div class="mb-2">
                                <label>${countryName} Price (${currency})<span class="text-danger">*</span></label>
                                <input type="number" name="prices[${id}]" class="form-control" placeholder="Enter price in ${currency}" required />
                            </div>
                        `);
                    });
                } else {
                    container.append('<small class="text-muted">Select countries to set prices.</small>');
                }
            }

            @if(Auth::user()->role === 'channel')
                let oldCountries = $('#country-select').val() || [];
                const wasAllSelected = oldCountries.includes('all');
                updatePriceFields(oldCountries, wasAllSelected);
            @endif

            // Image preview functionality
            $('#thumbnail-input').on('change', function (event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => $('#thumbnail_preview').attr('src', e.target.result);
                    reader.readAsDataURL(file);
                }
            });

            $('#poster-input').on('change', function (event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => $('#poster_preview').attr('src', e.target.result);
                    reader.readAsDataURL(file);
                }
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
