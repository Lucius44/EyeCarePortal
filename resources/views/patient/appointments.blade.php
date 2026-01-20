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
            
            {{-- Error/Success Alerts --}}
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
                
                {{-- BLOCKING OVERLAY: If user has an active appointment --}}
                @if($hasActiveAppointment)
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 z-3 d-flex flex-column justify-content-center align-items-center text-center">
                    <div class="bg-white p-5 rounded-4 shadow-lg border">
                        <i class="bi bi-calendar-check text-success display-1 mb-3"></i>
                        <h3 class="fw-bold">Appointment Pending or Confirmed</h3>
                        <p class="text-muted">You already have an active appointment.<br>You cannot book another one until your current appointment is completed or cancelled.</p>
                        <a href="{{ route('my.appointments') }}" class="btn btn-primary px-4 rounded-pill mt-2">View My Appointments</a>
                    </div>
                </div>
                @endif

                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 text-muted small flex-wrap gap-3">
                        <div><span class="d-inline-block border me-1 rounded-circle" style="width: 15px; height: 15px; background: #fff;"></span> Available</div>
                        <div><span class="d-inline-block me-1 rounded-circle" style="width: 15px; height: 15px; background: #d1e7dd;"></span> Today</div>
                        <div><span class="d-inline-block me-1 rounded-circle" style="width: 15px; height: 15px; background: #dc3545;"></span> Fully Booked (5/5)</div>
                    </div>
                    
                    <div id="calendar" 
                         data-verified="{{ Auth::user()->is_verified }}"
                         data-daily-counts="{{ json_encode($dailyCounts) }}"
                         data-taken-slots="{{ json_encode($takenSlots) }}"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 1. BOOKING MODAL --}}
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

{{-- 2. NEW: TODAY'S SCHEDULE INFO MODAL --}}
<div class="modal fade" id="todayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-info text-white"> 
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Today's Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                
                <h4 class="fw-bold text-secondary mb-3" id="todayDateDisplay"></h4>
                
                <p class="text-muted mb-4">
                    We do not accept same-day appointments online.<br>
                    Please call us at <strong>(123) 456-7890</strong> for urgent inquiries.
                </p>

                <div class="card bg-light border-0 p-3 rounded-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-3">Booked Slots Today</h6>
                    
                    <div id="todaySlotsList" class="d-flex flex-wrap justify-content-center gap-2"></div>
                    
                    <div id="todayNoSlots" class="text-success fw-bold small" style="display:none;">
                        <i class="bi bi-check-circle me-1"></i> No appointments scheduled yet.
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-info text-white px-5 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        // Read attributes safely
        const isVerified = calendarEl.getAttribute('data-verified') == '1';
        const dailyCounts = JSON.parse(calendarEl.getAttribute('data-daily-counts') || '{}'); 
        const takenSlots = JSON.parse(calendarEl.getAttribute('data-taken-slots') || '{}');   

        // CONVERT COUNTS TO EVENTS
        let countEvents = [];
        for (const [date, count] of Object.entries(dailyCounts)) {
            let color = '#198754'; 
            let title = count + ' Booked';
            
            if (count >= 3 && count < 5) color = '#ffc107'; 
            if (count >= 5) {
                color = '#dc3545';
                title = 'FULL';
            }

            countEvents.push({
                title: title,
                start: date,
                allDay: true,
                backgroundColor: color,
                borderColor: color,
                textColor: count >= 3 && count < 5 ? '#000' : '#fff',
                classNames: ['booking-badge'] 
            });
        }

        // DATE LIMITS
        let today = new Date();
        today.setHours(0,0,0,0);
        let maxBookableDate = new Date(today);
        maxBookableDate.setDate(today.getDate() + 30);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            events: countEvents, 
            
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'today prev,next'
            },
            
            validRange: {
                start: today,
                end: maxBookableDate
            },

            // DAY VIEW CONFIG
            slotDuration: '01:00:00', 
            slotMinTime: '09:00:00',
            slotMaxTime: '18:00:00',
            allDaySlot: false,
            expandRows: true,

            // Style Logic
            dayCellClassNames: function(arg) {
                let dateStr = arg.date.toISOString().split('T')[0];
                if (dailyCounts[dateStr] >= 5) {
                    return ['date-full']; 
                }
                return [];
            },

            // CLICK INTERACTION
            dateClick: function(info) {
                if (!isVerified) {
                    alert('Your account is not verified yet. Please upload your ID in settings.');
                    return; 
                }

                let dateStr = info.dateStr;
                if(dateStr.includes('T')) dateStr = dateStr.split('T')[0]; 

                let clickedDate = new Date(dateStr);
                clickedDate.setHours(0,0,0,0); 
                
                let isToday = clickedDate.getTime() === today.getTime();

                // 3. UPDATED LOGIC FOR TODAY
                if (isToday) {
                    let takenToday = takenSlots[dateStr] || [];
                    openTodayModal(clickedDate, takenToday); // <--- Call new modal function
                    return;
                }

                // FULLY BOOKED CHECK
                if (dailyCounts[dateStr] >= 5) {
                    alert('This date is fully booked (5/5 appointments). Please select another date.');
                    return;
                }

                openBookingModal(dateStr, clickedDate);
            }
        });

        calendar.render();

        // 4. HELPER FUNCTION FOR TODAY'S MODAL
        function openTodayModal(dateObj, takenArray) {
            // Set Date Title (e.g., "Monday, January 20")
            document.getElementById('todayDateDisplay').innerText = dateObj.toLocaleDateString(undefined, { 
                weekday: 'long', month: 'long', day: 'numeric' 
            });

            const listContainer = document.getElementById('todaySlotsList');
            const emptyMsg = document.getElementById('todayNoSlots');
            
            listContainer.innerHTML = ''; // Clear previous

            if (takenArray.length === 0) {
                emptyMsg.style.display = 'block';
            } else {
                emptyMsg.style.display = 'none';
                // Loop through taken times and create badges
                takenArray.forEach(time => {
                    let badge = document.createElement('span');
                    // Style: Grey, rounded pill, slightly transparent
                    badge.className = 'badge bg-secondary opacity-75 fs-6 fw-normal py-2 px-3 rounded-pill';
                    badge.innerText = time;
                    listContainer.appendChild(badge);
                });
            }

            var myModal = new bootstrap.Modal(document.getElementById('todayModal'));
            myModal.show();
        }

        // Helper to open booking modal
        function openBookingModal(dateStr, dateObj) {
            document.getElementById('modalDateInput').value = dateStr;
            document.getElementById('displayDate').innerText = dateObj.toLocaleDateString(undefined, { 
                weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' 
            });

            let count = dailyCounts[dateStr] || 0;
            document.getElementById('slotsInfo').innerText = count + " / 5 slots filled";

            let select = document.getElementById('timeSlotSelect');
            let taken = takenSlots[dateStr] || [];
            let options = select.options;
            let hasDisabled = false;

            for (let i = 0; i < options.length; i++) {
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

            var myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            myModal.show();
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
    /* Hero Section */
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

    /* Calendar Base Styles */
    .fc-toolbar-title { font-size: 1.75rem !important; font-weight: 700; color: #333; }
    .fc-button-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; text-transform: capitalize; font-weight: 500; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #eff2f7; }

    /* TODAY'S DATE */
    .fc-day-today { background-color: #d1e7dd !important; color: #0f5132 !important; font-weight: bold; }

    /* DISABLED / FULL DATES */
    .fc-day-disabled, .date-full {
        background-color: #f8f9fa !important;
        opacity: 1 !important;
        cursor: not-allowed !important; 
    }
    .date-full {
        background-color: #ffeaea !important; /* Light red */
    }
    
    .fc-day-disabled .fc-daygrid-day-number { visibility: hidden; }

    /* HOVER EFFECT */
    .fc-daygrid-day:not(.fc-day-disabled):not(.date-full) {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .fc-daygrid-day:not(.fc-day-disabled):not(.date-full):hover {
        background-color: #e7f1ff !important;
    }

    /* Event Styling */
    .booking-badge {
        font-size: 0.75rem;
        border-radius: 4px;
        padding: 1px 2px;
        margin-top: 2px;
        text-align: center;
        border: none !important;
    }
</style>
@endsection