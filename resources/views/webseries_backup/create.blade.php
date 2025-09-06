@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header"><h4>Add New WebSeries</h4></div>
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
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                    </div>

                    <div class="mb-4">
                        <label>Genre</label>
                        <select name="genre" class="form-control" required>
                            <option disabled selected>Select Genre</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}" {{ old('genre') == $genre->id ? 'selected' : '' }}>
                                    {{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Languages</label>
                        <select id="language-select" name="language[]" class="form-control select2" multiple required>
                            <option value="all">All</option>
                            @foreach($languages as $language)
                                <option value="{{ $language['_id'] }}"
                                    {{ collect(old('language'))->contains($language['_id']) ? 'selected' : '' }}>
                                    {{ $language['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Countries</label>
                        <select id="country-select" name="country[]" class="form-control select2" multiple required>
                            <option value="all">All</option>
                            @foreach($countries as $country)
                                <option value="{{ $country['id'] }}"
                                    {{ collect(old('country'))->contains($country['id']) ? 'selected' : '' }}>
                                    {{ $country['country'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dynamic Price Section --}}
                    <div class="mb-4">
                        <label>Price (per country or common)</label>
                        <div id="price-container">
                            {{-- JS will inject inputs here --}}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label>Channel</label>
                        <select name="channel_id" class="form-control" required>
                            <option disabled selected>Select Channel</option>
                            @foreach($channels as $channel)
                                <option value="{{ $channel['id'] }}" {{ old('channel_id') == $channel['id'] ? 'selected' : '' }}>
                                    {{ $channel['channel_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Release Date</label>
                        <input type="date" name="release" class="form-control" value="{{ old('release') }}">
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

                    <div class="row mb-4">
                        <div class="col-md-6 text-center">
                            <label for="thumbnail_image">🖼️ Thumbnail (Horizontal)</label>
                            <div class="border rounded p-2 mb-2" style="height: 200px; display: flex; justify-content: center; align-items: center;">
                                <img id="thumbnail_preview" src="{{ asset('images/default-thumbnail.png') }}" style="max-height: 180px;" />
                            </div>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-6 text-center">
                            <label for="poster_image">🎞️ Poster (Vertical)</label>
                            <div class="border rounded p-2 mb-2" style="height: 200px; display: flex; justify-content: center; align-items: center;">
                                <img id="poster_preview" src="{{ asset('images/default-poster.png') }}" style="max-height: 180px;" />
                            </div>
                            <input type="file" name="poster" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label>Video File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept="video/*" required>
                    </div>

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

    {{-- jQuery and Select2 scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({ placeholder: "Select options", closeOnSelect: false });

            // Handle Select All for Countries and Languages
            function handleSelectAll(selectId) {
                $(`#${selectId}`).on('change', function () {
                    let selected = $(this).val();
                    if (selected && selected.includes('all')) {
                        $(this).val(['all']).trigger('change.select2');
                    }
                });
            }
            handleSelectAll('country-select');
            handleSelectAll('language-select');

            // Price input dynamic handling
            function updatePriceFields(selectedCountries) {
                const container = $('#price-container');
                container.empty();

                if (selectedCountries.includes('all')) {
                    // Common price input
                    container.append(`
                        <input type="number" name="price" id="common-price" class="form-control" placeholder="Enter common price for all countries" required />
                    `);
                } else if(selectedCountries.length > 0) {
                    // Per-country price inputs
                    selectedCountries.forEach(id => {
                        const countryName = $('#country-select option[value="' + id + '"]').text();
                        container.append(`
                            <div class="mb-2">
                                <label>${countryName} Price</label>
                                <input type="number" name="prices[${id}]" class="form-control" placeholder="Enter price for ${countryName}" required />
                            </div>
                        `);
                    });
                } else {
                    // No countries selected
                    container.append('<small class="text-muted">Select countries to set prices.</small>');
                }
            }

            // On page load, populate price fields if old input exists
            let oldCountries = $('#country-select').val() || [];
            updatePriceFields(oldCountries);

            // On country select change
            $('#country-select').on('change', function () {
                let selected = $(this).val() || [];
                updatePriceFields(selected);
            });
        });
    </script>
@endsection
