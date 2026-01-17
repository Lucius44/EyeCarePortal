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

<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">New Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <input type="hidden" name="appointment_date" id="modalDateInput">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Selected Date:</label>
                        <span id="displayDate" class="text-primary fs-5"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service Required</label>
                        <select name="service" class="form-select" required>
                            <option value="">-- Select Service --</option>
                            @foreach($services as $service)
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Preferred Time</label>
                        <select name="appointment_time" class="form-select" required>
                            <option value="">-- Select Time --</option>
                            <option value="09:00 AM">09:00 AM</option>
                            <option value="10:00 AM">10:00 AM</option>
                            <option value="11:00 AM">11:00 AM</option>
                            <option value="01:00 PM">01:00 PM</option>
                            <option value="02:00 PM">02:00 PM</option>
                            <option value="03:00 PM">03:00 PM</option>
                            <option value="04:00 PM">04:00 PM</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes / Symptoms (Optional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Get User Verification Status from Laravel
        const isVerified = {{ Auth::user()->is_verified ? 'true' : 'false' }};
        
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            height: 'auto',
            selectable: true, 

            // 2. Handle Date Click
            dateClick: function(info) {
                // Constraint Check: Is the user verified?
                if (!isVerified) {
                    alert('Your account is not verified yet. Please upload your ID in settings to enable booking.');
                    return; // Stop here
                }

                // Constraint Check: Don't allow past dates
                let clickedDate = new Date(info.dateStr);
                let today = new Date();
                today.setHours(0,0,0,0); // Remove time part for accurate comparison

                if (clickedDate < today) {
                    alert('You cannot book an appointment in the past.');
                    return;
                }

                // 3. Open the Modal
                // Fill the hidden input and the display text
                document.getElementById('modalDateInput').value = info.dateStr;
                document.getElementById('displayDate').innerText = info.dateStr;
                
                // Show Bootstrap Modal
                var myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                myModal.show();
            }
        });
        calendar.render();
    });
</script>

<style>
    #calendar { max-width: 100%; margin: 0 auto; padding: 10px; }
    .fc-toolbar-title { font-size: 1.5rem !important; }
    .fc-button { background-color: #0d6efd !important; border-color: #0d6efd !important; }
    .fc-day-today { background-color: #e8f4ff !important; }
</style>
@endsection