@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5>Create New Plan</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('plan.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Plan ID</label>
                        <input type="text" name="plan_id" id="plan_id" class="form-control"
                            value="{{ old('plan_id') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Plan Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                            required>
                    </div>

                    @php
                        // Sort by country name (keep)
                        $sortedCountries = collect($countries)->sortBy('country')->values()->toArray();
                    @endphp

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <select name="country" id="country" class="form-control select2-single" required>
                            <option value="" disabled {{ old('country') ? '' : 'selected' }}>Select Country</option>
                            @foreach ($sortedCountries as $country)
                                <option value="{{ $country['short_hand_name'] }}" data-currency="{{ $country['currency'] }}"
                                    data-symbol="{{ $country['symbol'] }}"
                                    {{ old('country') == $country['short_hand_name'] ? 'selected' : '' }}>
                                    {{ $country['country'] }} - {{ $country['symbol'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select name="currency" id="currency" class="form-control select2-single" required>
                            <option value="" disabled {{ old('currency') ? '' : 'selected' }}>Select Currency
                            </option>
                            {{-- JS will inject the correct single option based on selected country --}}
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="day" class="form-label">Day(s)</label>
                        <input type="number" name="day" id="day" class="form-control"
                            value="{{ old('day') }}" min="1" placeholder=" (30 = 1 Months, 60 = 2 Months)"
                            required>
                    </div>

                    {{--                    <div class="mb-3"> --}}
                    {{--                        <label for="screens" class="form-label">Screens</label> --}}
                    {{--                        <input type="number" name="screens" id="screens" class="form-control" value="{{ old('screens') }}" min="1" required> --}}
                    {{--                    </div> --}}

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" id="price" class="form-control"
                            value="{{ old('price') }}" min="0" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Create Plan</button>
                    <a href="{{ route('plan.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country');
            const currencySelect = document.getElementById('currency');

            function setCurrencyFromCountry() {
                const opt = countrySelect.options[countrySelect.selectedIndex];
                if (!opt || !opt.dataset) return;

                const currency = opt.dataset.currency || '';
                const symbol = opt.dataset.symbol || '';

                // Replace currency options with exactly one correct option
                currencySelect.innerHTML = '';

                if (currency) {
                    const option = document.createElement('option');
                    option.value = currency;
                    option.textContent = `${currency} - ${symbol}`;
                    option.selected = true;
                    currencySelect.appendChild(option);
                } else {
                    // Put back placeholder if no country selected
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    placeholder.textContent = 'Select Currency';
                    currencySelect.appendChild(placeholder);
                }
            }

            // Wire up change handler
            countrySelect.addEventListener('change', setCurrencyFromCountry);

            // On initial load, if a country is preselected (e.g., validation error), sync currency
            if (countrySelect.value) {
                setCurrencyFromCountry();
            }
        });
    </script>
@endpush
