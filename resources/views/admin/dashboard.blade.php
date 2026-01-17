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
                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    ✅ Manage Appointments
                </a>
                <a href="#" class="btn btn-outline-secondary mb-2 text-start disabled border-0">
                    👥 Users List
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
        
        // FIX: Parse the data from the HTML attribute
        // This is pure JavaScript, so your editor will be happy.
        var eventsData = JSON.parse(calendarEl.getAttribute('data-events'));

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            events: eventsData, // Pass the clean data here
            
            // Event Click Action
            eventClick: function(info) {
                alert('Appointment for: ' + info.event.title + '\nStatus: ' + info.event.extendedProps.status);
            }
        });
        
        calendar.render();
    });
</script>
@endsection