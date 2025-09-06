@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Channel Management</h2>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="channelTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="table-dark text-center">
                <tr>
                    <th>Channel</th>
                    <th>User</th>
                    <th>Mobile</th>
                    <th>Address</th>
                    <th>Organization</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Document</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($channels as $channel)
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'approve' => 'success',
                            'rejected' => 'danger',
                            'block' => 'secondary',
                        ];
                        $color = $statusColors[$channel['status']] ?? 'dark';
                    @endphp
                    <tr>
                        <td>{{ $channel['channel_name'] }}</td>
                        <td>{{ $channel['user'] }}</td>
                        <td>{{ $channel['mobile_number'] }}</td>
                        <td>{{ $channel['address'] }}</td>
                        <td>{{ $channel['organization_name'] }}</td>
                        <td>
                            <form action="{{ route('channels.update', $channel['id']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" {{ $channel['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approve" {{ $channel['status'] === 'approve' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $channel['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="block" {{ $channel['status'] === 'block' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </form>
                            <span class="badge bg-{{ $color }} mt-1 d-inline-block w-100 text-uppercase fw-bold">
                                {{ ucfirst($channel['status']) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($channel['join_date'])->format('d M, Y h:i A') }}</td>
                        <td>
                           @php
                                $url = $channel['document_path'] ?? null;
                            @endphp
                            @if($url)
                                @if(Str::endsWith($url, ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                                    {{-- Image preview thumbnail --}}
                                    <img src="{{ $url }}" alt="image" width="100" height="80"
                                        data-bs-toggle="modal" data-bs-target="#filePreviewModal"
                                        onclick="showPreview('{{ $url }}', 'image')">

                                @elseif(Str::endsWith($url, ['.pdf']))
                                    {{-- PDF icon / link --}}
                                    <a href="javascript:void(0)"
                                    data-bs-toggle="modal" data-bs-target="#filePreviewModal"
                                    onclick="showPreview('{{ $url }}', 'pdf')">
                                    📄 View PDF
                                    </a>

                                @else
                                    {{-- Other file: link --}}
                                    <a href="javascript:void(0)"
                                    data-bs-toggle="modal" data-bs-target="#filePreviewModal"
                                    onclick="showPreview('{{ $url }}', 'other')">
                                    📎 View File
                                    </a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <!-- Bootstrap Modal -->
                <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center" id="filePreviewContent">
                        <!-- Dynamic content will load here -->
                    </div>
                    </div>
                </div>
                </div>

        </div>
    </div>
@endsection

@section('styles')
    {{-- DataTables & FontAwesome CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        table th, table td {
            vertical-align: middle !important;
            text-align: center;
        }
    </style>
@endsection

@push('scripts')
    <script>
    function showPreview(url, type) {
        let content = '';

        if(type === 'image') {
            content = `<img src="${url}" alt="Preview" style="max-width:100%; max-height:80vh;">`;
        }
        else if(type === 'pdf') {
            content = `<iframe src="${url}" width="100%" height="600px"></iframe>`;
        }
        else {
            content = `<a href="${url}" target="_blank">Download File</a>`;
        }

        document.getElementById('filePreviewContent').innerHTML = content;
    }

    $(document).ready(function () {
        $('#channelTable').DataTable({
            responsive: true,
            pageLength: 10,
            ordering: true,
            autoWidth: false,
            language: {
                searchPlaceholder: "Search channels...",
                search: "",
            }
        });
    });
    </script>
@endpush
