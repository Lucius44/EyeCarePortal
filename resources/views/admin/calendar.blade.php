@extends('layouts.app')

@section('content')
<style>
    .fc-daygrid-day { cursor: pointer; transition: background-color 0.2s; }
    .fc-daygrid-day:hover { background-color: #e9ecef !important; }
    .modal-header { border-bottom: none; }
    .modal-footer { border-top: none; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="{{ route('admin.calendar') }}" class="btn btn-primary mb-2 text-start"><i class="bi bi-calendar-week me-2"></i> Calendar</a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-check-circle me-2"></i> Appointments</a>
                <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-clock-history me-2"></i> History</a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-people me-2"></i> Users List</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Appointment Schedule</h2>
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="adminCalendar" data-events="{{ json_encode($events) }}"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- View Event Modal --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Appointment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold text-secondary small text-uppercase">Patient / Service</label>
                    <p id="modalPatient" class="fs-5 text-dark mb-0"></p>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="fw-bold text-secondary small text-uppercase">Time</label>
                        <p id="modalTime" class="fs-5 text-dark mb-0"></p>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="fw-bold text-secondary small text-uppercase">Status</label>
                        <p><span id="modalStatus" class="badge"></span></p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold text-secondary small text-uppercase">Notes</label>
                    <p id="modalDescription" class="text-muted bg-light p-2 rounded">No notes provided.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Create Appointment Modal --}}
{{-- UPDATED: Added data attributes here to pass PHP data to JS cleanly --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true" 
     data-has-errors="{{ $errors->any() ? 'true' : 'false' }}"
     data-old-date="{{ old('appointment_date') }}">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.calendar.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Book Walk-in / Follow-up</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    {{-- Validation Error Feedback --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-light border mb-3 text-center">
                        <strong>Selected Date:</strong> <span id="displayDate" class="text-success"></span>
                    </div>
                    {{-- Retain old date on error --}}
                    <input type="hidden" name="appointment_date" id="createDate" value="{{ old('appointment_date') }}">

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Middle (Opt)</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required value="{{ old('last_name') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Phone (Optional)</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Time</label>
                            <select name="appointment_time" id="createTime" class="form-select" required>
                                <option value="" disabled {{ old('appointment_time') ? '' : 'selected' }}>-- Select Time --</option>
                                @foreach(['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'] as $time)
                                    <option value="{{ $time }}" {{ old('appointment_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <select name="service" class="form-select" required>
                            <option value="General Checkup" {{ old('service') == 'General Checkup' ? 'selected' : '' }}>General Checkup</option>
                            <option value="Laser Treatment" {{ old('service') == 'Laser Treatment' ? 'selected' : '' }}>Laser Treatment</option>
                            <option value="Glasses/Contacts" {{ old('service') == 'Glasses/Contacts' ? 'selected' : '' }}>Glasses/Contacts Fitting</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Book Appointment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('adminCalendar');
        var eventsData = JSON.parse(calendarEl.getAttribute('data-events'));
        
        // Grab the modal element to access data attributes
        var createModalEl = document.getElementById('createModal');

        var eventModal = new window.bootstrap.Modal(document.getElementById('eventModal'));
        var createModal = new window.bootstrap.Modal(createModalEl);

        var modalPatient = document.getElementById('modalPatient');
        var modalTime = document.getElementById('modalTime');
        var modalStatus = document.getElementById('modalStatus');
        var modalDescription = document.getElementById('modalDescription');
        
        var createDateInput = document.getElementById('createDate');
        var displayDate = document.getElementById('displayDate');
        var createTimeSelect = document.getElementById('createTime');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'dayGridMonth,timeGridDay',
                center: 'title',
                right: 'today prev,next'
            },
            height: 'auto',
            events: eventsData,
            eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
            slotLabelFormat: { hour: 'numeric', minute: '2-digit', omitZeroMinute: false, meridiem: 'short' },
            slotMinTime: '09:00:00', 
            slotMaxTime: '18:00:00', 
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                modalPatient.textContent = info.event.title; 
                if(info.event.start) modalTime.textContent = info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                var status = info.event.extendedProps.status; 
                if (typeof status === 'object' && status !== null && status.value) status = status.value;

                if(status) {
                    modalStatus.textContent = status.toUpperCase();
                    modalStatus.className = 'badge'; 
                    if(status === 'confirmed') modalStatus.classList.add('bg-success');
                    else if(status === 'pending') modalStatus.classList.add('bg-warning', 'text-dark');
                    else modalStatus.classList.add('bg-secondary');
                }
                modalDescription.textContent = info.event.extendedProps.description || "No notes provided.";
                eventModal.show();
            },

            dateClick: function(info) {
                var clickedDate = info.dateStr; 
                if (clickedDate.includes('T')) clickedDate = clickedDate.split('T')[0];
                createDateInput.value = clickedDate;
                
                // Format Date for Display
                displayDate.textContent = new Date(clickedDate).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                // Reset Time Options
                for (var i = 0; i < createTimeSelect.options.length; i++) {
                    createTimeSelect.options[i].disabled = false;
                    if(createTimeSelect.options[i].innerText.includes("(Booked)")) {
                        createTimeSelect.options[i].innerText = createTimeSelect.options[i].value; 
                    }
                }
                
                createTimeSelect.value = "";

                // Disable Booked Times
                var bookedTimes = eventsData.filter(function(event) {
                    return event.start.startsWith(clickedDate);
                }).map(function(event) {
                    var dateObj = new Date(event.start);
                    var hours = dateObj.getHours();
                    var minutes = dateObj.getMinutes();
                    var ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12; 
                    var strTime = (hours < 10 ? '0' + hours : hours) + ':' + (minutes < 10 ? '0' + minutes : minutes) + ' ' + ampm;
                    return strTime;
                });

                for (var i = 0; i < createTimeSelect.options.length; i++) {
                    var optValue = createTimeSelect.options[i].value; 
                    if (bookedTimes.includes(optValue)) {
                        createTimeSelect.options[i].disabled = true;
                        createTimeSelect.options[i].innerText = optValue + " (Booked)";
                    }
                }

                createModal.show();
            }
        });
        
        calendar.render();

        // --- UPDATED: Handle Validation Errors (Pure JS) ---
        // We read the data attributes from the modal div instead of injecting PHP here
        var hasErrors = createModalEl.getAttribute('data-has-errors') === 'true';
        var oldDate = createModalEl.getAttribute('data-old-date');

        if (hasErrors) {
            createModal.show();

            // Refill the Display Date text using the preserved old date
            if (oldDate) {
                var parts = oldDate.split('-');
                // Create local date object (Year, MonthIndex, Day)
                var dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
                displayDate.textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
        }
    });
</script>
@endsection