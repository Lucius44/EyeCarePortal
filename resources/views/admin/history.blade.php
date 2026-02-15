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

    .table-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
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
            {{-- HEADER --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <button class="btn btn-white border shadow-sm d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu">
                    <i class="bi bi-list fs-5 text-primary"></i>
                </button>
                <h2 class="fw-bold text-dark mb-0">Appointment History</h2>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.history') }}" method="GET" class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Patient Name..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Filter by Status</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="no-show" {{ request('status') == 'no-show' ? 'selected' : '' }}>No-Show</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Apply Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.history') }}" class="btn btn-light w-100 text-muted border">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 text-secondary small text-uppercase">Patient</th>
                                <th class="py-3 text-secondary small text-uppercase">Type</th>
                                <th class="py-3 text-secondary small text-uppercase">Date</th>
                                <th class="py-3 text-secondary small text-uppercase">Status</th>
                                <th class="py-3 text-secondary small text-uppercase">Actions / Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $appt)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $appt->patient_name }}</td>
                                <td>
                                    @if($appt->user_id)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-2">Registered</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 rounded-pill px-2">Guest</span>
                                    @endif
                                </td>
                                <td>{{ $appt->appointment_date->format('M d, Y') }}</td>
                                <td>
                                    @if($appt->status->value === 'completed') 
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Completed</span>
                                    @elseif($appt->status->value === 'cancelled') 
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Cancelled</span>
                                    @elseif($appt->status->value === 'rejected') 
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Rejected</span>
                                    @elseif($appt->status->value === 'no-show') 
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3">No-Show</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if($appt->status->value === 'completed')
                                        <button class="btn btn-link p-0 text-primary fw-bold text-decoration-none small" data-bs-toggle="modal" data-bs-target="#recordModal-{{ $appt->id }}">
                                            View Results <i class="bi bi-arrow-right"></i>
                                        </button>

                                        {{-- Medical Record Modal --}}
                                        <div class="modal fade" id="recordModal-{{ $appt->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-primary">Medical Record</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <small class="text-uppercase text-muted fw-bold">Diagnosis</small>
                                                            <div class="p-3 bg-light rounded mt-1">{{ $appt->diagnosis ?? 'No diagnosis recorded.' }}</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <small class="text-uppercase text-muted fw-bold">Prescription</small>
                                                            <div class="p-3 bg-light rounded mt-1">{{ $appt->prescription ?? 'No prescription recorded.' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{ $appt->cancellation_reason ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No history records found matching criteria.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
@endsection