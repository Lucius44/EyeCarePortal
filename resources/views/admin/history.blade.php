@extends('layouts.app')

@section('content')
<style>
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); }
    .admin-sidebar { width: 260px; background: #0F172A; color: #94a3b8; flex-shrink: 0; }
    .admin-content { flex-grow: 1; background: #F1F5F9; padding: 2rem; }
    
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
        
        <div class="admin-sidebar p-3 d-none d-lg-block">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            <nav class="nav flex-column gap-1">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a href="{{ route('admin.calendar') }}" class="admin-nav-link"><i class="bi bi-calendar-week"></i> Calendar</a>
                <a href="{{ route('admin.appointments') }}" class="admin-nav-link"><i class="bi bi-calendar-check"></i> Appointments</a>
                <a href="{{ route('admin.history') }}" class="admin-nav-link active"><i class="bi bi-clock-history"></i> History</a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link"><i class="bi bi-people"></i> Users & Patients</a>
            </nav>
        </div>

        <div class="admin-content">
            <h2 class="fw-bold text-dark mb-4">Appointment History</h2>

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
                                <th class="py-3 text-secondary small text-uppercase">Notes</th>
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
                                    {{ $appt->cancellation_reason ?? '-' }}
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
@endsection