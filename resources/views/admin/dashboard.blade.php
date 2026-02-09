@extends('layouts.app')

@section('content')
<style>
    /* Admin Specific Overrides */
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); }
    .admin-sidebar { width: 260px; background: #0F172A; color: #94a3b8; flex-shrink: 0; transition: all 0.3s; }
    .admin-content { flex-grow: 1; background: #F1F5F9; padding: 2rem; }
    
    /* Sidebar Links */
    .admin-nav-link {
        display: flex; align-items: center; padding: 12px 20px;
        color: #94a3b8; text-decoration: none; font-weight: 500;
        border-radius: 8px; margin-bottom: 5px; transition: all 0.2s;
    }
    .admin-nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav-link.active { background: #3B82F6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .admin-nav-link i { font-size: 1.1rem; margin-right: 12px; }

    /* Stats Cards */
    .stat-card {
        background: white; border-radius: 16px; padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0; height: 100%;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .icon-box {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin-right: 1rem;
    }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
        <div class="admin-sidebar p-3 d-none d-lg-block">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            <nav class="nav flex-column gap-1">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link active">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
                <a href="{{ route('admin.calendar') }}" class="admin-nav-link">
                    <i class="bi bi-calendar-week"></i> Calendar
                </a>
                <a href="{{ route('admin.appointments') }}" class="admin-nav-link">
                    <i class="bi bi-calendar-check"></i> Appointments
                </a>
                <a href="{{ route('admin.history') }}" class="admin-nav-link">
                    <i class="bi bi-clock-history"></i> History
                </a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link">
                    <i class="bi bi-people"></i> Users & Patients
                </a>
            </nav>
            
            <div class="mt-auto px-2 pt-5">
                <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                    <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
                    <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
                </div>
            </div>
        </div>

        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Dashboard Overview</h2>
                    <p class="text-secondary mb-0">Welcome back, Admin. Here's what's happening today.</p>
                </div>
                <div>
                    <span class="bg-white border px-3 py-2 rounded-pill shadow-sm fw-bold text-secondary small">
                        <i class="bi bi-calendar-event me-2 text-primary"></i> {{ now()->format('l, F d, Y') }}
                    </span>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stat-card d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-1">Total Patients</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $totalPatients }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-1">Visits Today</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $appointmentsToday }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card d-flex align-items-center">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-1">Pending</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $pendingRequests }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clipboard-check-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-1">Completed</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $totalCompleted }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Clinic Traffic</h5>
                            <small class="text-muted">Appointments over the last 7 days</small>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <canvas id="appointmentsChart" 
                                    data-labels="{{ json_encode($labels) }}" 
                                    data-counts="{{ json_encode($data) }}"
                                    height="120"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-grid gap-3">
                                <a href="{{ route('admin.calendar') }}" class="btn btn-outline-light text-dark text-start p-3 rounded-3 border fw-bold d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-plus-lg"></i>
                                    </div>
                                    <div>
                                        <span class="d-block">Book Walk-in</span>
                                        <small class="text-muted fw-normal">Add manual appointment</small>
                                    </div>
                                </a>

                                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-light text-dark text-start p-3 rounded-3 border fw-bold d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    <div>
                                        <span class="d-block">Review Requests</span>
                                        <small class="text-muted fw-normal">{{ $pendingRequests }} pending approval</small>
                                    </div>
                                </a>

                                <a href="{{ route('admin.users') }}" class="btn btn-outline-light text-dark text-start p-3 rounded-3 border fw-bold d-flex align-items-center">
                                    <div class="bg-warning text-dark rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div>
                                        <span class="d-block">Verify Patients</span>
                                        <small class="text-muted fw-normal">Check uploaded IDs</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('appointmentsChart');
    if(ctx) {
        const labels = JSON.parse(ctx.getAttribute('data-labels'));
        const data = JSON.parse(ctx.getAttribute('data-counts'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Appointments',
                    data: data,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3B82F6',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#e2e8f0' },
                        ticks: { stepSize: 1, color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    }
</script>
@endsection