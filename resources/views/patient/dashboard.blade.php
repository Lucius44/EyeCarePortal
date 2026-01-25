@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    {{-- 1. WELCOME HERO SECTION --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden" style="border-radius: 20px;">
                <div class="card-body p-5 position-relative">
                    <div class="position-relative z-2">
                        @php
                            $hour = date('H');
                            $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                        @endphp
                        <h1 class="fw-bold display-5">{{ $greeting }}, {{ Auth::user()->first_name }}!</h1>
                        <p class="lead opacity-75 mb-0">Welcome to your EyeCare Portal. We're here to help you see clearly.</p>
                    </div>
                    {{-- Decorative Icon Background --}}
                    <i class="bi bi-eye-fill position-absolute text-white" style="font-size: 15rem; opacity: 0.1; right: -2rem; top: -4rem;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. ALERTS (Verification) --}}
    @if(!Auth::user()->is_verified)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 p-3" role="alert">
            <div class="bg-warning bg-opacity-25 p-3 rounded-circle me-3 text-warning-emphasis">
                <i class="bi bi-shield-exclamation fs-4"></i>
            </div>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Account Unverified</h5>
                <p class="mb-0 small text-muted">
                    Please upload your ID in <a href="{{ route('settings') }}" class="fw-bold text-dark text-decoration-underline">Settings</a> to enable appointment booking.
                </p>
            </div>
        </div>
    @endif

    <div class="row g-4">
        
        {{-- 3. LEFT COLUMN: STATUS & APPOINTMENTS --}}
        <div class="col-lg-8">
            
            {{-- DYNAMIC APPOINTMENT CARD --}}
            <h5 class="fw-bold text-secondary mb-3">Current Status</h5>
            
            @if($activeAppointment)
                {{-- CASE A: User HAS an appointment --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-5 {{ $activeAppointment->status->value === 'confirmed' ? 'border-success' : 'border-warning' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                @if($activeAppointment->status->value === 'confirmed')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Confirmed Appointment
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-hourglass-split me-1"></i> Request Pending
                                    </span>
                                @endif
                            </div>
                            <div class="text-end">
                                <small class="text-muted fw-bold d-block">Reference ID</small>
                                <span class="font-monospace">#{{ str_pad($activeAppointment->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="bg-light p-3 rounded-4 me-3 text-center" style="min-width: 80px;">
                                <div class="text-danger fw-bold small text-uppercase">{{ $activeAppointment->appointment_date->format('M') }}</div>
                                <div class="display-6 fw-bold text-dark">{{ $activeAppointment->appointment_date->format('d') }}</div>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">{{ $activeAppointment->service }}</h4>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-clock me-1"></i> {{ $activeAppointment->appointment_time }}
                                </p>
                            </div>
                        </div>

                        <hr class="my-3 border-light">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Need to reschedule?</small>
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">Manage Appointment</a>
                        </div>
                    </div>
                </div>
            @else
                {{-- CASE B: NO Appointment --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3 text-muted opacity-50">
                            <i class="bi bi-calendar-check" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold">No Upcoming Appointments</h5>
                        <p class="text-muted small mb-3">It's important to have regular checkups to maintain healthy vision.</p>
                        @if(Auth::user()->is_verified)
                            <a href="{{ route('appointments.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> Book New Appointment
                            </a>
                        @else
                            <button class="btn btn-secondary rounded-pill px-4 fw-bold" disabled>Verify Account to Book</button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- QUICK SHORTCUTS --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('my.appointments') }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-4 hover-scale transition-all">
                            <div class="card-body p-4 d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                                    <i class="bi bi-clock-history fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">History</h6>
                                    <small class="text-muted">View past records</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('profile') }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-4 hover-scale transition-all">
                            <div class="card-body p-4 d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle me-3">
                                    <i class="bi bi-person-lines-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">My Profile</h6>
                                    <small class="text-muted">Update your details</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        {{-- 4. RIGHT COLUMN: STATS & INFO --}}
        <div class="col-lg-4">
            
            {{-- Account Verification Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">Account Status</h6>
                    <div class="d-flex align-items-center mb-3">
                        @if(Auth::user()->is_verified)
                            <div class="position-relative">
                                <img src="{{ Auth::user()->id_photo_path ? asset('storage/'.Auth::user()->id_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->first_name).'&background=0D6EFD&color=fff' }}" 
                                     class="rounded-circle object-fit-cover" 
                                     width="60" height="60" alt="Profile">
                                <span class="position-absolute bottom-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle">
                                    <span class="visually-hidden">Verified</span>
                                </span>
                            </div>
                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">Verified</h5>
                                <small class="text-success"><i class="bi bi-check-all"></i> All features unlocked</small>
                            </div>
                        @else
                            <div class="position-relative">
                                <div class="bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-person text-secondary fs-3"></i>
                                </div>
                                <span class="position-absolute bottom-0 start-100 translate-middle p-2 bg-warning border border-light rounded-circle">
                                    <span class="visually-hidden">Unverified</span>
                                </span>
                            </div>
                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">Unverified</h5>
                                <small class="text-muted">Limited Access</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats Card --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">My Journey</h6>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Completed Visits</span>
                        <span class="fw-bold fs-5">{{ $completedVisits }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Member Since</span>
                        <span class="fw-bold small">{{ Auth::user()->created_at->format('M Y') }}</span>
                    </div>

                    <hr class="border-light">

                    <a href="{{ route('settings') }}" class="btn btn-light w-100 rounded-pill text-muted fw-bold">
                        <i class="bi bi-gear-fill me-2"></i> Settings
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .hover-scale {
        transition: transform 0.2s ease-in-out;
    }
    .hover-scale:hover {
        transform: translateY(-5px);
        cursor: pointer;
    }
</style>
@endsection