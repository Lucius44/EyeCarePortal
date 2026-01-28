@extends('layouts.app')

@section('content')
<div class="appointment-hero text-white mb-5 position-relative shadow-sm">
    <div class="hero-overlay"></div> 
    <div class="container position-relative z-2 py-5 text-center">
        <h1 class="display-5 fw-bold mb-3">Schedule your Appointment</h1>
        <p class="lead mb-4 text-white-50">Select a date below to book your consultation.</p>
        
        <div class="d-flex justify-content-center gap-3">
            <button id="btnDayView" class="btn btn-outline-light px-4 fw-bold rounded-pill border-2">
                <i class="bi bi-calendar-day me-2"></i>Day View
            </button>
            <button id="btnMonthView" class="btn btn-light px-4 fw-bold rounded-pill text-primary shadow-sm">
                <i class="bi bi-calendar-month me-2"></i>Month View
            </button>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                
                <div class="card-body p-4">
                    {{-- Legend --}}
                    <div class="d-flex align-items-center mb-3 text-muted small flex-wrap gap-3">
                        <div><span class="d-inline-block border me-1 rounded-circle" style="width: 15px; height: 15px; background: #fff;"></span> Available</div>
                        <div><span class="d-inline-block me-1 rounded-circle" style="width: 15px; height: 15px; background: #d1e7dd;"></span> Today</div>
                        <div><span class="d-inline-block me-1 rounded-circle" style="width: 15px; height: 15px; background: #ffeaea; border: 1px solid #dc3545;"></span> Closed</div>
                        <div><span class="d-inline-block me-1 rounded-circle" style="width: 15px; height: 15px; background: #e9ecef; border: 1px solid #6c757d;"></span> Full</div>
                    </div>
                    
                    {{-- Calendar Container --}}
                    <div id="calendar" 
                         data-verified="{{ Auth::user()->is_verified }}"
                         data-has-active="{{ $activeAppointment ? '1' : '0' }}"
                         data-daily-counts="{{ json_encode($dailyCounts) }}"
                         data-taken-slots="{{ json_encode($takenSlots) }}"
                         data-status="{{ json_encode($calendarStatus ?? []) }}" 
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 1. Booking Modal --}}
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">New Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="appointment_date" id="modalDateInput">
                    <div class="mb-3">
                        <label class="fw-bold text-secondary small text-uppercase">Selected Date</label>
                        <div id="displayDate" class="fs-4 text-primary fw-bold"></div>
                        <div id="slotsInfo" class="small text-muted mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Required</label>
                        <select name="service" class="form-select form-select-lg" required>
                            <option value="">-- Select Service --</option>
                            @foreach($services as $service)
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Preferred Time</label>
                        <select name="appointment_time" id="timeSlotSelect" class="form-select form-select-lg" required>
                            <option value="">-- Select Time --</option>
                            @php
                                $times = ['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'];
                            @endphp
                            @foreach($times as $time)
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-danger" id="timeSlotWarning" style="display:none;">
                            Some slots are disabled because they are already booked.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes / Symptoms (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe any specific eye issues..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 2. Today Modal (Same Day Prevention) --}}
<div class="modal fade" id="todayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-info text-white"> 
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Today's Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <h4 class="fw-bold text-secondary mb-3" id="todayDateDisplay"></h4>
                <p class="text-muted mb-4">We do not accept same-day appointments online.<br>Please call us at <strong>(123) 456-7890</strong> for urgent inquiries.</p>
                <div class="card bg-light border-0 p-3 rounded-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-3">Booked Slots Today</h6>
                    <div id="todaySlotsList" class="d-flex flex-wrap justify-content-center gap-2"></div>
                    <div id="todayNoSlots" class="text-success fw-bold small" style="display:none;"><i class="bi bi-check-circle me-1"></i> No appointments scheduled yet.</div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-info text-white px-5 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">I Understand</button>
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
                <h3 class="fw-bold">Active Appointment Found</h3>
                @if($activeAppointment)
                    <p class="text-muted mb-4">You have a <strong>{{ $activeAppointment->status->value }}</strong> appointment on:<br><span class="fs-5 text-dark fw-bold">{{ $activeAppointment->appointment_date->format('F d, Y') }} at {{ $activeAppointment->appointment_time }}</span><br>You cannot book a new appointment until this one is completed or cancelled.</p>
                    <div class="d-grid gap-2 col-10 mx-auto">
                        <a href="{{ route('my.appointments') }}" class="btn btn-primary rounded-pill fw-bold">View My Appointments</a>
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
                @else
                    <p class="text-muted mb-4">You already have a pending or confirmed appointment.</p>
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
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
                <h3 class="fw-bold">Verification Required</h3>
                <p class="text-muted mb-4">To ensure safety and security, you must verify your account before booking an appointment.<br>Please upload a valid ID in your settings.</p>
                <div class="d-grid gap-2 col-10 mx-auto">
                    <a href="{{ route('settings') }}" class="btn btn-danger rounded-pill fw-bold shadow-sm"><i class="bi bi-upload me-2"></i>Upload ID Now</a>
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5. Fully Booked Modal (For Default Limits) --}}
<div class="modal fade" id="fullyBookedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-5 text-center">
                <i class="bi bi-calendar-x text-danger display-1 mb-3"></i>
                <h3 class="fw-bold">Fully Booked</h3>
                <p class="text-muted mb-4">This date has reached its maximum capacity.<br>Please select a different date for your consultation.</p>
                <div class="d-grid gap-2 col-8 mx-auto">
                    <button type="button" class="btn btn-primary rounded-pill fw-bold" data-bs-dismiss="modal">Select Another Date</button>
                </div>
            </div>
        </div>
    </div>
</div>

@if($activeAppointment && $activeAppointment->status->value === 'pending')
<div class="modal fade" id="pendingCancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        const isVerified = calendarEl.getAttribute('data-verified') == '1';
        const hasActiveAppointment = calendarEl.getAttribute('data-has-active') == '1'; 
        const dailyCounts = JSON.parse(calendarEl.getAttribute('data-daily-counts') || '{}'); 
        const takenSlots = JSON.parse(calendarEl.getAttribute('data-taken-slots') || '{}');
        // --- NEW: Read Status Data ---
        const calendarStatus = JSON.parse(calendarEl.getAttribute('data-status') || '{}');

        function getLocalYMD(dateObj) {
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        let calendarEvents = [];
        
        // 1. Generate "Booked" Visuals for Month View (Legacy logic kept for visual density)
        for (const [date, count] of Object.entries(dailyCounts)) {
            // Only add these badges if the day isn't CLOSED or FULL via the new status
            let status = calendarStatus[date];
            if (status !== 'closed' && status !== 'full') {
                let color = '#198754'; 
                let title = count + ' Booked';
                if (count >= 3) color = '#ffc107'; 
                
                calendarEvents.push({
                    title: title,
                    start: date,
                    allDay: true,
                    backgroundColor: color,
                    borderColor: color,
                    textColor: count >= 3 ? '#000' : '#fff',
                    classNames: ['booking-badge'] 
                });
            }
        }

        // 2. Generate Time Slot Blocks (for Day View)
        for (const [date, times] of Object.entries(takenSlots)) {
            times.forEach(timeStr => {
                let timeParts = timeStr.match(/(\d+):(\d+) (\w+)/);
                if(timeParts) {
                    let hours = parseInt(timeParts[1]);
                    let minutes = timeParts[2];
                    let amp = timeParts[3];
                    if (amp === "PM" && hours < 12) hours += 12;
                    if (amp === "AM" && hours === 12) hours = 0;
                    let isoTime = hours.toString().padStart(2, '0') + ':' + minutes + ':00';
                    
                    calendarEvents.push({
                        title: 'Booked',
                        start: date + 'T' + isoTime,
                        end: date + 'T' + (hours + 1).toString().padStart(2, '0') + ':' + minutes + ':00',
                        backgroundColor: '#e9ecef',
                        borderColor: '#dee2e6',
                        textColor: '#6c757d',
                        classNames: ['booked-slot-event']
                    });
                }
            });
        }

        let today = new Date();
        today.setHours(0,0,0,0);
        let maxBookableDate = new Date(today);
        maxBookableDate.setDate(today.getDate() + 31);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            events: calendarEvents, 
            
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'today prev,next'
            },
            
            validRange: {
                start: today,
                end: maxBookableDate
            },

            slotDuration: '01:00:00', 
            slotMinTime: '09:00:00',
            slotMaxTime: '18:00:00',
            allDaySlot: false,
            expandRows: true,

            // --- MERGED LOGIC: Use new Status for Classes ---
            dayCellClassNames: function(arg) {
                let dateStr = getLocalYMD(arg.date);
                let status = calendarStatus[dateStr];

                // 1. Closed by Admin
                if (status === 'closed') return ['day-closed'];
                
                // 2. Full (Custom limit or Default 5)
                if (status === 'full') return ['day-full'];

                return [];
            },

            dateClick: function(info) {
                // 1. Check User Status
                if (hasActiveAppointment) {
                    new bootstrap.Modal(document.getElementById('activeAppointmentModal')).show();
                    return;
                }
                if (!isVerified) {
                    new bootstrap.Modal(document.getElementById('unverifiedModal')).show();
                    return; 
                }

                let dateStr = info.dateStr;
                if(dateStr.includes('T')) dateStr = dateStr.split('T')[0]; 

                // 2. Check Admin Status (Closed/Full) - NEW
                let status = calendarStatus[dateStr];
                if (status === 'closed') return; // Do nothing (Visuals already indicate closed)
                if (status === 'full') {
                     new bootstrap.Modal(document.getElementById('fullyBookedModal')).show();
                     return;
                }

                let clickedDate = new Date(dateStr);
                clickedDate.setHours(0,0,0,0); 
                let isToday = clickedDate.getTime() === today.getTime();

                // 3. Check "Today"
                if (isToday) {
                    let takenToday = takenSlots[dateStr] || [];
                    openTodayModal(clickedDate, takenToday); 
                    return;
                }

                openBookingModal(dateStr, clickedDate);
            }
        });

        calendar.render();

        function openTodayModal(dateObj, takenArray) {
            document.getElementById('todayDateDisplay').innerText = dateObj.toLocaleDateString(undefined, { 
                weekday: 'long', month: 'long', day: 'numeric' 
            });
            const listContainer = document.getElementById('todaySlotsList');
            const emptyMsg = document.getElementById('todayNoSlots');
            listContainer.innerHTML = ''; 
            if (takenArray.length === 0) {
                emptyMsg.style.display = 'block';
            } else {
                emptyMsg.style.display = 'none';
                takenArray.forEach(time => {
                    let badge = document.createElement('span');
                    badge.className = 'badge bg-secondary opacity-75 fs-6 fw-normal py-2 px-3 rounded-pill';
                    badge.innerText = time;
                    listContainer.appendChild(badge);
                });
            }
            new bootstrap.Modal(document.getElementById('todayModal')).show();
        }

        function openBookingModal(dateStr, dateObj) {
            document.getElementById('modalDateInput').value = dateStr;
            document.getElementById('displayDate').innerText = dateObj.toLocaleDateString(undefined, { 
                weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' 
            });
            let count = dailyCounts[dateStr] || 0;
            document.getElementById('slotsInfo').innerText = count + " booked"; // Removed " / 5" because limit varies now
            let select = document.getElementById('timeSlotSelect');
            let taken = takenSlots[dateStr] || [];
            let options = select.options;
            let hasDisabled = false;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === "") continue;
                if (taken.includes(options[i].value)) {
                    options[i].disabled = true;
                    options[i].innerText = options[i].value + " (Booked)";
                    hasDisabled = true;
                } else {
                    options[i].disabled = false;
                    options[i].innerText = options[i].value;
                }
            }
            document.getElementById('timeSlotWarning').style.display = hasDisabled ? 'block' : 'none';
            select.value = "";
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }

        document.getElementById('btnDayView').addEventListener('click', function() {
            calendar.changeView('timeGridDay');
            this.classList.remove('btn-outline-light');
            this.classList.add('btn-light', 'text-primary');
            let monthBtn = document.getElementById('btnMonthView');
            monthBtn.classList.remove('btn-light', 'text-primary');
            monthBtn.classList.add('btn-outline-light', 'text-white');
        });

        document.getElementById('btnMonthView').addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
            this.classList.remove('btn-outline-light', 'text-white');
            this.classList.add('btn-light', 'text-primary');
            let dayBtn = document.getElementById('btnDayView');
            dayBtn.classList.remove('btn-light', 'text-primary');
            dayBtn.classList.add('btn-outline-light');
        });
    });
</script>

<style>
    /* HERO SECTION */
    .appointment-hero {
        background-color: #0d6efd; 
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
        background: linear-gradient(rgba(13, 110, 253, 0.8), rgba(0, 0, 0, 0.4));
    }
    
    /* CALENDAR OVERRIDES */
    .fc-toolbar-title { font-size: 1.75rem !important; font-weight: 700; color: #333; }
    .fc-button-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; text-transform: capitalize; font-weight: 500; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #eff2f7; }
    .fc-day-today { background-color: #d1e7dd !important; color: #0f5132 !important; font-weight: bold; }
    
    /* NEW STATUS STYLES (Merged) */
    .day-closed {
        background-color: #ffeaea !important; /* Light Red */
        cursor: not-allowed !important;
        position: relative;
    }
    .day-closed::after {
        content: 'CLOSED';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.75rem;
        font-weight: 800;
        color: #dc3545;
        opacity: 0.5;
        letter-spacing: 1px;
    }

    .day-full {
        background-color: #e9ecef !important; /* Darker Gray */
        cursor: not-allowed !important;
        position: relative;
    }
    .day-full::after {
        content: 'FULL';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.75rem;
        font-weight: 800;
        color: #6c757d;
        opacity: 0.5;
        letter-spacing: 1px;
    }

    /* Standard Interactive Days */
    .fc-daygrid-day:not(.day-closed):not(.day-full):not(.fc-day-today) { cursor: pointer; transition: background-color 0.2s ease; }
    .fc-daygrid-day:not(.day-closed):not(.day-full):not(.fc-day-today):hover { background-color: #e7f1ff !important; }
    
    .fc-timegrid-slot-lane:hover { background-color: #e7f1ff !important; cursor: pointer; }
    .booking-badge { font-size: 0.75rem; border-radius: 4px; padding: 1px 2px; margin-top: 2px; text-align: center; border: none !important; }
    
    .fc-dayGridMonth-view .booked-slot-event { display: none !important; }
    .booked-slot-event { border: none !important; pointer-events: none; }
</style>
@endsection