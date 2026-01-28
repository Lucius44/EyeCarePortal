@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4 fw-bold">EyeCare Admin</h5>
                
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mb-2 text-start shadow-sm">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.calendar') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
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

        {{-- Main Content --}}
        <div class="col-md-10 p-4 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Overview</h2>
                    <p class="text-muted">Welcome back, Admin. Here's what's happening today.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-calendar-event me-2 text-primary"></i> {{ now()->format('l, F d, Y') }}
                    </span>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row g-4 mb-4">
                {{-- Total Patients --}}
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-square bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Total Patients</h6>
                                <h3 class="fw-bold mb-0">{{ $totalPatients }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Appointments Today --}}
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-square bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                                <i class="bi bi-calendar-check-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Today's Visits</h6>
                                <h3 class="fw-bold mb-0">{{ $appointmentsToday }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Requests --}}
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-square bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Pending Requests</h6>
                                <h3 class="fw-bold mb-0">{{ $pendingRequests }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Completed --}}
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-square bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                                <i class="bi bi-clipboard-check-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Completed</h6>
                                <h3 class="fw-bold mb-0">{{ $totalCompleted }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Chart Section --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0">Appointment Analytics</h5>
                            <small class="text-muted">Traffic over the last 7 days</small>
                        </div>
                        <div class="card-body">
                            {{-- Data Attributes for JS --}}
                            <canvas id="appointmentsChart" 
                                    data-labels="{{ json_encode($labels) }}" 
                                    data-counts="{{ json_encode($data) }}"
                                    height="120"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-3">
                                <a href="{{ route('admin.calendar') }}" class="btn btn-outline-primary text-start p-3 rounded-3 border-2 fw-bold">
                                    <i class="bi bi-plus-circle-fill me-2"></i> Book Walk-in
                                </a>
                                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-success text-start p-3 rounded-3 border-2 fw-bold">
                                    <i class="bi bi-check-lg me-2"></i> Review Pending ({{ $pendingRequests }})
                                </a>
                                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary text-start p-3 rounded-3 border-2 fw-bold">
                                    <i class="bi bi-person-badge me-2"></i> Verify Patients
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('appointmentsChart');
    
    // Parse the data from the HTML attributes
    const labels = JSON.parse(ctx.getAttribute('data-labels'));
    const data = JSON.parse(ctx.getAttribute('data-counts'));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Appointments',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#000',
                    bodyColor: '#666',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] },
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endsection