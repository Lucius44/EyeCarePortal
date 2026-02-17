@extends('layouts.app')

@section('content')
<style>
    /* --- ADMIN LAYOUT STYLES --- */
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); overflow-x: hidden; }
    
    .admin-sidebar { 
        width: 260px; 
        background: #0F172A; 
        color: #94a3b8; 
        flex-shrink: 0; 
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        display: none; 
    }

    .admin-content { 
        flex-grow: 1; 
        background: #F1F5F9; 
        padding: 1.5rem; 
        min-width: 0; 
    }

    @media (min-width: 992px) {
        .admin-sidebar { display: flex; }
        .admin-content { padding: 2rem; }
    }
    
    .admin-nav-link {
        display: flex; align-items: center; padding: 12px 20px;
        color: #94a3b8; text-decoration: none; font-weight: 500;
        border-radius: 8px; margin-bottom: 5px; transition: all 0.2s;
    }
    .admin-nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav-link.active { background: #3B82F6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .admin-nav-link i { font-size: 1.1rem; margin-right: 12px; }

    /* --- CALENDAR SPECIFIC STYLES --- */
    .fc-daygrid-day { cursor: pointer; transition: background-color 0.2s; }
    .fc-daygrid-day:hover { background-color: #f1f3f5 !important; }
    .fc-timegrid-allday { display: none !important; }
    .fc-timegrid-slot-lane { cursor: pointer; } 
    .fc-timegrid-slot-lane:hover { background-color: #e7f1ff !important; }

    .past-date { background-color: #f8f9fa !important; cursor: not-allowed !important; opacity: 0.6; }
    .past-date .fc-daygrid-day-number { color: #adb5bd !important; }

    .closed-date { background-color: #ffeaea !important; position: relative; }
    .closed-date::after {
        content: 'CLOSED'; position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%); font-size: 0.7rem;
        font-weight: 800; color: #dc3545; opacity: 0.4; letter-spacing: 1px; pointer-events: none;
    }

    /* --- MOBILE STRIP STYLES --- */
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
    .date-card.closed-day { background: #fff5f5; border-color: #ffe0e3; color: #e11d48; }
    
    /* Past Day Style (Unclickable Visual) */
    .date-card.past-day { background: #f1f5f9; border-color: #e2e8f0; color: #94a3b8; cursor: default; }
    .date-card.past-day .day-name, .date-card.past-day .day-num { opacity: 0.6; }

    .day-name { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; }
    .day-num { font-size: 1.25rem; font-weight: 800; line-height: 1; margin-top: 2px; }

    /* --- MODAL STYLES --- */
    .control-section { display: none; opacity: 0; transition: opacity 0.3s ease; }
    .control-section.active { display: block; opacity: 1; }

    .selection-btn {
        border: 2px solid #e9ecef; border-radius: 12px; padding: 15px;
        text-align: left; transition: all 0.2s; background: white; cursor: pointer; width: 100%; height: 100%;
    }
    .selection-btn:hover { border-color: var(--accent-color); background-color: #f8f9fa; transform: translateY(-2px); }
    .selection-btn.active-btn {
        border-color: var(--accent-color); background-color: #eff6ff;
        color: var(--accent-color); font-weight: bold;
    }
    .selection-btn i { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
    
    .transition-btn { transition: all 0.2s ease; }
    .transition-btn:hover { transform: translateY(-2px); }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
        {{-- SIDEBAR --}}
        <div class="admin-sidebar p-3 d-none d-lg-flex">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            @include('admin.partials.nav_links')

            {{-- Support Line --}}
            <div class="mt-auto p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
                <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="admin-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-white border shadow-sm d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu">
                        <i class="bi bi-list fs-5 text-primary"></i>
                    </button>

                    <div>
                        <h2 class="fw-bold text-dark mb-1">Master Schedule</h2>
                        <p class="text-secondary mb-0 small">Manage clinic availability and bookings.</p>
                    </div>
                </div>

                <div class="d-none d-md-flex gap-2">
                    <button id="btnMonthView" class="btn btn-primary px-3 fw-bold rounded-pill shadow-sm transition-btn">
                        <i class="bi bi-calendar-month me-2"></i>Month
                    </button>
                    <button id="btnDayView" class="btn btn-outline-secondary px-3 fw-bold rounded-pill transition-btn">
                        <i class="bi bi-calendar-day me-2"></i>Day
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                 <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- MOBILE VIEW: NAVIGATOR + DATE STRIP --}}
            <div class="d-md-none mb-4">
                {{-- New Navigation Header --}}
                <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded-4 shadow-sm">
                    <button class="btn btn-sm btn-light rounded-circle border" id="mobilePrevMonth">
                        <i class="bi bi-chevron-left text-dark"></i>
                    </button>
                    
                    <div class="text-center">
                        <h6 class="mb-0 fw-bold text-dark" id="mobileMonthDisplay">Loading...</h6>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-light rounded-circle border" id="mobileNextMonth">
                            <i class="bi bi-chevron-right text-dark"></i>
                        </button>
                        
                        {{-- Today Button --}}
                        <button class="btn btn-sm btn-outline-primary fw-bold rounded-3" id="mobileTodayBtn" style="font-size: 0.7rem;">
                            Today
                        </button>

                        <div style="position: relative;">
                            <input type="date" id="mobileDatePicker" style="position: absolute; opacity: 0; width: 100%; height: 100%; top:0; left:0; z-index: 10;">
                            <button class="btn btn-sm btn-primary rounded-circle shadow-sm">
                                <i class="bi bi-calendar-date"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="date-strip-wrapper">
                    <div class="d-flex gap-2" id="mobileDateStrip">
                        {{-- JS Populated --}}
                    </div>
                </div>
                
                {{-- Footer Instruction --}}
                <div class="text-center mt-3 text-muted small opacity-75">
                    <small>Tap a date to manage schedule</small>
                </div>
            </div>

            {{-- DESKTOP VIEW: FULLCALENDAR --}}
            <div class="d-none d-md-block card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4">
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
            
            <div class="modal-header bg-white border-bottom-0 p-4 pb-0">
                <div>
                    <h4 class="modal-title fw-bold text-primary" id="masterDateDisplay">Selected Date</h4>
                    <span class="badge bg-light text-secondary border mt-2" id="masterDateStatus">Open</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-4" id="col-book-btn">
                        <div class="selection-btn text-center" onclick="toggleSection('book')" id="btn-book">
                            <i class="bi bi-plus-circle-fill text-success"></i>
                            <div class="small fw-bold">Book</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="selection-btn text-center" onclick="toggleSection('view')" id="btn-view">
                            <i class="bi bi-list-check text-primary"></i>
                            <div class="small fw-bold">Schedule</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="selection-btn text-center" onclick="toggleSection('settings')" id="btn-settings">
                            <i class="bi bi-sliders text-secondary"></i>
                            <div class="small fw-bold">Settings</div>
                        </div>
                    </div>
                </div>

                {{-- SECTION: BOOK --}}
                <div id="section-book" class="control-section">
                    <form action="{{ route('admin.calendar.store') }}" method="POST" class="bg-light p-4 rounded-4">
                        @csrf
                        <h6 class="fw-bold text-success mb-3">Add Walk-in / Manual Booking</h6>
                        <input type="hidden" name="appointment_date" id="bookDateInput">

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="middle_name" class="form-control" placeholder="Middle (Opt)">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                            </div>
                            {{-- NEW SUFFIX FIELD --}}
                            <div class="col-md-2">
                                <select name="patient_suffix" class="form-select">
                                    <option value="">Suffix</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="phone" class="form-control" placeholder="Phone Number">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <select name="appointment_time" id="bookTimeSelect" class="form-select" required>
                                    <option value="" disabled selected>Select Time Slot</option>
                                    @foreach(['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'] as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="service" class="form-select" required>
                                    <option value="" disabled selected>Select Service...</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->name }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success fw-bold">Confirm Booking</button>
                        </div>
                    </form>
                </div>

                {{-- SECTION: VIEW --}}
                <div id="section-view" class="control-section">
                    <div class="bg-light p-3 rounded-4" style="max-height: 400px; overflow-y: auto;">
                        <h6 class="fw-bold text-primary mb-3">Scheduled Appointments</h6>
                        <div id="appointmentsList" class="d-grid gap-2"></div>
                        <div id="noAppointmentsMsg" class="text-center text-muted py-5 d-none">
                            <i class="bi bi-calendar-x fs-1 opacity-25"></i>
                            <p class="mt-2 small">No appointments for this day.</p>
                        </div>
                    </div>
                </div>

                {{-- SECTION: SETTINGS --}}
                <div id="section-settings" class="control-section">
                    <form action="{{ route('admin.calendar.settings') }}" method="POST" class="bg-light p-4 rounded-4 border border-secondary border-opacity-10">
                        @csrf
                        <h6 class="fw-bold text-secondary mb-3">Day Configuration</h6>
                        <input type="hidden" name="date" id="settingDateInput">
                        <input type="hidden" name="is_closed" value="0">

                        <div class="form-check form-switch mb-4 p-3 bg-white rounded-3 border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" name="is_closed" value="1" id="isClosedCheck" style="float:none;">
                            <label class="form-check-label fw-bold" for="isClosedCheck">Close Clinic</label>
                            <div class="small text-muted mt-1">Prevents patients from booking this date.</div>
                        </div>

                        {{-- NEW: Reason Field (Toggled by JS) --}}
                        <div class="mb-3 d-none" id="closeReasonContainer">
                            <label class="form-label fw-bold small text-uppercase text-danger">Reason for Closing <span class="text-danger">*</span></label>
                            <textarea name="close_reason" id="daySettingsReason" class="form-control" rows="2" placeholder="e.g. Doctor is on leave, Holiday, etc."></textarea>
                            <div class="form-text text-muted">This reason will be sent to patients with Pending requests.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Max Capacity</label>
                            <input type="number" name="max_appointments" id="maxApptInput" class="form-control" value="5" min="1" max="9">
                            <div class="form-text">Limit bookings for this specific date (1-9).</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-secondary fw-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MOBILE MENU --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileAdminMenu" style="background: #0F172A; width: 280px;">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-white fw-bold">Admin Console</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('admin.partials.nav_links')
        
        <div class="mt-5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
            <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
            <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
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

        // --- DESKTOP: FULLCALENDAR INITIALIZATION (UNTOUCHED) ---
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'standard',
            headerToolbar: { left: 'title', right: 'today prev,next' },
            height: 'auto',
            events: eventsData,
            
            allDaySlot: false,
            slotMinTime: '09:00:00',
            slotMaxTime: '18:00:00', 
            slotDuration: '01:00:00', 
            expandRows: true, 

            dayCellClassNames: function(arg) {
                var d = new Date(arg.date); d.setHours(0,0,0,0);
                if (d < today) return ['past-date'];
                
                var iso = formatDate(d);
                if (daySettings[iso] && daySettings[iso].is_closed) return ['closed-date'];
                return [];
            },

            dateClick: function(info) {
                var d = new Date(info.dateStr); 
                d.setHours(0,0,0,0);
                if (d < today) return;
                
                var safeDateStr = info.dateStr.split('T')[0];
                openMasterModal(safeDateStr);
            },
            eventClick: function(info) {
                var iso = info.event.startStr.split('T')[0];
                openMasterModal(iso, 'view');
            }
        });
        calendar.render();

        var btnMonth = document.getElementById('btnMonthView');
        var btnDay = document.getElementById('btnDayView');

        if(btnMonth && btnDay) {
            btnMonth.addEventListener('click', function() {
                calendar.changeView('dayGridMonth');
                btnMonth.classList.remove('btn-outline-secondary');
                btnMonth.classList.add('btn-primary', 'shadow-sm');
                btnDay.classList.remove('btn-primary', 'shadow-sm');
                btnDay.classList.add('btn-outline-secondary');
            });

            btnDay.addEventListener('click', function() {
                calendar.changeView('timeGridDay');
                btnDay.classList.remove('btn-outline-secondary');
                btnDay.classList.add('btn-primary', 'shadow-sm');
                btnMonth.classList.remove('btn-primary', 'shadow-sm');
                btnMonth.classList.add('btn-outline-secondary');
            });
        }

        // --- MOBILE STRIP LOGIC ---
        var currentMobileDate = new Date(); // State for current view

        function renderMobileStrip(startDate) {
            var container = document.getElementById('mobileDateStrip');
            var monthDisplay = document.getElementById('mobileMonthDisplay');
            if(!container) return; 
            
            container.innerHTML = ''; // Clear previous

            // Update Header Display (e.g., "February 2026")
            monthDisplay.textContent = startDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            // Render 31 days starting from the selected date
            for(let i=0; i<31; i++) {
                let d = new Date(startDate);
                d.setDate(startDate.getDate() + i);
                
                // Determine if past date
                let dCheck = new Date(d); dCheck.setHours(0,0,0,0);
                let isPast = dCheck < today;

                let iso = formatDate(d);
                let dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
                let dayNum = d.getDate();
                let settings = daySettings[iso];
                let isClosed = settings ? settings.is_closed : false;
                
                let card = document.createElement('div');
                // Apply 'past-day' class if past
                card.className = 'date-card ' + (isClosed ? 'closed-day ' : '') + (isPast ? 'past-day' : '');
                
                card.innerHTML = `<div class="day-name">${dayName}</div><div class="day-num">${dayNum}</div>${isClosed ? '<small class="fw-bold mt-1" style="font-size:0.6rem">CLOSED</small>' : ''}`;
                
                // Only attach onclick if NOT past
                if (!isPast) {
                    card.onclick = function() { openMasterModal(iso); };
                }
                
                container.appendChild(card);
            }
        }

        // Initialize Mobile Strip
        renderMobileStrip(currentMobileDate);

        // Mobile Navigation Events
        document.getElementById('mobilePrevMonth').addEventListener('click', function() {
            currentMobileDate.setDate(1); 
            currentMobileDate.setMonth(currentMobileDate.getMonth() - 1);
            renderMobileStrip(currentMobileDate);
        });

        document.getElementById('mobileNextMonth').addEventListener('click', function() {
            currentMobileDate.setDate(1); 
            currentMobileDate.setMonth(currentMobileDate.getMonth() + 1);
            renderMobileStrip(currentMobileDate);
        });

        // Today Button Logic
        document.getElementById('mobileTodayBtn').addEventListener('click', function() {
            currentMobileDate = new Date(); // Reset to actual today
            renderMobileStrip(currentMobileDate);
        });

        document.getElementById('mobileDatePicker').addEventListener('change', function(e) {
            if(e.target.value) {
                currentMobileDate = new Date(e.target.value);
                renderMobileStrip(currentMobileDate);
            }
        });

        // --- SHARED MODAL LOGIC ---
        var masterModal = null;
        
        // NEW: Toggle Logic for Reason Field
        function toggleReasonField(isClosed) {
            var container = document.getElementById('closeReasonContainer');
            var input = document.getElementById('daySettingsReason');
            
            if (isClosed) {
                container.classList.remove('d-none');
                input.setAttribute('required', 'required');
            } else {
                container.classList.add('d-none');
                input.removeAttribute('required');
                input.value = ''; 
            }
        }

        // NEW: Event Listener for Checkbox
        var closeCheck = document.getElementById('isClosedCheck');
        if(closeCheck) {
            closeCheck.addEventListener('change', function() {
                toggleReasonField(this.checked);
            });
        }
        
        function openMasterModal(dateStr, defaultTab = 'book') {
            if (!masterModal) masterModal = new bootstrap.Modal(document.getElementById('masterModal'));
            
            var parts = dateStr.split('-');
            var displayDate = new Date(parts[0], parts[1]-1, parts[2]);
            
            var modalToday = new Date(); modalToday.setHours(0,0,0,0);
            var isPast = displayDate < modalToday;

            document.getElementById('masterDateDisplay').textContent = displayDate.toLocaleDateString('en-US', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
            });

            document.getElementById('bookDateInput').value = dateStr;
            document.getElementById('settingDateInput').value = dateStr;

            var btnBook = document.getElementById('btn-book');
            var colBook = document.getElementById('col-book-btn');
            var badge = document.getElementById('masterDateStatus');

            if (isPast) {
                colBook.classList.add('d-none');
                defaultTab = 'view';
                badge.className = 'badge bg-secondary text-white mt-2';
                badge.textContent = 'Past Date';
            } else {
                colBook.classList.remove('d-none');
                
                var settings = daySettings[dateStr];
                var isClosed = settings ? settings.is_closed : false;
                var maxLimit = settings ? settings.max_appointments : 5;
                
                document.getElementById('isClosedCheck').checked = isClosed;
                document.getElementById('maxApptInput').value = maxLimit;
                
                // NEW: Sync visibility when opening modal
                toggleReasonField(isClosed);
                
                if(isClosed) {
                    badge.className = 'badge bg-danger text-white mt-2';
                    badge.textContent = 'Clinic Closed';
                } else {
                    badge.className = 'badge bg-success text-white mt-2';
                    badge.textContent = 'Open for Booking';
                }
            }

            var dayEvents = eventsData.filter(e => e.start.startsWith(dateStr));
            var listContainer = document.getElementById('appointmentsList');
            var noMsg = document.getElementById('noAppointmentsMsg');
            var bookSelect = document.getElementById('bookTimeSelect');
            
            listContainer.innerHTML = '';
            for(let opt of bookSelect.options) { opt.disabled = false; if(opt.text.includes('(Booked)')) opt.text = opt.value; }

            if(dayEvents.length === 0) {
                noMsg.classList.remove('d-none');
            } else {
                noMsg.classList.add('d-none');
                dayEvents.forEach(evt => {
                    let time = new Date(evt.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    let status = (evt.extendedProps.status?.value || evt.extendedProps.status || 'unknown');
                    let badgeColor = status === 'confirmed' ? 'bg-success' : (status === 'pending' ? 'bg-warning' : 'bg-secondary');
                    
                    let div = document.createElement('div');
                    div.className = 'p-3 bg-white rounded-3 shadow-sm border mb-1 d-flex justify-content-between align-items-center';
                    div.innerHTML = `<div><div class="fw-bold">${evt.title}</div><div class="small text-muted"><i class="bi bi-clock me-1"></i>${time}</div></div><span class="badge ${badgeColor} rounded-pill text-uppercase" style="font-size:0.7rem">${status}</span>`;
                    listContainer.appendChild(div);

                    for(let opt of bookSelect.options) {
                        if(opt.value === time) { opt.disabled = true; opt.text = opt.value + ' (Booked)'; }
                    }
                });
            }
            toggleSection(defaultTab);
            masterModal.show();
        }

        window.toggleSection = function(sec) {
            document.querySelectorAll('.control-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.selection-btn').forEach(el => el.classList.remove('active-btn'));
            document.getElementById('section-'+sec).classList.add('active');
            document.getElementById('btn-'+sec).classList.add('active-btn');
        };

        function formatDate(d) {
            let month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        }
    });
</script>
@endsection