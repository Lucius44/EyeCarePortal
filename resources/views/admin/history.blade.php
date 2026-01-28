@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="{{ route('admin.calendar') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-calendar-week me-2"></i> Calendar</a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-check-circle me-2"></i> Appointments</a>
                <a href="{{ route('admin.history') }}" class="btn btn-primary mb-2 text-start"><i class="bi bi-clock-history me-2"></i> History</a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-people me-2"></i> Users List</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Appointment History</h2>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.history') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by Patient Name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">-- All Statuses --</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="no-show" {{ request('status') == 'no-show' ? 'selected' : '' }}>No-Show</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary w-100">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Type</th> {{-- New Column --}}
                                <th>Date</th>
                                <th>Status</th>
                                <th>Reason/Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $appt)
                            <tr>
                                <td>{{ $appt->patient_name }}</td>
                                <td>
                                    @if($appt->user_id)
                                        <span class="badge bg-primary">Registered</span>
                                    @else
                                        <span class="badge bg-info text-dark">Guest</span>
                                    @endif
                                </td>
                                <td>{{ $appt->appointment_date->format('M d, Y') }}</td>
                                <td>
                                    @if($appt->status->value === 'completed') 
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($appt->status->value === 'cancelled') 
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @elseif($appt->status->value === 'rejected') 
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($appt->status->value === 'no-show') 
                                        <span class="badge bg-warning text-dark">No-Show</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $appt->cancellation_reason ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No history records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection