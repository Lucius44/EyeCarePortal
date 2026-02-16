@extends('layouts.app')

@section('content')
<style>
    /* --- DASHBOARD SPECIFIC STYLES --- */
    
    /* Hero Card */
    .dashboard-hero {
        background: linear-gradient(120deg, #0F172A 0%, #1e293b 100%);
        border-radius: 24px;
        padding: 3rem;
        position: relative;
        overflow: hidden;
        color: white;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
    }
    
    .hero-bg-img {
        position: absolute;
        top: 0;
        right: 0;
        width: 50%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
        mask-image: linear-gradient(to left, black, transparent);
        -webkit-mask-image: linear-gradient(to left, black, transparent);
    }

    /* Action Cards */
    .action-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 1.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .action-card:hover:not(.disabled) {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        border-color: transparent;
    }

    .action-card.disabled {
        background: #f8fafc;
        opacity: 0.7;
        cursor: not-allowed;
    }

    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    /* Appointment Ticket Style */
    .appointment-ticket {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
        border: 1px solid #f1f5f9;
        position: relative;
    }
    
    .ticket-header {
        background: #f8fafc;
        padding: 1.5rem;
        border-bottom: 1px dashed #cbd5e1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ticket-body {
        padding: 2rem;
    }
    
    .date-box {
        text-align: center;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 10px 20px;
        min-width: 90px;
    }
    
    /* Profile Sidebar */
    .profile-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        text-align: center;
        border: 1px solid #f1f5f9;
    }
    
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
        align-items: center;
    }
    .stat-row:last-child { border-bottom: none; }

</style>

<div class="container py-4">
    
    <div class="row mb-5">
        <div class="col-12">
            <div class="dashboard-hero">
                <img src="https://images.unsplash.com/photo-1579684385180-1ea55f61d21e?q=80&w=2070&auto=format&fit=crop" class="hero-bg-img" alt="Background">
                
                <div class="position-relative z-2">
                    @php
                        $hour = date('H');
                        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                    @endphp
                    <span class="badge bg-white bg-opacity-20 backdrop-blur text-white px-3 py-2 rounded-pill mb-3 fw-bold border border-white border-opacity-25">
                        <i class="bi bi-calendar-day me-2"></i> {{ date('l, F j, Y') }}
                    </span>
                    <h1 class="display-5 fw-bold mb-2">{{ $greeting }}, {{ Auth::user()->first_name }}!</h1>
                    <p class="lead opacity-75 mb-4" style="max-width: 600px;">
                        Welcome to your personal vision portal. Manage your appointments and track your eye health journey all in one place.
                    </p>
                    
                    {{-- [NEW] RESTRICTED ACCOUNT ALERT --}}
                    @if(Auth::user()->account_status === 'restricted')
                        <div class="d-inline-flex align-items-center bg-danger border border-danger text-white px-4 py-3 rounded-4 backdrop-blur shadow-sm mb-3 me-2">
                            <i class="bi bi-exclamation-octagon fs-3 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Account Restricted</h6>
                                <span class="small opacity-90">Booking privileges are suspended due to multiple violations.</span>
                            </div>
                        </div>
                    @endif

                    @if(!Auth::user()->is_verified)
                        {{-- UPDATED ALERT TEXT COLOR: text-dark for better contrast on yellow --}}
                        <div class="d-inline-flex align-items-center bg-warning border border-warning text-dark px-4 py-3 rounded-4 backdrop-blur shadow-sm mb-3">
                            <i class="bi bi-shield-exclamation fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Action Required: Verify Account</h6>
                                <a href="{{ route('settings') }}" class="small text-dark text-decoration-underline fw-bold">Upload ID Now &rarr;</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8">
            
            <h5 class="fw-bold text-dark mb-4 px-1">Upcoming Schedule</h5>

            @if(isset($activeAppointment) && $activeAppointment)
                <div class="appointment-ticket mb-5">
                    <div class="ticket-header">
                        <div>
                            @if($activeAppointment->status->value === 'confirmed')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold border border-success border-opacity-25">
                                    <i class="bi bi-check-circle-fill me-1"></i> Confirmed
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis px-3 py-2 rounded-pill fw-bold border border-warning border-opacity-25">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending Approval
                                </span>
                            @endif
                        </div>
                        <div class="text-secondary small fw-bold">
                            REF #{{ str_pad($activeAppointment->id, 6, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>
                    
                    <div class="ticket-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="date-box">
                                    <div class="text-uppercase text-danger fw-bold small">{{ $activeAppointment->appointment_date->format('M') }}</div>
                                    <div class="display-6 fw-bold text-dark">{{ $activeAppointment->appointment_date->format('d') }}</div>
                                    <div class="text-muted small">{{ $activeAppointment->appointment_date->format('Y') }}</div>
                                </div>
                            </div>
                            <div class="col ps-3">
                                <h4 class="fw-bold text-primary mb-1">{{ $activeAppointment->service }}</h4>
                                <div class="d-flex align-items-center text-secondary mb-2">
                                    <i class="bi bi-clock me-2"></i> {{ $activeAppointment->appointment_time }}
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-geo-alt me-2"></i> ClearOptics Clinic
                                </div>
                            </div>
                            <div class="col-md-auto mt-3 mt-md-0 text-end">
                                <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                                    Manage
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 mb-5 bg-light position-relative overflow-hidden">
                    <div class="card-body p-5 text-center position-relative z-1">
                        <div class="mb-3">
                            <div class="bg-white p-3 rounded-circle d-inline-flex shadow-sm text-primary">
                                <i class="bi bi-calendar-plus" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold">No Upcoming Appointments</h4>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                            Regular eye exams are key to maintaining good vision. Schedule your next checkup today.
                        </p>
                        
                        {{-- Logic to disable booking button if restricted --}}
                        @if(Auth::user()->account_status === 'restricted')
                            <button class="btn btn-danger rounded-pill px-5 py-3 fw-bold shadow-lg" disabled>
                                <i class="bi bi-slash-circle me-2"></i> Account Restricted
                            </button>
                        @elseif(Auth::user()->is_verified)
                            <a href="{{ route('appointments.index') }}" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg">
                                <i class="bi bi-plus-lg me-2"></i> Book Appointment
                            </a>
                        @else
                            <button class="btn btn-secondary rounded-pill px-5 py-3 fw-bold" disabled>Verify Account First</button>
                        @endif
                    </div>
                    <i class="bi bi-calendar-check position-absolute text-muted opacity-10" style="font-size: 15rem; right: -3rem; bottom: -3rem; opacity: 0.05;"></i>
                </div>
            @endif

            <h5 class="fw-bold text-dark mb-4 px-1">Quick Actions</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    @if(Auth::user()->account_status === 'restricted')
                        {{-- Disabled Card for Restricted Users --}}
                        <div class="action-card disabled">
                            <div>
                                <div class="action-icon bg-secondary text-white bg-opacity-50">
                                    <i class="bi bi-calendar-x-fill"></i>
                                </div>
                                <h6 class="fw-bold text-muted mb-1">Booking Unavailable</h6>
                                <p class="text-muted small mb-0">Your account is currently restricted.</p>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('appointments.index') }}" class="text-decoration-none">
                            <div class="action-card">
                                <div>
                                    <div class="action-icon bg-blue-50 text-primary bg-primary bg-opacity-10">
                                        <i class="bi bi-calendar-plus-fill"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Book New Visit</h6>
                                    <p class="text-muted small mb-0">Schedule a checkup or consultation.</p>
                                </div>
                                <div class="mt-3 text-end">
                                    <span class="btn btn-sm btn-light rounded-pill fw-bold"><i class="bi bi-arrow-right"></i></span>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>

                <div class="col-md-6">
                    <a href="{{ route('my.appointments') }}" class="text-decoration-none">
                        <div class="action-card">
                            <div>
                                <div class="action-icon bg-green-50 text-success bg-success bg-opacity-10">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">View History</h6>
                                <p class="text-muted small mb-0">Check past prescriptions and visits.</p>
                            </div>
                            <div class="mt-3 text-end">
                                <span class="btn btn-sm btn-light rounded-pill fw-bold"><i class="bi bi-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            
            <div class="profile-card mb-4">
                <div class="position-relative d-inline-block mb-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) }}&background=0F172A&color=fff&size=128" 
                         class="rounded-circle shadow-sm" width="80" height="80" alt="Profile">
                    
                    @if(Auth::user()->is_verified)
                        <span class="position-absolute bottom-0 start-100 translate-middle p-2 bg-success border border-white rounded-circle" data-bs-toggle="tooltip" title="Verified Account">
                            <span class="visually-hidden">Verified</span>
                        </span>
                    @endif
                </div>
                
                <h5 class="fw-bold mb-1">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
                <p class="text-muted small mb-4">{{ Auth::user()->email }}</p>
                
                <div class="d-grid">
                    <a href="{{ route('profile') }}" class="btn btn-outline-secondary rounded-pill fw-bold btn-sm">View Full Profile</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-secondary small mb-3 ls-1">Health Overview</h6>
                
                <div class="stat-row">
                    <span class="text-muted">Status</span>
                    {{-- [NEW] STATUS BADGE LOGIC --}}
                    @if(Auth::user()->account_status === 'restricted')
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Restricted</span>
                    @elseif(Auth::user()->account_status === 'banned')
                        <span class="badge bg-dark text-white rounded-pill px-3">Banned</span>
                    @elseif(Auth::user()->is_verified)
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                    @else
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Unverified</span>
                    @endif
                </div>
                
                <div class="stat-row">
                    <span class="text-muted">Total Visits</span>
                    <span class="fw-bold text-dark">{{ $completedVisits ?? 0 }}</span>
                </div>

                <div class="stat-row">
                    <span class="text-muted">Member Since</span>
                    <span class="fw-bold text-dark">{{ Auth::user()->created_at->format('M Y') }}</span>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection