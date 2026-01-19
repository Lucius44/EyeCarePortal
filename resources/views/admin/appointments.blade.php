@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary mb-2 text-start border-0">
                    📅 Calendar
                </a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-primary mb-2 text-start">
                    ✅ Manage Appointments
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    👥 Users List
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
                <li class="nav-item">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">History</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                
                <div class="tab-pane fade show active" id="pending">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pending as $appt)
                                    <tr>
                                        <td>{{ $appt->user->first_name }} {{ $appt->user->last_name }}</td>
                                        <td>
                                            {{ $appt->appointment_date->format('M d, Y') }} 
                                            <br> 
                                            <small class="text-muted">{{ $appt->appointment_time }}</small>
                                        </td>
                                        <td>{{ $appt->service }}</td>
                                        <td>{{ Str::limit($appt->description, 30) }}</td>
                                        <td>
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="confirmed">
                                                <button class="btn btn-success btn-sm">Accept</button>
                                            </form>
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No pending requests.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ongoing">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table class="table table-hover">
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
                                        <td>{{ $appt->user->first_name }} {{ $appt->user->last_name }}</td>
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

                <div class="tab-pane fade" id="history">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $appt)
                                    <tr>
                                        <td>{{ $appt->user->first_name }} {{ $appt->user->last_name }}</td>
                                        <td>{{ $appt->appointment_date->format('M d, Y') }}</td>
                                        <td>
                                            {{-- FIX: Compare the Enum VALUE, not the object --}}
                                            @if($appt->status->value === 'completed') 
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($appt->status->value === 'cancelled') 
                                                <span class="badge bg-danger">Cancelled</span>
                                            @elseif($appt->status->value === 'no-show') 
                                                <span class="badge bg-warning text-dark">No-Show</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection