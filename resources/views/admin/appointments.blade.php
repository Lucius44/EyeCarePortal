@extends('layouts.app')

@section('content')
<style>
    /* --- ADMIN RESPONSIVE LAYOUT --- */
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

    /* Page Specific */
    .table-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
    .nav-pills .nav-link { 
        color: #64748b; font-weight: 600; border-radius: 50px; padding: 0.5rem 1.5rem; 
    }
    .nav-pills .nav-link.active { 
        background: #0F172A; color: white; 
    }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
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

        <div class="admin-content">
            {{-- HEADER with Mobile Toggle --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <button class="btn btn-white border shadow-sm d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu">
                    <i class="bi bi-list fs-5 text-primary"></i>
                </button>
                <h2 class="fw-bold text-dark mb-0">Manage Appointments</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            {{-- TAB PERSISTENCE LOGIC --}}
            @php
                $activeTab = request('tab') ?? 'pending';
                if(!in_array($activeTab, ['pending', 'ongoing'])) {
                    $activeTab = 'pending';
                }
            @endphp

            <div class="d-flex mb-4">
                <ul class="nav nav-pills bg-white p-2 rounded-pill shadow-sm" id="myTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" 
                                id="pending-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#pending" 
                                type="button">
                            Pending <span class="badge bg-danger ms-2 rounded-circle">{{ $pending->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'ongoing' ? 'active' : '' }}" 
                                id="ongoing-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#ongoing" 
                                type="button">
                            Confirmed / Ongoing
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="myTabContent">
                
                {{-- PENDING TAB --}}
                <div class="tab-pane fade {{ $activeTab === 'pending' ? 'show active' : '' }}" id="pending">
                    <div class="table-card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary small text-uppercase">Patient</th>
                                        <th class="py-3 text-secondary small text-uppercase">Date & Time</th>
                                        <th class="py-3 text-secondary small text-uppercase">Service</th>
                                        <th class="py-3 text-secondary small text-uppercase">Notes</th>
                                        <th class="py-3 text-end pe-4 text-secondary small text-uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pending as $appt)
                                    <tr>
                                        {{-- Stacked Name Display --}}
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $appt->patient_name }}</div>
                                            @if($appt->patient_first_name && $appt->user)
                                                <div class="small text-muted">
                                                    {{-- UPDATED: Added suffix to 'Booked by' --}}
                                                    <i class="bi bi-person-badge me-1"></i>Booked by: {{ $appt->user->first_name }} {{ $appt->user->last_name }} {{ $appt->user->suffix }}
                                                    @if($appt->relationship)
                                                        <span class="text-primary">({{ $appt->relationship }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded p-2 text-center me-3" style="min-width: 50px;">
                                                    <span class="d-block text-danger fw-bold small text-uppercase">{{ $appt->appointment_date->format('M') }}</span>
                                                    <span class="d-block fw-bold h5 mb-0">{{ $appt->appointment_date->format('d') }}</span>
                                                </div>
                                                <small class="text-muted fw-bold">{{ $appt->appointment_time }}</small>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-2">{{ $appt->service }}</span></td>
                                        <td class="small text-muted" style="max-width: 200px;">{{ Str::limit($appt->description, 30) ?: '-' }}</td>
                                        <td class="text-end pe-4">
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="confirmed">
                                                <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold">Accept</button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $appt->id }}">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Reject Modal --}}
                                    <div class="modal fade" id="rejectModal-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-danger">Reject Appointment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-muted mb-3">Please select a reason for rejecting <strong>{{ $appt->patient_name }}</strong>.</p>
                                                        
                                                        <div class="mb-3">
                                                            <select class="form-select form-select-lg" name="reason_select" onchange="toggleOther(this, '{{ $appt->id }}')" required>
                                                                <option value="">-- Select Reason --</option>
                                                                <option value="Doctor Unavailable">Doctor Unavailable</option>
                                                                <option value="Double Booked">Double Booked</option>
                                                                <option value="Service Not Available">Service Not Available</option>
                                                                <option value="Incomplete Information">Incomplete Information</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 d-none" id="otherReasonDiv-{{ $appt->id }}">
                                                            <textarea name="cancellation_reason" id="textArea-{{ $appt->id }}" class="form-control" rows="3" placeholder="Please specify the reason..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Confirm Rejection</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No pending requests found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- PAGINATION: PENDING --}}
                        <div class="row align-items-center p-3 border-top g-0">
                            <div class="col-lg-4 d-none d-lg-block order-lg-1"></div>
                            <div class="col-12 col-lg-4 text-center text-muted small order-2 order-lg-2 mt-2 mt-lg-0">
                                @if($pending->total() > 0)
                                    Showing {{ $pending->firstItem() }} to {{ $pending->lastItem() }} of {{ $pending->total() }} results
                                @else
                                    No results
                                @endif
                            </div>
                            <div class="col-12 col-lg-4 text-end order-1 order-lg-3">
                                {{ $pending->appends(['tab' => 'pending'])->links('partials.pagination') }}
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ONGOING TAB --}}
                <div class="tab-pane fade {{ $activeTab === 'ongoing' ? 'show active' : '' }}" id="ongoing">
                    <div class="table-card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary small text-uppercase">Patient</th>
                                        <th class="py-3 text-secondary small text-uppercase">Schedule</th>
                                        <th class="py-3 text-secondary small text-uppercase">Service</th>
                                        <th class="py-3 text-end pe-4 text-secondary small text-uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($confirmed as $appt)
                                    <tr>
                                        {{-- Stacked Name Display --}}
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $appt->patient_name }}</div>
                                            @if($appt->patient_first_name && $appt->user)
                                                <div class="small text-muted">
                                                    {{-- UPDATED: Added suffix to 'Booked by' --}}
                                                    <i class="bi bi-person-badge me-1"></i>Booked by: {{ $appt->user->first_name }} {{ $appt->user->last_name }} {{ $appt->user->suffix }}
                                                    @if($appt->relationship)
                                                        <span class="text-primary">({{ $appt->relationship }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-dark fw-bold">{{ $appt->appointment_date->format('M d, Y') }}</span>
                                            <span class="text-muted small ms-2">{{ $appt->appointment_time }}</span>
                                        </td>
                                        <td>{{ $appt->service }}</td>
                                        <td class="text-end pe-4">
                                            {{-- Mark Complete Button Triggers Modal --}}
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#completeModal-{{ $appt->id }}">
                                                Mark Complete
                                            </button>
                                            
                                            {{-- No-Show Button Triggers Modal --}}
                                            <button type="button" class="btn btn-light text-warning-emphasis btn-sm rounded-pill px-3 fw-bold border ms-1" data-bs-toggle="modal" data-bs-target="#noShowModal-{{ $appt->id }}">
                                                No-Show
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- COMPLETE MODAL --}}
                                    <div class="modal fade" id="completeModal-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="completed">
                                                
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-primary">Complete Appointment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="alert alert-light border mb-4">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <small class="text-uppercase text-muted fw-bold">Patient</small>
                                                                    <div class="fw-bold">{{ $appt->patient_name }}</div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <small class="text-uppercase text-muted fw-bold">Service</small>
                                                                    <div class="fw-bold">{{ $appt->service }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-medical me-2"></i>Medical Record Entry</h6>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-secondary">Diagnosis / Findings</label>
                                                            <textarea name="diagnosis" class="form-control" rows="3" placeholder="e.g. Myopia, Astigmatism, Healthy eyes..." required></textarea>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-secondary">Prescription / Treatment Plan</label>
                                                            <textarea name="prescription" class="form-control" rows="3" placeholder="e.g. OD: -1.50, OS: -1.75, Eye drops daily..."></textarea>
                                                            <div class="form-text">This information will be visible to the patient in their portal.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save & Complete</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- NO-SHOW CONFIRMATION MODAL --}}
                                    <div class="modal fade" id="noShowModal-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.appointment.status', $appt->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="no-show">
                                                
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-warning">Confirm No-Show</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-center">
                                                        <div class="mb-3">
                                                            <i class="bi bi-exclamation-circle-fill text-warning display-3"></i>
                                                        </div>
                                                        <h5 class="fw-bold text-dark">Mark as No-Show?</h5>
                                                        <p class="text-muted">
                                                            Are you sure <strong>{{ $appt->patient_name }}</strong> did not attend their appointment? 
                                                            <br><br>
                                                            <span class="text-danger small fw-bold">Note: This will add a STRIKE to the patient's record.</span>
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0 justify-content-center">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning text-white rounded-pill px-4 fw-bold">Yes, Mark No-Show</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">No upcoming appointments found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- PAGINATION: ONGOING --}}
                        <div class="row align-items-center p-3 border-top g-0">
                            <div class="col-lg-4 d-none d-lg-block order-lg-1"></div>
                            <div class="col-12 col-lg-4 text-center text-muted small order-2 order-lg-2 mt-2 mt-lg-0">
                                @if($confirmed->total() > 0)
                                    Showing {{ $confirmed->firstItem() }} to {{ $confirmed->lastItem() }} of {{ $confirmed->total() }} results
                                @else
                                    No results
                                @endif
                            </div>
                            <div class="col-12 col-lg-4 text-end order-1 order-lg-3">
                                {{ $confirmed->appends(['tab' => 'ongoing'])->links('partials.pagination') }}
                            </div>
                        </div>

                    </div>
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

<script>
    function toggleOther(select, id) {
        const otherDiv = document.getElementById('otherReasonDiv-' + id);
        const textArea = document.getElementById('textArea-' + id);
        
        if (select.value === 'Other') {
            otherDiv.classList.remove('d-none');
            textArea.required = true;
            textArea.value = '';
        } else {
            otherDiv.classList.add('d-none');
            textArea.required = false;
            textArea.value = select.value;
        }
    }
</script>
@endsection