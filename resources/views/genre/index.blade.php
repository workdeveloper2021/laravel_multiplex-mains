@extends('layouts.app')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Banner List</h2>
        <a href="{{ route('genre.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
            ➕ Add Genre
        </a>
    </div>
    <div class="container mt-5 mx-auto px-4">


        <table class="min-w-full table-auto border-collapse shadow-lg bg-white rounded-lg" id="genreTable">
            <thead class="bg-indigo-600 text-white">
            <tr>
                <th class="py-2 px-4 text-left">#</th>
                <th class="py-2 px-4 text-left">Genre Name</th>
                <th class="py-2 px-4 text-left">Description</th>
                <th class="py-2 px-4 text-left">Image</th>
                <th class="py-2 px-4 text-left">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            </tbody>
        </table>
    </div>

    @push('scripts')
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#genreTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('genre.index') }}',
                        data: function(d) {
                            d.start = d.start || 0;
                            d.length = d.length || 10;
                            d.draw = d.draw || 1;
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', class: 'text-center' },
                        { data: 'name', name: 'name' },
                        { data: 'description', name: 'description' },
                        { data: 'image_url', name: 'image_url', render: function(data) {
                                // return data ? `<img src="{{ asset('storage/') }}/${data}" alt="Image" class="w-20 h-20 object-cover rounded-md">` : 'No Image';
                                return data ? `<img src="/storage/${data}" alt="Image" class="w-20 h-20 object-cover rounded-md">` : 'No Image';

    //return data ? `<img src="${data}" alt="Image" class="w-20 h-20 object-cover rounded-md">` : 'No Image';


                            }},
                        { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center' }, // Action column
                    ],
                    order: [[0, 'asc']], // Sort by the first column by default
                    pageLength: 10, // Set default page length
                    lengthMenu: [10, 20, 30], // Page length options
                    responsive: true, // Make the table responsive
                });
            });
        </script>
    @endpush
@endsection
