@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">My Profile</a>
            <a href="{{ route('appointments.index') }}" class="list-group-item list-group-item-action">Book Appointment</a>
            <a href="{{ route('my.appointments') }}" class="list-group-item list-group-item-action active">My Appointments</a>
            <a href="{{ route('settings') }}" class="list-group-item list-group-item-action">Account Settings</a>
            
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action text-danger">Logout</button>
            </form>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">My Appointments</h4>
            </div>
            <div class="card-body">
                
                <ul class="nav nav-tabs mb-3" id="apptTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button">Upcoming</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">History</button>
                    </li>
                </ul>

                <div class="tab-content" id="apptTabContent">
                    
                    <div class="tab-pane fade show active" id="upcoming">
                        @if($upcoming->isEmpty())
                            <div class="text-center py-4">
                                <p class="text-muted">You have no upcoming appointments.</p>
                                <a href="{{ route('appointments.index') }}" class="btn btn-primary btn-sm">Book Now</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcoming as $appt)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($appt->appointment_date)->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $appt->appointment_time }}</small>
                                            </td>
                                            <td>{{ $appt->service }}</td>
                                            <td>
                                                @if($appt->status == 'confirmed')
                                                    <span class="badge bg-success">Confirmed</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($appt->description)
                                                    <small class="text-muted">{{ Str::limit($appt->description, 30) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="history">
                        @if($history->isEmpty())
                            <p class="text-muted text-center py-4">No appointment history found.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($history as $appt)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('M d, Y') }}</td>
                                            <td>{{ $appt->service }}</td>
                                            <td>
                                                @if($appt->status == 'completed') 
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($appt->status == 'cancelled') 
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @elseif($appt->status == 'no-show') 
                                                    <span class="badge bg-secondary">No-Show</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection