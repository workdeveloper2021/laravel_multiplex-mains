@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Transaction Logs</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover" id="transaction-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Transaction Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#transaction-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("tlogs.index") }}',
                columns: [
                    { data: 'user', name: 'user' },
                    { data: 'email', name: 'email' },
                    { data: 'transaction_type', name: 'transaction_type' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[4, 'desc']],
                responsive: true,
                language: {
                    emptyTable: "No transactions available",
                    processing: "Loading..."
                }
            });
        });
    </script>
@endpush
