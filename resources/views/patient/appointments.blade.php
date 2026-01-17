@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Book an Appointment</h4>
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">Back to Dashboard</a>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', // Show the monthly view by default
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto', // Adjust height automatically
            selectable: true, // Allow user to click/select days
            selectMirror: true,
            
            // This function runs when a user clicks a date!
            dateClick: function(info) {
                alert('You clicked on: ' + info.dateStr);
                // We will open the booking modal here in the next step!
            }
        });
        
        calendar.render();
    });
</script>

<style>
    /* Simple styling to make the calendar look good */
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        padding: 10px;
    }
    .fc-toolbar-title {
        font-size: 1.5rem !important;
    }
    .fc-button {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
    }
</style>
@endsection