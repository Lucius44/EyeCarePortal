@extends('layouts.app')

@section('content')
{{-- Load the separated JS --}}
@vite(['resources/js/appointments.js'])

{{-- Hero Section --}}
<div class="appointment-hero text-white mb-4 position-relative shadow-sm">
    <div class="hero-overlay"></div> 
    <div class="container position-relative z-2 py-4 py-md-5 text-center">
        <h1 class="display-5 fw-bold mb-2">Book Appointment</h1>
        <p class="lead mb-0 text-white-50 fs-6">Select a date to schedule your visit.</p>
        
        {{-- Desktop View Toggles --}}
        <div class="d-none d-md-flex justify-content-center gap-3 mt-4">
            <button id="btnDayView" class="btn btn-outline-light px-4 fw-bold rounded-pill border-2 transition-btn">
                <i class="bi bi-calendar-day me-2"></i>Day View
            </button>
            <button id="btnMonthView" class="btn btn-light px-4 fw-bold rounded-pill text-primary shadow-sm transition-btn">
                <i class="bi bi-calendar-month me-2"></i>Month View
            </button>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-4 mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- MOBILE VIEW: Date Strip & Slot Grid        --}}
            {{-- ========================================== --}}
            <div class="d-md-none">
                {{-- 1. Date Strip --}}
                <h6 class="text-uppercase text-muted small fw-bold mb-3 px-1">Select Date</h6>
                <div class="date-strip-wrapper mb-4">
                    <div class="d-flex gap-2" id="mobileDateStrip">
                        {{-- JS will populate this --}}
                    </div>
                </div>

                {{-- 2. Time Slots Grid --}}
                <div id="mobileTimeSection" style="display: none;">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3 px-1">
                        Available Slots for <span id="mobileSelectedDateText" style="color: var(--accent-color);"></span>
                    </h6>
                    <div class="row g-2" id="mobileTimeGrid">
                        {{-- JS will populate this --}}
                    </div>
                    <div id="mobileNoSlots" class="text-center py-5 bg-light rounded-4 text-muted" style="display:none;">
                        <i class="bi bi-calendar-x display-1 text-secondary opacity-25"></i>
                        <p class="mt-3 mb-0">No available slots for this date.</p>
                    </div>
                </div>

                {{-- Initial Prompt State --}}
                <div id="mobileInitialPrompt" class="text-center py-5">
                    <i class="bi bi-hand-index-thumb text-primary opacity-25 display-1"></i>
                    <p class="text-muted mt-3">Tap a date above to see availability.</p>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- DESKTOP VIEW: FullCalendar                 --}}
            {{-- ========================================== --}}
            <div class="d-none d-md-block card shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-4">
                    {{-- Legend --}}
                    <div class="d-flex align-items-center mb-4 text-muted small flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2"><span class="d-inline-block border rounded-circle" style="width: 12px; height: 12px; background: #fff;"></span> Available</div>
                        <div class="d-flex align-items-center gap-2"><span class="d-inline-block rounded-circle" style="width: 12px; height: 12px; background: #eff6ff; border: 1px solid var(--accent-color);"></span> Today</div>
                        <div class="d-flex align-items-center gap-2"><span class="d-inline-block rounded-circle" style="width: 12px; height: 12px; background: #fff5f5; border: 1px solid #dc3545;"></span> Closed</div>
                        <div class="d-flex align-items-center gap-2"><span class="d-inline-block rounded-circle" style="width: 12px; height: 12px; background: #f8fafc; border: 1px solid #94a3b8;"></span> Full</div>
                        <div class="d-flex align-items-center gap-2"><span class="d-inline-block rounded-circle" style="width: 12px; height: 12px; background: #f1f5f9; border: 1px dashed #64748b;"></span> Cutoff (8PM)</div>
                    </div>
                    
                    {{-- Calendar Container --}}
                    <div id="calendar"></div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Data Store for JS --}}
<div id="calendarData" 
     data-verified="{{ Auth::user()->is_verified }}"
     data-has-active="{{ $activeAppointment ? '1' : '0' }}"
     data-server-hour="{{ \Carbon\Carbon::now('Asia/Manila')->hour }}"
     data-server-tomorrow="{{ \Carbon\Carbon::tomorrow('Asia/Manila')->format('Y-m-d') }}"
     data-daily-counts="{{ json_encode($dailyCounts) }}"
     data-taken-slots="{{ json_encode($takenSlots) }}"
     data-status="{{ json_encode($calendarStatus ?? []) }}" 
></div>

{{-- 1. Booking Modal --}}
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header text-white" style="background-color: var(--primary-color);">
                    <h5 class="modal-title fw-bold">Confirm Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="appointment_date" id="modalDateInput">
                    
                    <div class="d-flex align-items-center bg-light p-3 rounded-3 mb-4 border border-light-subtle">
                        <div class="me-3">
                            <div class="bg-white p-2 rounded shadow-sm text-center" style="min-width: 60px;">
                                <div id="summaryMonth" class="small text-uppercase fw-bold text-danger" style="font-size: 0.7rem;"></div>
                                <div id="summaryDay" class="h4 mb-0 fw-bold" style="color: var(--primary-color);"></div>
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase">Appointment Time</div>
                            <div id="summaryTime" class="h5 mb-0 fw-bold" style="color: var(--accent-color);"></div>
                            <input type="hidden" name="appointment_time" id="modalTimeInput">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Required</label>
                        <select name="service" class="form-select form-select-lg bg-light border-0" required id="serviceSelect">
                            <option value="">-- Select Service --</option>
                            @foreach($services as $service)
                                {{-- FIX: $service is a string, NOT an object --}}
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- "Other" Service Input --}}
                    <div class="mb-3 d-none" id="otherServiceDiv">
                        <label class="form-label fw-bold">Please Specify Service</label>
                        <input type="text" name="service_other" id="serviceInput" class="form-control bg-light border-0" placeholder="Type your required service...">
                    </div>

                    <div class="mb-3" id="desktopTimeSelectWrapper">
                        <label class="form-label fw-bold">Select Time</label>
                        <select id="desktopTimeSelect" class="form-select form-select-lg" onchange="document.getElementById('modalTimeInput').value = this.value; updateSummaryTime(this.value);">
                            <option value="">-- Select Time --</option>
                            @php
                                $times = ['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'];
                            @endphp
                            @foreach($times as $time)
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-danger" id="timeSlotWarning" style="display:none;">
                            Some slots are unavailable.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes / Symptoms</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white px-4 rounded-pill fw-bold shadow-sm" style="background-color: var(--primary-color);">Confirm Booking</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 2. Today Modal --}}
<div class="modal fade" id="todayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--accent-color);">
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Today's Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <h4 class="fw-bold mb-3" style="color: var(--primary-color);" id="todayDateDisplay"></h4>
                <p class="text-muted mb-4">We do not accept same-day appointments online.<br>Please call us at <strong>+63 945 826 4969</strong> for urgent inquiries.</p>
                <div class="card bg-light border-0 p-3 rounded-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-3">Booked Slots Today</h6>
                    <div id="todaySlotsList" class="d-flex flex-wrap justify-content-center gap-2"></div>
                    <div id="todayNoSlots" class="text-success fw-bold small" style="display:none;">
                        <i class="bi bi-check-circle me-1"></i> No appointments scheduled yet.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn text-white px-5 rounded-pill fw-bold shadow-sm" style="background-color: var(--accent-color);" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

{{-- 3. Active Appointment Modal --}}
<div class="modal fade" id="activeAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-5 text-center">
                <i class="bi bi-calendar-check text-warning display-1 mb-3"></i>
                <h3 class="fw-bold">Active Appointment</h3>
                @if($activeAppointment)
                    <p class="text-muted mb-4">You have a <strong>{{ $activeAppointment->status->value }}</strong> appointment on:<br>
                        <span class="fs-5 fw-bold" style="color: var(--primary-color);">{{ $activeAppointment->appointment_date->format('F d, Y') }} at {{ $activeAppointment->appointment_time }}</span>
                    </p>
                    <div class="d-grid gap-2 col-10 mx-auto">
                        <a href="{{ route('my.appointments') }}" class="btn text-white rounded-pill fw-bold" style="background-color: var(--primary-color);">View Details</a>
                        
                        @if($activeAppointment->status->value === 'pending')
                            <button type="button" class="btn btn-outline-danger rounded-pill fw-bold w-100" data-bs-toggle="modal" data-bs-target="#pendingCancelModal">Cancel Request</button>
                        @else
                            <button type="button" class="btn btn-outline-danger rounded-pill fw-bold w-100" data-bs-toggle="collapse" data-bs-target="#cancelReasonCollapse">Cancel Existing Appointment</button>
                            <div class="collapse mt-3" id="cancelReasonCollapse">
                                <form action="{{ route('appointments.cancel', $activeAppointment->id) }}" method="POST" class="text-start p-3 bg-light rounded-3">
                                    @csrf
                                    <label class="small fw-bold mb-1">Reason for cancellation:</label>
                                    <textarea name="cancellation_reason" class="form-control mb-2" rows="2" required placeholder="Why are you cancelling?"></textarea>
                                    <button type="submit" class="btn btn-danger btn-sm w-100">Confirm Cancellation</button>
                                </form>
                            </div>
                        @endif

                        <button type="button" class="btn btn-light rounded-pill mt-2" data-bs-dismiss="modal">Close & View Calendar</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 4. Unverified Modal --}}
<div class="modal fade" id="unverifiedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-5 text-center">
                <i class="bi bi-shield-lock text-danger display-1 mb-3"></i>
                <h3 class="fw-bold">ID Verification Required</h3>
                <p class="text-muted mb-4">To ensure clinic security, please upload a valid ID before booking your first appointment.</p>
                <div class="d-grid gap-2 col-10 mx-auto">
                    <a href="{{ route('settings') }}" class="btn btn-danger rounded-pill fw-bold shadow-sm"><i class="bi bi-upload me-2"></i>Upload ID</a>
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5. Pending Cancel Modal --}}
@if($activeAppointment && $activeAppointment->status->value === 'pending')
<div class="modal fade" id="pendingCancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-3">Cancel Request?</h5>
                <p class="text-muted mb-4">Are you sure you want to remove this appointment request?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#activeAppointmentModal">Keep It</button>
                    <form action="{{ route('appointments.cancel', $activeAppointment->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Yes, Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<style>
    /* HERO SECTION */
    .appointment-hero {
        background-color: var(--primary-color); 
        background-image: url('/images/sixeyes.png'); 
        background-size: cover;
        background-position: center;
        border-radius: 1rem;
        margin-top: -1.5rem;
        overflow: hidden;
    }
    .hero-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.6));
    }
    .transition-btn { transition: all 0.2s ease; }
    .transition-btn:hover { transform: translateY(-2px); }

    /* --- MOBILE STYLES --- */
    .date-strip-wrapper { overflow-x: auto; padding-bottom: 10px; -ms-overflow-style: none; scrollbar-width: none; }
    .date-strip-wrapper::-webkit-scrollbar { display: none; }
    .date-card {
        min-width: 65px; height: 80px; background: white; border: 1px solid #e2e8f0; border-radius: 16px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s ease; position: relative;
    }
    .date-card.active {
        background: var(--primary-color); color: white; border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.3); transform: translateY(-2px);
    }
    .date-card.disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; border-color: #f1f5f9; }
    .date-card.is-today { border-color: var(--accent-color); background: #eff6ff; color: var(--accent-color); }
    .day-name { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; }
    .day-num { font-size: 1.25rem; font-weight: 800; line-height: 1; margin-top: 2px; }
    .status-dot { position: absolute; bottom: 6px; width: 6px; height: 6px; border-radius: 50%; }
    .status-dot.closed { background-color: #dc3545; }
    .status-dot.full { background-color: #94a3b8; }
    .time-slot {
        padding: 12px 5px; text-align: center; border: 1px solid #e2e8f0; border-radius: 12px;
        background: white; font-weight: 600; font-size: 0.9rem; color: #334155;
        cursor: pointer; transition: all 0.2s;
    }
    .time-slot.available:active { background: #eff6ff; border-color: var(--accent-color); }
    .time-slot.disabled { background: #f1f5f9; color: #cbd5e1; text-decoration: line-through; border-color: transparent; cursor: default; }

    /* --- DESKTOP CALENDAR STYLES --- */
    :root {
        --fc-border-color: #f1f5f9;
        --fc-button-text-color: #fff;
        --fc-button-bg-color: var(--primary-color);
        --fc-button-border-color: var(--primary-color);
        --fc-button-hover-bg-color: #1e293b;
        --fc-button-hover-border-color: #1e293b;
        --fc-button-active-bg-color: #1e293b;
        --fc-button-active-border-color: #1e293b;
        --fc-today-bg-color: #eff6ff;
    }
    .fc-toolbar-title { font-size: 1.5rem !important; font-weight: 800; color: var(--primary-color); letter-spacing: -0.5px; }
    .fc-button { text-transform: capitalize; font-weight: 600; padding: 0.4rem 1rem !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s ease; }
    .fc-button:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .fc-theme-standard td, .fc-theme-standard th { border: 1px solid #f1f5f9; }
    .fc-daygrid-day-frame { transition: background-color 0.2s ease; min-height: 100px; }
    .fc-daygrid-day:not(.day-closed):not(.day-full):not(.fc-day-today):hover .fc-daygrid-day-frame { background-color: #f8fafc; cursor: pointer; }
    
    .fc-day-today { cursor: pointer !important; transition: background-color 0.2s; }
    .fc-day-today:hover { background-color: #dbeafe !important; }

    .day-closed { background-color: #fff5f5 !important; cursor: not-allowed !important; position: relative; }
    .day-closed::after { content: 'CLOSED'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.65rem; font-weight: 800; color: #dc3545; opacity: 0.5; letter-spacing: 1px; }
    
    .day-full { background-color: #f8fafc !important; cursor: not-allowed !important; position: relative; }
    .day-full::after { content: 'FULL'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.65rem; font-weight: 800; color: #94a3b8; opacity: 0.5; letter-spacing: 1px; }

    /* --- NEW: CUTOFF STYLE --- */
    .day-cutoff { background-color: #f1f5f9 !important; cursor: not-allowed !important; position: relative; border-color: #e2e8f0 !important; }
    .day-cutoff::after { content: 'CUTOFF'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.65rem; font-weight: 800; color: #64748b; opacity: 0.5; letter-spacing: 1px; }

    .booking-badge { 
        font-size: 0.75rem; border: none !important; padding: 2px 6px; margin-top: 4px; 
        border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.1s;
        pointer-events: none;
    }
    .fc-dayGridMonth-view .booked-slot-event { display: none !important; }

    .fc-timegrid-slot-lane:hover {
        background-color: #f1f5f9 !important; 
        cursor: pointer;
        transition: background-color 0.2s;
    }
</style>
@endsection