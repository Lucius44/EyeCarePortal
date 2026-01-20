@extends('layouts.app')

@section('content')
<div class="appointment-hero text-white mb-5 position-relative shadow-sm">
    <div class="hero-overlay"></div> 
    <div class="container position-relative z-2 py-5 text-center">
        <h1 class="display-5 fw-bold mb-3">Schedule your Appointment</h1>
        <p class="lead mb-4 text-white-50">Select a date below to book your consultation.</p>
        
        <div class="d-flex justify-content-center gap-3">
            <button id="btnDayView" class="btn btn-light px-4 fw-bold rounded-pill shadow-sm">
                <i class="bi bi-calendar-day me-2"></i>Day View
            </button>
            <button id="btnMonthView" class="btn btn-outline-light px-4 fw-bold rounded-pill text-white border-2">
                <i class="bi bi-calendar-month me-2"></i>Month View
            </button>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 text-muted small">
                        <span class="d-inline-block bg-white border me-1" style="width: 15px; height: 15px;"></span> Available
                        <span class="d-inline-block ms-3 me-1" style="width: 15px; height: 15px; background: #e9ecef;"></span> Unavailable (Past or >30 days out)
                    </div>
                    <div id="calendar" data-verified="{{ Auth::user()->is_verified }}"></div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <select name="appointment_time" class="form-select form-select-lg" required>
                            <option value="">-- Select Time --</option>
                            <option value="09:00 AM">09:00 AM</option>
                            <option value="10:00 AM">10:00 AM</option>
                            <option value="11:00 AM">11:00 AM</option>
                            <option value="01:00 PM">01:00 PM</option>
                            <option value="02:00 PM">02:00 PM</option>
                            <option value="03:00 PM">03:00 PM</option>
                            <option value="04:00 PM">04:00 PM</option>
                            <option value="05:00 PM">05:00 PM</option>
                        </select>
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

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        const isVerified = calendarEl.getAttribute('data-verified') == '1';

        // 1. ROBUST DATE SETUP (Local Time)
        // We use setHours(0,0,0,0) to compare dates without worrying about time
        let today = new Date();
        today.setHours(0,0,0,0);
        
        // Calculate the maximum allowed booking date (Today + 30 days)
        // Logic: You can book FROM today UNTIL 30 days in the future.
        let maxBookableDate = new Date(today);
        maxBookableDate.setDate(today.getDate() + 30);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'today prev,next'
            },
            
            height: 'auto',
            selectable: true, 
            
            // 2. GREY OUT INVALID DATES
            dayCellClassNames: function(arg) {
                // Parse the cell date ensuring local time logic to prevent timezone shifts
                let cellDate = new Date(arg.date);
                cellDate.setHours(0,0,0,0);

                // Disable if in the past OR more than 30 days in future
                if (cellDate < today || cellDate > maxBookableDate) {
                    return ['date-disabled'];
                }
                return [];
            },

            // Interaction logic
            dateClick: function(info) {
                if (!isVerified) {
                    alert('Your account is not verified yet. Please upload your ID in settings to enable booking.');
                    return; 
                }

                // TIMEZONE SAFE PARSING
                // info.dateStr is "YYYY-MM-DD". splitting handles local construction better than new Date(string)
                const parts = info.dateStr.split('-');
                const clickedDate = new Date(parts[0], parts[1] - 1, parts[2]); // Year, Month (0-indexed), Day
                clickedDate.setHours(0,0,0,0);

                // Constraint Check: Past Dates
                if (clickedDate < today) {
                    // It's disabled visually, but if they somehow click it:
                    return; 
                }
                
                // Constraint Check: > 30 Days
                if (clickedDate > maxBookableDate) {
                    // It's disabled visually
                    return;
                }

                // Proceed with Booking
                document.getElementById('modalDateInput').value = info.dateStr;
                
                // Nice formatting
                document.getElementById('displayDate').innerText = clickedDate.toLocaleDateString(undefined, { 
                    weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' 
                });
                
                var myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                myModal.show();
            }
        });

        calendar.render();

        // Button Event Listeners
        document.getElementById('btnDayView').addEventListener('click', function() {
            calendar.changeView('timeGridDay');
            this.classList.remove('btn-outline-light');
            this.classList.add('btn-light');
            
            let monthBtn = document.getElementById('btnMonthView');
            monthBtn.classList.remove('btn-light');
            monthBtn.classList.add('btn-outline-light');
            monthBtn.classList.add('text-white');
        });

        document.getElementById('btnMonthView').addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
            this.classList.remove('btn-outline-light', 'text-white');
            this.classList.add('btn-light');
            
            let dayBtn = document.getElementById('btnDayView');
            dayBtn.classList.remove('btn-light');
            dayBtn.classList.add('btn-outline-light');
        });
    });
</script>

<style>
    /* Hero Section Styling */
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

    /* Calendar Visual Tweaks */
    .fc-toolbar-title {
        font-size: 1.75rem !important;
        font-weight: 700;
        color: #333;
    }
    
    .fc-button-primary {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        text-transform: capitalize;
        font-weight: 500;
    }

    .fc-day-today {
        background-color: #f0f7ff !important;
    }
    
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #eff2f7;
    }

    /* DISABLED DATE STYLING */
    .date-disabled {
        background-color: #f8f9fa !important; /* Lighter grey */
        color: #ced4da !important;            /* Very muted text */
        cursor: not-allowed !important;
        pointer-events: none;                 /* Prevent clicks */
    }
</style>
@endsection