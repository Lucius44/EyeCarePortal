@extends('layouts.app')

@section('content')
<style>
    /* Change cursor to pointer to indicate clickable events */
    .fc-event {
        cursor: pointer;
    }
    
    /* Optional: Make the modal look a bit cleaner */
    .modal-header {
        border-bottom: none;
    }
    .modal-footer {
        border-top: none;
    }
</style>

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

{{-- Event Details Modal --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Appointment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('adminCalendar');
        var eventsData = JSON.parse(calendarEl.getAttribute('data-events'));
        
        // FIX: Use 'window.bootstrap' to ensure we find the loaded library
        var eventModal;
        try {
            eventModal = new window.bootstrap.Modal(document.getElementById('eventModal'));
        } catch (e) {
            console.error("Bootstrap Modal failed to initialize", e);
        }

        var modalTitle = document.getElementById('modalTitle');
        var modalPatient = document.getElementById('modalPatient');
        var modalTime = document.getElementById('modalTime');
        var modalStatus = document.getElementById('modalStatus');
        var modalDescription = document.getElementById('modalDescription');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridDay'
            },
            height: 'auto',
            events: eventsData,

            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: 'short'
            },
            slotMinTime: '08:00:00', 
            slotMaxTime: '18:00:00', 
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();

                // 1. Set Data
                modalPatient.textContent = info.event.title; 
                
                // Format Time
                if(info.event.start) {
                    modalTime.textContent = info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }

                // FIX: Access 'status' directly (it's a string, not an object with .value)
                var status = info.event.extendedProps.status; 
                
                // Safety check if status is somehow an object (e.g. if Resource changed)
                if (typeof status === 'object' && status !== null && status.value) {
                    status = status.value;
                }

                if(status) {
                    modalStatus.textContent = status.toUpperCase();
                    
                    // Reset classes
                    modalStatus.className = 'badge'; 
                    
                    if(status === 'confirmed') modalStatus.classList.add('bg-success');
                    else if(status === 'pending') modalStatus.classList.add('bg-warning', 'text-dark');
                    else modalStatus.classList.add('bg-secondary');
                } else {
                    modalStatus.textContent = 'UNKNOWN';
                    modalStatus.className = 'badge bg-secondary';
                }

                // Description
                var desc = info.event.extendedProps.description;
                modalDescription.textContent = desc ? desc : "No notes provided.";

                // 2. Show Modal
                if (eventModal) {
                    eventModal.show();
                } else {
                    alert('Modal not initialized. Please refresh.');
                }
            }
        });
        
        calendar.render();
    });
</script>
@endsection