@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mb-2 text-start">
                    📅 Calendar
                </a>
                <a href="#" class="btn btn-outline-secondary mb-2 text-start disabled">
                    👥 Users List (Next)
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-auto">
                    @csrf
                    <button class="btn btn-danger w-100">Logout</button>
                </form>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Appointment Schedule</h2>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="adminCalendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('adminCalendar');
        
        // 1. Prepare the events from the Laravel variable
        // We map your database fields to FullCalendar's expected fields (title, start, color)
        var appointments = @json($appointments->map(function($appt) {
            return [
                'title' => $appt->user->first_name . ' - ' . $appt->service, // What shows on the calendar
                'start' => $appt->appointment_date . 'T' . $appt->appointment_time, // When it is
                'color' => $appt->status === 'confirmed' ? '#198754' : '#ffc107', // Green if confirmed, Yellow if pending
                'extendedProps' => [
                    'status' => $appt->status,
                    'description' => $appt->description
                ]
            ];
        }));

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            events: appointments, // <--- This feeds the data to the calendar!
            
            // When Admin clicks an appointment
            eventClick: function(info) {
                alert('Appointment for: ' + info.event.title + '\nStatus: ' + info.event.extendedProps.status);
                // We will add the Accept/Reject Modal here next!
            }
        });
        
        calendar.render();
    });
</script>
@endsection