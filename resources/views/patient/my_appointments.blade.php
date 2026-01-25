@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">My Appointments</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
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
                
                <div class="tab-pane fade show active" id="upcoming">
                    @if($upcoming->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i></div>
                            <h5 class="text-muted">No upcoming appointments</h5>
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
                                        <td>{{ Str::limit($appt->description, 30) ?: '-' }}</td>
                                        <td class="text-end">
                                            @if($appt->status->value === 'pending')
                                                {{-- 1. PENDING: Simple Confirm Modal --}}
                                                <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#cancelPending-{{ $appt->id }}">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </button>
                                            @else
                                                {{-- 2. CONFIRMED: Reason Modal --}}
                                                <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#cancelConfirmed-{{ $appt->id }}">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- MODALS --}}
                                    
                                    <div class="modal fade" id="cancelPending-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0">
                                                <div class="modal-body p-4 text-center">
                                                    <h5 class="fw-bold mb-3">Cancel Request?</h5>
                                                    <p class="text-muted mb-4">Are you sure you want to remove this appointment request? It hasn't been confirmed yet.</p>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Keep It</button>
                                                        <form action="{{ route('appointments.cancel', $appt->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4">Yes, Cancel</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="cancelConfirmed-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('appointments.cancel', $appt->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content rounded-4 border-0">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Cancel Appointment</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-muted">Since this appointment is confirmed, please let us know why you are cancelling.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="fw-bold small text-uppercase">Reason</label>
                                                            <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="e.g. I found another doctor, I got sick..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="history">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Notes</th>
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
                                            <span class="badge bg-secondary rounded-pill px-3">Cancelled</span>
                                        @elseif($appt->status->value === 'rejected') 
                                            <span class="badge bg-danger rounded-pill px-3">Rejected</span>
                                        @elseif($appt->status->value === 'no-show') 
                                            <span class="badge bg-warning text-dark rounded-pill px-3">No-Show</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $appt->cancellation_reason ?? '-' }}</td>
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
@endsection