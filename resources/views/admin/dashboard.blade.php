@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mb-2 text-start">
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
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Admin Dashboard</h2>

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-primary border-4">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-2">Total Patients</h6>
                            <h3 class="fw-bold text-primary">{{ $totalPatients }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-success border-4">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-2">Appointments Today</h6>
                            <h3 class="fw-bold text-success">{{ $appointmentsToday }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-warning border-4">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-2">Pending Requests</h6>
                            <h3 class="fw-bold text-warning">{{ $pendingRequests }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-info border-4">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-2">Total Completed</h6>
                            <h3 class="fw-bold text-info">{{ $totalCompleted }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Section --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Appointments (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    {{-- FIX: Pass data via data attributes to prevent IDE syntax errors --}}
                    <canvas id="appointmentsChart" 
                            data-labels="{{ json_encode($labels) }}" 
                            data-counts="{{ json_encode($data) }}"
                            height="100"></canvas>
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
                label: 'Number of Appointments',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endsection