@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5>Edit Plan: {{ $plan->name }}</h5>
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

                <form action="{{ route('plan.update', $plan->_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Plan ID</label>
                        <input type="text" name="plan_id" id="plan_id" class="form-control" value="{{ old('plan_id', $plan->plan_id) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Plan Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $plan->country) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <input type="text" name="currency" id="currency" class="form-control" value="{{ old('currency', $plan->currency) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="day" class="form-label">Day(s)</label>
                        <input type="number" name="day" id="day" class="form-control" value="{{ old('day', $plan->day) }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="screens" class="form-label">Screens</label>
                        <input type="number" name="screens" id="screens" class="form-control" value="{{ old('screens', $plan->screens) }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $plan->price) }}" min="0" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="1" {{ old('status', $plan->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $plan->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Plan</button>
                    <a href="{{ route('plan.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection
