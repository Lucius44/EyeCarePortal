@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                
                {{-- Sidebar --}}
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.calendar') }}" class="btn btn-primary mb-2 text-start">
                    <i class="bi bi-calendar-week me-2"></i> Calendar
                </a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-check-circle me-2"></i> Appointments
                </a>
                <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-clock-history me-2"></i> History
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    <i class="bi bi-people me-2"></i> Users List
                </a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Appointment Schedule</h2>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    {{-- Calendar Container --}}
                    <div id="adminCalendar" data-events="{{ json_encode($events) }}"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('adminCalendar');
        var eventsData = JSON.parse(calendarEl.getAttribute('data-events'));

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridDay' // Swapped list/week for Day view
            },
            height: 'auto',
            events: eventsData,

            // --- 1. FORCE 12-HOUR FORMAT (AM/PM) ON THE EVENTS ---
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short' // Result: "2:30 PM"
            },

            // --- 2. FORCE 12-HOUR FORMAT ON THE LEFT AXIS (Day View) ---
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: 'short' // Result: "9:00 AM"
            },

            // Optional: Better time range for the day view
            slotMinTime: '08:00:00', // Start calendar at 8 AM
            slotMaxTime: '18:00:00', // End at 6 PM
            
            eventClick: function(info) {
                // We also format the alert to show nice time
                var timeStr = info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                alert(
                    'Patient: ' + info.event.title + 
                    '\nTime: ' + timeStr +
                    '\nStatus: ' + info.event.extendedProps.status.value
                );
            }
        });
        
        calendar.render();
    });
</script>
@endsection