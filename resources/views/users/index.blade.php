@extends('layouts.app') {{-- Adjust to your layout --}}

@section('content')
    <!-- Add CSRF token meta tag -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container">
        <h2 class="mb-4">Users List</h2>

        <div class="table-responsive">
            <table class="table table-bordered" id="users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Assign Payment</th>
                        {{--  <th>Assign Plan</th>  --}}
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Plan Assignment Modal --}}
    <div class="modal fade" id="assignPlanModal" tabindex="-1" aria-labelledby="assignPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignPlanModalLabel">Assign Plan to User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignPlanForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="userId" name="user_id">

                        <div class="mb-3">
                            <label class="form-label">User Name</label>
                            <input type="text" id="userName" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Plan <span class="text-danger">*</span></label>
                            <select id="planSelect" name="plan_id" class="form-control" required>
                                <option value="">Choose Plan...</option>
                                {{-- Plans will be loaded dynamically --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Duration (Days)</label>
                            <input type="number" id="duration" name="duration" class="form-control" min="1"
                                placeholder="Enter duration in days" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="assignPlanBtn">Assign Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Payment Modal --}}
    <div class="modal fade" id="assignPaymentModal" tabindex="-1" aria-labelledby="assignPaymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignPaymentModalLabel">Assign Manual Payment to User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignPaymentForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="paymentUserId" name="user_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">User Name</label>
                                    <input type="text" id="paymentUserName" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Custom Duration (Days) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" id="customDuration" name="custom_duration" class="form-control"
                                        min="1" value="2" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price Amount <span class="text-danger">*</span></label>
                                    <input type="number" id="priceAmount" name="price_amount" class="form-control"
                                        min="0" step="0.01" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
                                    <input type="number" id="paidAmount" name="paid_amount" class="form-control"
                                        min="0" step="0.01" value="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Channel (Optional)</label>
                                    <select id="channelSelect" name="channel_id" class="form-control">
                                        <option value="">Choose Channel (Optional)...</option>
                                        {{-- Channels will be loaded dynamically --}}
                                    </select>
                                </div>
                            </div>
                            {{--  <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Video ID (Optional)</label>
                                    <input type="text" id="videoId" name="video_id" class="form-control" placeholder="Enter video ID">
                                </div>
                            </div>  --}}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Plan ID (Optional)</label>
                            <select id="paymentPlanSelect" name="plan_id" class="form-control">
                                <option value="">Choose Plan (Optional)...</option>
                                {{-- Plans will be loaded dynamically --}}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="assignPaymentBtn">Assign Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include jQuery and DataTables if not already included -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'assign_payment',
                        name: 'assign_payment',
                        orderable: false,
                        searchable: false
                    },

                ]
            });
            //{ data: 'assign_plan', name: 'assign_plan', orderable: false, searchable: false },

            // Load plans when modal opens
            $('#assignPlanModal').on('show.bs.modal', function() {
                // Clear and reset the form first
                $('#planSelect').empty().append('<option value="">Loading plans...</option>');
                loadPlans();
            });

            // Load plans when payment modal opens
            $('#assignPaymentModal').on('show.bs.modal', function() {
                // Clear and reset the form first
                $('#paymentPlanSelect').empty().append('<option value="">Loading plans...</option>');
                $('#channelSelect').empty().append('<option value="">Loading channels...</option>');
                loadPlansForPayment();
                loadChannelsForPayment();
            });

            // Debug plan selection
            $(document).on('change', '#planSelect', function() {
                const selectedValue = $(this).val();
                const selectedText = $(this).find('option:selected').text();
                console.log('Plan selected:');
                console.log('Value:', selectedValue);
                console.log('Text:', selectedText);
            });

            // Debug duration input
            $(document).on('input', '#duration', function() {
                const durationValue = $(this).val();
                console.log('Duration entered:', durationValue);
            });

            // Handle plan assignment form submission
            $('#assignPlanForm').on('submit', function(e) {
                e.preventDefault();

                const userId = $('#userId').val();
                const planId = $('#planSelect').val();
                const duration = $('#duration').val();

                // Debug logs
                console.log('Form submission data:');
                console.log('User ID:', userId);
                console.log('Plan ID:', planId);
                console.log('Duration:', duration);
                console.log('Plan dropdown element:', $('#planSelect')[0]);
                console.log('Plan dropdown HTML:', $('#planSelect').html());
                console.log('Selected option:', $('#planSelect option:selected'));
                console.log('Selected option value:', $('#planSelect option:selected').val());
                console.log('Selected option text:', $('#planSelect option:selected').text());

                // Validation
                if (!planId || planId === '' || planId === 'undefined') {
                    alert('Please select a plan');
                    return;
                }

                if (!duration || duration < 1) {
                    alert('Please enter a valid duration');
                    return;
                }

                const formData = {
                    user_id: userId,
                    plan_id: planId,
                    duration: duration,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                console.log('Final form data:', formData);

                $('#assignPlanBtn').prop('disabled', true).text('Assigning...');

                $.ajax({
                    url: '{{ route('users.assign-plan') }}', // You'll need to create this route
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        alert('Plan assigned successfully!');
                        $('#assignPlanModal').modal('hide');
                        $('#users-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message ||
                        'Something went wrong'));
                    },
                    complete: function() {
                        $('#assignPlanBtn').prop('disabled', false).text('Assign Plan');
                    }
                });
            });

            // Handle payment assignment form submission
            $('#assignPaymentForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    user_id: $('#paymentUserId').val(),
                    channel_id: $('#channelSelect').val() || null,
                    video_id: $('#videoId').val() || null,
                    plan_id: $('#paymentPlanSelect').val() || null,
                    price_amount: $('#priceAmount').val(),
                    paid_amount: $('#paidAmount').val(),
                    custom_duration: $('#customDuration').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $('#assignPaymentBtn').prop('disabled', true).text('Assigning...');

                $.ajax({
                    url: '{{ route('users.assign-payment') }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        alert('Payment assigned successfully!');
                        $('#assignPaymentModal').modal('hide');
                        $('#users-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        let errorMessage = 'Something went wrong';
                        if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert('Error: ' + errorMessage);
                    },
                    complete: function() {
                        $('#assignPaymentBtn').prop('disabled', false).text('Assign Payment');
                    }
                });
            });
        });

        // Function to open assign plan modal
        function openAssignPlanModal(userId, userName) {
            console.log('Opening modal for:', userId, userName);

            // Set values first
            $('#userId').val(userId);
            $('#userName').val(userName);
            $('#duration').val(''); // Reset duration

            console.log('User ID set to:', $('#userId').val());
            console.log('User Name set to:', $('#userName').val());

            // Show modal (this will trigger plan loading)
            $('#assignPlanModal').modal('show');
        }

        // Function to open assign payment modal
        function openAssignPaymentModal(userId, userName) {
            console.log('Opening payment modal for:', userId, userName);

            // Set values and reset form
            $('#paymentUserId').val(userId);
            $('#paymentUserName').val(userName);
            $('#customDuration').val('2');
            $('#priceAmount').val('0');
            $('#paidAmount').val('0');
            $('#channelSelect').val('');
            $('#videoId').val('');

            // Show modal (this will trigger plan loading)
            $('#assignPaymentModal').modal('show');
        }

        // Function to load plans dynamically
        function loadPlans() {
            console.log('Starting to load plans...');

            $.ajax({
                url: '{{ route('users.get-plans') }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                beforeSend: function() {
                    console.log('Sending request to load plans...');
                },
                success: function(response) {
                    console.log('Plans response received:', response);

                    const planSelect = $('#planSelect');

                    // Clear existing options
                    planSelect.empty();
                    planSelect.append('<option value="">Choose Plan...</option>');

                    // Check if response is successful and contains plans
                    if (response.success && response.data && response.data.length > 0) {
                        console.log('Processing', response.data.length, 'plans');

                        response.data.forEach(function(plan, index) {
                            console.log(`Plan ${index + 1}:`, plan);

                            // Make sure plan has required fields
                            if ((plan._id || plan.id) && plan.name && plan.price) {
                                const optionValue = plan._id || plan.id;
                                const optionText = `${plan.name} - $${plan.price}`;
                                const optionHtml =
                                    `<option value="${optionValue}">${optionText}</option>`;

                                console.log('Adding option:', optionHtml);
                                planSelect.append(optionHtml);
                            } else {
                                console.warn('Plan missing required fields:', plan);
                            }
                        });

                        console.log('Plans loaded successfully');
                        console.log('Final dropdown options count:', planSelect.find('option').length);

                        // Test if dropdown is properly populated
                        setTimeout(function() {
                            console.log('Current dropdown HTML:', planSelect.html());
                        }, 100);

                    } else {
                        console.log('No plans found in response');
                        planSelect.append('<option value="">No plans available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load plans:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);

                    const planSelect = $('#planSelect');
                    planSelect.empty();
                    planSelect.append('<option value="">Failed to load plans</option>');

                    alert('Failed to load plans. Please check console for details.');
                }
            });
        }

        // Function to load plans for payment modal
        function loadPlansForPayment() {
            $.ajax({
                url: '{{ route('users.get-plans') }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const planSelect = $('#paymentPlanSelect');

                    // Clear existing options
                    planSelect.empty();
                    planSelect.append('<option value="">Choose Plan (Optional)...</option>');

                    // Check if response is successful and contains plans
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(function(plan) {
                            if ((plan._id || plan.id) && plan.name && plan.price) {
                                const optionValue = plan._id || plan.id;
                                const optionText = `${plan.name} - $${plan.price}`;
                                planSelect.append(
                                    `<option value="${optionValue}">${optionText}</option>`);
                            }
                        });
                    } else {
                        planSelect.append('<option value="">No plans available</option>');
                    }
                },
                error: function() {
                    const planSelect = $('#paymentPlanSelect');
                    planSelect.empty();
                    planSelect.append('<option value="">Failed to load plans</option>');
                }
            });
        }

        // Function to load channels for payment modal
        function loadChannelsForPayment() {
            $.ajax({
                url: '{{ route('users.get-channels') }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const channelSelect = $('#channelSelect');

                    // Clear existing options
                    channelSelect.empty();
                    channelSelect.append('<option value="">Choose Channel (Optional)...</option>');

                    // Check if response is successful and contains channels
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(function(channel) {
                            if ((channel._id || channel.id) && channel.channel_name) {
                                const optionValue = channel._id || channel.id;
                                const optionText = channel.channel_name;
                                channelSelect.append(
                                    `<option value="${optionValue}">${optionText}</option>`);
                            }
                        });
                    } else {
                        channelSelect.append('<option value="">No channels available</option>');
                    }
                },
                error: function() {
                    const channelSelect = $('#channelSelect');
                    channelSelect.empty();
                    channelSelect.append('<option value="">Failed to load channels</option>');
                }
            });
        }
    </script>
@endsection
