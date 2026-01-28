@extends('layouts.app')

@section('content')
<style>
    /* -- Calendar Base Styling -- */
    .fc-daygrid-day { cursor: pointer; transition: background-color 0.2s; }
    .fc-daygrid-day:hover { background-color: #f1f3f5 !important; }
    
    /* Past Dates */
    .past-date {
        background-color: #f8f9fa !important;
        cursor: not-allowed !important;
        opacity: 0.6;
    }
    .past-date .fc-daygrid-day-number { color: #adb5bd !important; }

    /* Closed Dates (Visual Indicator) */
    .closed-date {
        background-color: #ffeaea !important; /* Light Red */
        position: relative;
    }
    .closed-date::after {
        content: 'CLOSED';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.8rem;
        font-weight: bold;
        color: #dc3545;
        opacity: 0.3;
        pointer-events: none;
    }

    /* -- Master Modal Styling -- */
    .modal-header { border-bottom: none; }
    .modal-footer { border-top: none; }
    
    /* The Accordion/Dropdown Animation Magic */
    .control-section {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.5s ease-in-out, opacity 0.4s ease-in-out;
    }
    
    .control-section.active {
        max-height: 800px; /* Arbitrary large height to allow expansion */
        opacity: 1;
    }

    /* Selection Buttons */
    .selection-btn {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px;
        text-align: left;
        transition: all 0.2s;
        background: white;
        cursor: pointer;
        width: 100%;
        margin-bottom: 10px;
    }
    .selection-btn:hover {
        border-color: var(--primary-color);
        background-color: #f8f9fa;
    }
    .selection-btn.active-btn {
        border-color: var(--primary-color);
        background-color: #e7f1ff;
        color: var(--primary-color);
        font-weight: bold;
    }
    .selection-btn i { font-size: 1.2rem; margin-right: 10px; }
</style>

<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4 fw-bold">EyeCare Admin</h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="{{ route('admin.calendar') }}" class="btn btn-primary mb-2 text-start"><i class="bi bi-calendar-week me-2"></i> Calendar</a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-check-circle me-2"></i> Appointments</a>
                <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-clock-history me-2"></i> History</a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-2 text-start border-0"><i class="bi bi-people me-2"></i> Users List</a>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-md-10 p-4">
            <h2 class="mb-4 fw-bold">Master Schedule</h2>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                 <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    {{-- Calendar Container --}}
                    <div id="adminCalendar" 
                         data-events="{{ json_encode($events) }}"
                         data-settings="{{ json_encode($daySettings) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MASTER CONTROL MODAL --}}
<div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white p-4">
                <div>
                    <h4 class="modal-title fw-bold" id="masterDateDisplay">Selected Date</h4>
                    <small class="opacity-75">Manage appointments and day availability</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                
                {{-- Master Control Buttons --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <button class="selection-btn" onclick="toggleSection('book')">
                            <i class="bi bi-plus-circle-fill text-success"></i> Book Appointment
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="selection-btn" onclick="toggleSection('view')">
                            <i class="bi bi-list-check text-primary"></i> View Schedule
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="selection-btn" onclick="toggleSection('settings')">
                            <i class="bi bi-gear-fill text-secondary"></i> Day Settings
                        </button>
                    </div>
                </div>

                <hr class="text-secondary opacity-25">

                {{-- SECTION 1: BOOK APPOINTMENT --}}
                <div id="section-book" class="control-section">
                    <form action="{{ route('admin.calendar.store') }}" method="POST" class="bg-white p-4 rounded-4 shadow-sm">
                        @csrf
                        <h5 class="fw-bold text-success mb-3"><i class="bi bi-person-plus-fill me-2"></i>New Appointment</h5>
                        
                        <input type="hidden" name="appointment_date" id="bookDateInput">

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label small fw-bold text-muted">First Name</label>
                                <input type="text" name="first_name" class="form-control bg-light" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold text-muted">Middle</label>
                                <input type="text" name="middle_name" class="form-control bg-light">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold text-muted">Last Name</label>
                                <input type="text" name="last_name" class="form-control bg-light" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" name="email" class="form-control bg-light" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Phone</label>
                                <input type="text" name="phone" class="form-control bg-light">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Time Slot</label>
                                <select name="appointment_time" id="bookTimeSelect" class="form-select bg-light" required>
                                    <option value="" disabled selected>-- Select Time --</option>
                                    @foreach(['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'] as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Service</label>
                            <select name="service" class="form-select bg-light" required>
                                <option value="General Checkup">General Checkup</option>
                                <option value="Laser Treatment">Laser Treatment</option>
                                <option value="Glasses/Contacts">Glasses/Contacts Fitting</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success rounded-pill px-4">Confirm Booking</button>
                        </div>
                    </form>
                </div>

                {{-- SECTION 2: VIEW APPOINTMENTS --}}
                <div id="section-view" class="control-section">
                    <div class="bg-white p-4 rounded-4 shadow-sm">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-event me-2"></i>Scheduled Visits</h5>
                        <div id="appointmentsList" class="list-group list-group-flush">
                            </div>
                        <div id="noAppointmentsMsg" class="text-center text-muted py-4 d-none">
                            <i class="bi bi-calendar-x fs-1 opacity-25"></i>
                            <p class="mt-2 small">No appointments scheduled for this day.</p>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: DAY SETTINGS --}}
                <div id="section-settings" class="control-section">
                    <form action="{{ route('admin.calendar.settings') }}" method="POST" class="bg-white p-4 rounded-4 shadow-sm border-start border-4 border-secondary">
                        @csrf
                        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-sliders me-2"></i>Configure Day</h5>
                        
                        <input type="hidden" name="date" id="settingDateInput">
                        
                        <input type="hidden" name="is_closed" value="0">

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_closed" value="1" id="isClosedCheck">
                            <label class="form-check-label fw-bold" for="isClosedCheck">Close Clinic on this Date?</label>
                            <div class="form-text">Checking this will prevent patients from booking.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Max Appointments Allowed</label>
                            <input type="number" name="max_appointments" id="maxApptInput" class="form-control" value="5" min="0" max="20">
                            <div class="form-text">Default is 5. Changing this overrides the global limit for this day.</div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-secondary rounded-pill px-4">Save Configuration</button>
                        </div>
                    </form>
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
        var daySettings = JSON.parse(calendarEl.getAttribute('data-settings'));
        var today = new Date();
        today.setHours(0,0,0,0);

        let masterModal = null;

        function getModal() {
            if (!masterModal) {
                if (typeof bootstrap !== 'undefined') {
                    masterModal = new bootstrap.Modal(document.getElementById('masterModal'));
                } else if (window.bootstrap) {
                    masterModal = new window.bootstrap.Modal(document.getElementById('masterModal'));
                } else {
                    console.error("Bootstrap 5 is not loaded yet!");
                    alert("System Error: Bootstrap JS not loaded. Please refresh.");
                }
            }
            return masterModal;
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            headerToolbar: { left: 'dayGridMonth,timeGridDay', center: 'title', right: 'today prev,next' },
            height: 'auto',
            events: eventsData,

            dayCellClassNames: function(arg) {
                var cellDate = new Date(arg.date);
                cellDate.setHours(0,0,0,0);
                
                if (cellDate < today) return ['past-date'];

                var dateStr = formatDate(cellDate);
                if (daySettings[dateStr] && daySettings[dateStr].is_closed) {
                    return ['closed-date'];
                }
                return [];
            },

            dateClick: function(info) {
                var clickedDate = new Date(info.dateStr);
                clickedDate.setHours(0,0,0,0);
                
                if (clickedDate < today) return; 

                // --- FIX: Normalize date string to remove time (YYYY-MM-DD) ---
                // info.dateStr in 'dayGrid' is "2026-01-31"
                // info.dateStr in 'timeGrid' is "2026-01-31T14:30:00"
                // We split by 'T' to ensure we only get the date part.
                var cleanDateStr = info.dateStr.split('T')[0];

                openMasterModal(cleanDateStr);
            },

            eventClick: function(info) {
                var dateStr = info.event.startStr.split('T')[0];
                openMasterModal(dateStr, 'view');
            }
        });

        calendar.render();

        function openMasterModal(dateStr, defaultTab) {
            if (!defaultTab) defaultTab = 'book';
            
            var dateObj = new Date(dateStr);
            // Fix timezone offset issue for display
            // We use the date string parts to create the object strictly in local time concept
            var parts = dateStr.split('-');
            var displayDate = new Date(parts[0], parts[1]-1, parts[2]);

            document.getElementById('masterDateDisplay').textContent = displayDate.toLocaleDateString('en-US', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
            });

            document.getElementById('bookDateInput').value = dateStr;
            document.getElementById('settingDateInput').value = dateStr;

            var settings = daySettings[dateStr];
            var isClosed = settings ? settings.is_closed : false;
            var maxLimit = settings ? settings.max_appointments : 5;

            document.getElementById('isClosedCheck').checked = isClosed;
            document.getElementById('maxApptInput').value = maxLimit;

            var listEl = document.getElementById('appointmentsList');
            var msgEl = document.getElementById('noAppointmentsMsg');
            listEl.innerHTML = '';
            
            var dayEvents = eventsData.filter(function(e) { return e.start.startsWith(dateStr); });

            if (dayEvents.length === 0) {
                msgEl.classList.remove('d-none');
            } else {
                msgEl.classList.add('d-none');
                dayEvents.sort(function(a,b) { return a.start.localeCompare(b.start); });

                dayEvents.forEach(function(event) {
                    var timeStr = new Date(event.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    var rawStatus = event.extendedProps.status;
                    var statusVal = (rawStatus && rawStatus.value) ? rawStatus.value : rawStatus;
                    if (!statusVal) statusVal = 'unknown';

                    var badgeClass = 'bg-secondary';
                    if(statusVal === 'confirmed') badgeClass = 'bg-success';
                    if(statusVal === 'pending') badgeClass = 'bg-warning text-dark';
                    
                    var item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center p-3 mb-2 rounded border shadow-sm';
                    item.innerHTML = `
                        <div>
                            <div class="fw-bold text-dark">${event.title}</div>
                            <div class="small text-muted"><i class="bi bi-clock me-1"></i> ${timeStr}</div>
                        </div>
                        <span class="badge ${badgeClass} rounded-pill">${statusVal.toUpperCase()}</span>
                    `;
                    listEl.appendChild(item);
                });
            }

            resetSections();
            toggleSection(defaultTab);
            
            getModal().show();
        }

        window.toggleSection = function(sectionName) {
            var targetId = 'section-' + sectionName;
            
            document.querySelectorAll('.control-section').forEach(function(el) {
                if(el.id !== targetId) el.classList.remove('active');
            });
            document.getElementById(targetId).classList.add('active');
        };

        function resetSections() {
            document.querySelectorAll('.control-section').forEach(function(el) { el.classList.remove('active'); });
        }

        function formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }
    });
</script>
@endsection