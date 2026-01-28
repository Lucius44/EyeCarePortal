@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.calendar') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-calendar-week me-2"></i> Calendar
                </a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-primary mb-2 text-start">
                    <i class="bi bi-check-circle me-2"></i> Appointments
                </a>
                <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-clock-history me-2"></i> History
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-people me-2"></i> Users List
                </a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Manage Appointments</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending Requests</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button">Ongoing / Confirmed</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                
                {{-- Pending Tab --}}
                <div class="tab-pane fade show active" id="pending">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pending as $appt)
                                    <tr>
                                        <td>{{ $appt->patient_name }}</td>
                                        <td>
                                            {{ $appt->appointment_date->format('M d, Y') }} 
                                            <br> 
                                            <small class="text-muted">{{ $appt->appointment_time }}</small>
                                        </td>
                                        <td>{{ $appt->service }}</td>
                                        <td>{{ Str::limit($appt->description, 30) ?: '-' }}</td>
                                        <td>
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="confirmed">
                                                <button class="btn btn-success btn-sm">Accept</button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $appt->id }}">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Reject Modal --}}
                                    <div class="modal fade" id="rejectModal-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Reject Appointment</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Why are you rejecting this appointment?</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason</label>
                                                            <select class="form-select" name="reason_select" onchange="toggleOther(this, '{{ $appt->id }}')" required>
                                                                <option value="">-- Select Reason --</option>
                                                                <option value="Doctor Unavailable">Doctor Unavailable</option>
                                                                <option value="Double Booked">Double Booked</option>
                                                                <option value="Service Not Available">Service Not Available</option>
                                                                <option value="Incomplete Information">Incomplete Information</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 d-none" id="otherReasonDiv-{{ $appt->id }}">
                                                            <label class="form-label">Specific Reason</label>
                                                            <textarea name="cancellation_reason" id="textArea-{{ $appt->id }}" class="form-control" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No pending requests.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Ongoing Tab --}}
                <div class="tab-pane fade" id="ongoing">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($confirmed as $appt)
                                    <tr>
                                        <td>{{ $appt->patient_name }}</td>
                                        <td>
                                            {{ $appt->appointment_date->format('M d, Y') }} 
                                            <br> 
                                            <small class="text-muted">{{ $appt->appointment_time }}</small>
                                        </td>
                                        <td>{{ $appt->service }}</td>
                                        <td>
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="completed">
                                                <button class="btn btn-primary btn-sm">Mark Complete</button>
                                            </form>
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="no-show">
                                                <button class="btn btn-warning btn-sm text-white">No-Show</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted">No upcoming appointments.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function toggleOther(select, id) {
        const otherDiv = document.getElementById('otherReasonDiv-' + id);
        const textArea = document.getElementById('textArea-' + id);
        
        if (select.value === 'Other') {
            otherDiv.classList.remove('d-none');
            textArea.required = true;
            textArea.value = '';
        } else {
            otherDiv.classList.add('d-none');
            textArea.required = false;
            textArea.value = select.value;
        }
    }
</script>
@endsection