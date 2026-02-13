@extends('layouts.app')

@section('content')
<style>
    /* Admin Specific Overrides */
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); overflow-x: hidden; }
    
    /* Sidebar (Desktop) */
    .admin-sidebar { 
        width: 260px; 
        background: #0F172A; 
        color: #94a3b8; 
        flex-shrink: 0; 
        transition: all 0.3s;
        display: flex; /* Flex to push support line down */
        flex-direction: column;
        display: none; /* Default hidden on mobile */
    }
    
    /* Content Area */
    .admin-content { 
        flex-grow: 1; 
        background: #F1F5F9; 
        padding: 1.5rem; 
        min-width: 0; 
    }

    /* Desktop View Media Query */
    @media (min-width: 992px) {
        .admin-sidebar { display: flex; } /* Flex for sidebar layout */
        .admin-content { padding: 2rem; }
    }
    
    /* Sidebar Links Styling */
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
        
        {{-- DESKTOP SIDEBAR --}}
        <div class="admin-sidebar p-3 d-none d-lg-flex">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            {{-- LOAD THE SHARED NAV LINKS --}}
            @include('admin.partials.nav_links')

            {{-- Support Line --}}
            <div class="mt-auto p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
                <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="admin-content">
            
            {{-- HEADER SECTION --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div class="d-flex align-items-center gap-3">
                    {{-- MOBILE MENU TOGGLE --}}
                    <button class="btn btn-white border shadow-sm d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu">
                        <i class="bi bi-list fs-5 text-primary"></i>
                    </button>
                    
                    <div>
                        <h2 class="fw-bold text-dark mb-1">Dashboard</h2>
                        <p class="text-secondary mb-0 small">Welcome back, Admin.</p>
                    </div>
                </div>
                <div>
                    <span class="bg-white border px-3 py-2 rounded-pill shadow-sm fw-bold text-secondary small d-inline-flex align-items-center">
                        <i class="bi bi-calendar-event me-2 text-primary"></i> {{ now()->format('l, F d, Y') }}
                    </span>
                </div>
            </div>

            {{-- STATS GRID --}}
            <div class="row g-3 g-md-4 mb-5">
                <div class="col-12 col-sm-6 col-md-3">
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
                
                <div class="col-12 col-sm-6 col-md-3">
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

                <div class="col-12 col-sm-6 col-md-3">
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

                <div class="col-12 col-sm-6 col-md-3">
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

            {{-- CHARTS & ACTIONS --}}
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Clinic Traffic</h5>
                            <small class="text-muted">Appointments over the last 7 days</small>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div style="position: relative; height: 100%; width: 100%; min-height: 250px;">
                                <canvas id="appointmentsChart" 
                                        data-labels="{{ json_encode($labels) }}" 
                                        data-counts="{{ json_encode($data) }}"></canvas>
                            </div>
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

{{-- MOBILE OFF CANVAS MENU --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileAdminMenu" style="background: #0F172A; width: 280px;">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-white fw-bold">Admin Console</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        {{-- LOAD THE SHARED NAV LINKS --}}
        @include('admin.partials.nav_links')
        
        <div class="mt-5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
            <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
            <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
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
                maintainAspectRatio: false, // Allows chart to resize in container
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