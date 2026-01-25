@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">My Appointments</h2>
    </div>

    {{-- Success/Error Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill" id="apptTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active py-3 fw-bold" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button">
                        <i class="bi bi-calendar-event me-2"></i> Upcoming
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 fw-bold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
                        <i class="bi bi-clock-history me-2"></i> History
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4" id="apptTabContent">
                
                {{-- UPCOMING TAB --}}
                <div class="tab-pane fade show active" id="upcoming">
                    @if($upcoming->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-muted">No upcoming appointments</h5>
                            <p class="text-muted small">You're all caught up! Need to see a doctor?</p>
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary mt-2">Book Now</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Date & Time</th>
                                        <th style="width: 20%;">Service</th>
                                        <th style="width: 15%;">Status</th>
                                        <th style="width: 25%;">Notes</th>
                                        {{-- ADDED: Actions Column --}}
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcoming as $appt)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                            <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $appt->appointment_time }}</div>
                                        </td>
                                        <td>{{ $appt->service }}</td>
                                        <td>
                                            @if($appt->status->value === 'confirmed')
                                                <span class="badge bg-success rounded-pill px-3">Confirmed</span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($appt->description)
                                                <small class="text-muted fst-italic">{{ Str::limit($appt->description, 30) }}</small>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        {{-- ADDED: Cancel Button --}}
                                        <td class="text-end">
                                            <form action="{{ route('appointments.cancel', $appt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- HISTORY TAB --}}
                <div class="tab-pane fade" id="history">
                    @if($history->isEmpty())
                        <div class="text-center py-5">
                            <p class="text-muted">No appointment history found.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                        <td>{{ $appt->appointment_date->format('M d, Y') }}</td>
                                        <td>{{ $appt->service }}</td>
                                        <td>
                                            @if($appt->status->value === 'completed') 
                                                <span class="badge bg-primary rounded-pill px-3">Completed</span>
                                            @elseif($appt->status->value === 'cancelled') 
                                                <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                            @elseif($appt->status->value === 'no-show') 
                                                <span class="badge bg-secondary rounded-pill px-3">No-Show</span>
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
@endsection