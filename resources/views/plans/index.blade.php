@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Plans List</h5>
                <a href="{{ route('plan.create') }}" class="btn btn-primary">Create Plan</a>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($plans ?? [])
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Plan ID</th>
                                    <th>Name</th>
                                    <th>Country</th>
                                    <th>Currency</th>
                                    <th>Day</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plans as $index => $plan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $plan->plan_id }}</td>
                                        <td>{{ $plan->name }}</td>
                                        <td>{{ $plan->country }}</td>
                                        <td>{{ $plan->currency }}</td>
                                        <td>{{ $plan->day }}</td>
                                        <td>{{ $plan->price }}</td>
                                        <td>{{ $plan->status ? 'Active' : 'Inactive' }}</td>
                                        <td>
                                            <a href="{{ route('plan.edit', $plan->_id) }}"
                                                class="btn btn-warning btn-sm">Edit</a>
                                            <form action="{{ route('plan.destroy', $plan->_id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Are you sure?')"
                                                    class="btn btn-danger btn-sm" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $plans->links() }}
                    </div>
                @else
                    <p class="text-muted">No Plans available.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
