@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark mb-1">My Appointments</h2>
            <p class="text-muted">Track your scheduled visits and history.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('appointments.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Book New
            </a>
        </div>
    </div>

    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold px-4 me-2" id="pills-upcoming-tab" data-bs-toggle="pill" data-bs-target="#pills-upcoming" type="button" role="tab">
                Upcoming <span class="badge bg-white text-primary ms-1 shadow-sm">{{ $upcoming->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold px-4" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab">
                Past History
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        
        <div class="tab-pane fade show active" id="pills-upcoming" role="tabpanel">
            @if($upcoming->isEmpty())
                <div class="text-center py-5 bg-light rounded-4">
                    <div class="text-muted opacity-50 mb-3"><i class="bi bi-calendar-x" style="font-size: 3rem;"></i></div>
                    <h5 class="fw-bold">No upcoming appointments</h5>
                    <p class="text-muted">You have no scheduled visits at the moment.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($upcoming as $app)
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        @if($app->status->value === 'confirmed')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Confirmed</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3 py-2">Pending</span>
                                        @endif
                                        <small class="text-muted fw-bold">#{{ str_pad($app->id, 5, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                    
                                    <h5 class="fw-bold mb-1">{{ $app->service }}</h5>
                                    <div class="d-flex align-items-center text-muted mb-4">
                                        <i class="bi bi-clock me-2"></i> {{ $app->appointment_date->format('F d, Y') }} at {{ $app->appointment_time }}
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" 
                                                class="btn btn-link text-danger text-decoration-none fw-bold p-0 small btn-cancel-hover"
                                                data-action="{{ route('appointments.cancel', $app->id) }}"
                                                data-status="{{ $app->status->value }}"
                                                onclick="openCancelModal(this)">
                                            Cancel Request
                                        </button>
                                        
                                        <a href="{{ route('appointments.index') }}" class="btn btn-light rounded-pill btn-sm fw-bold">
                                            View in Calendar
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-0 py-3">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Please arrive 10 mins early.</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="tab-pane fade" id="pills-history" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 text-secondary small text-uppercase">Date</th>
                                <th class="py-3 text-secondary small text-uppercase">Service</th>
                                <th class="py-3 text-secondary small text-uppercase">Status</th>
                                <th class="py-3 text-secondary small text-uppercase text-end pe-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $app)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ $app->appointment_date->format('M d, Y') }}
                                        <div class="small text-muted fw-normal">{{ $app->appointment_time }}</div>
                                    </td>
                                    <td>{{ $app->service }}</td>
                                    <td>
                                        @if($app->status->value == 'completed')
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Completed</span>
                                        @elseif($app->status->value == 'cancelled')
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">{{ ucfirst($app->status->value) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 text-muted small">
                                        {{ $app->cancellation_reason ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        No past appointments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-cancel-hover { transition: all 0.2s ease; }
    .btn-cancel-hover:hover { text-decoration: underline !important; transform: translateX(2px); }
</style>

<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="cancelForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Cancel Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="simpleCancelMsg" class="d-none">
                        <p class="text-muted">Are you sure you want to cancel this pending request? This action cannot be undone.</p>
                    </div>
                    
                    <div id="reasonCancelMsg" class="d-none">
                        <p class="text-muted mb-3">Since your appointment is already <strong>Confirmed</strong>, please provide a reason for cancellation.</p>
                        <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="e.g. Family emergency, feeling unwell..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Keep It</button>
                    <button type="submit" class="btn btn-danger rounded-pill fw-bold px-4">Yes, Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FIX: Function now accepts the button element ('this')
    function openCancelModal(button) {
        // Retrieve data from attributes
        const actionUrl = button.getAttribute('data-action');
        const status = button.getAttribute('data-status');

        const form = document.getElementById('cancelForm');
        const simpleMsg = document.getElementById('simpleCancelMsg');
        const reasonMsg = document.getElementById('reasonCancelMsg');
        const textArea = reasonMsg.querySelector('textarea');
        
        form.action = actionUrl;
        
        if(status === 'confirmed') {
            simpleMsg.classList.add('d-none');
            reasonMsg.classList.remove('d-none');
            textArea.required = true;
        } else {
            simpleMsg.classList.remove('d-none');
            reasonMsg.classList.add('d-none');
            textArea.required = false;
        }
        
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }
</script>
@endsection