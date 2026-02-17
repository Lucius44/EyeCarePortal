@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark">My Profile</h4>
                <a href="{{ route('settings') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold text-sm">
                    <i class="bi bi-gear-fill me-2"></i> Settings
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div style="height: 120px; background: linear-gradient(135deg, #0F172A 0%, #334155 100%);"></div>
                
                <div class="card-body p-4 p-lg-5 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle">
                        <div class="p-1 bg-white rounded-circle shadow-sm">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) }}&background=0F172A&color=fff&size=150" 
                                 class="rounded-circle" width="100" height="100" alt="Profile">
                        </div>
                    </div>

                    <div class="text-center mt-5 mb-5">
                        {{-- FIXED: Added Suffix to Full Name --}}
                        <h3 class="fw-bold mb-1">{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }} {{ Auth::user()->suffix }}</h3>
                        <p class="text-muted mb-2">{{ Auth::user()->email }}</p>
                        
                        <div class="d-flex justify-content-center gap-2 align-items-center flex-wrap mt-3">
                            {{-- [NEW] Account Status Badge (High Visibility) --}}
                            @if(Auth::user()->account_status === 'restricted')
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold border border-danger border-opacity-25">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Restricted Account
                                </span>
                            @elseif(Auth::user()->account_status === 'banned')
                                <span class="badge bg-dark rounded-pill px-3 py-2 fw-bold">
                                    <i class="bi bi-slash-circle-fill me-1"></i> Banned
                                </span>
                            @endif

                            {{-- Verification Badge --}}
                            @if(Auth::user()->is_verified)
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Verified Patient
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3 py-2 fw-bold">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Unverified
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 h-100">
                                <h6 class="fw-bold text-uppercase text-secondary small mb-4 ls-1">Personal Info</h6>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Date of Birth</label>
                                    <span class="fw-bold text-dark fs-5">
                                        {{ \Carbon\Carbon::parse(Auth::user()->birthday)->format('F d, Y') }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Gender</label>
                                    <span class="fw-bold text-dark fs-5">{{ ucfirst(Auth::user()->gender) }}</span>
                                </div>

                                {{-- Account Status Field --}}
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Account Status</label>
                                    @if(Auth::user()->account_status === 'restricted')
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-danger fs-5">
                                                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Restricted
                                            </span>
                                            {{-- Display End Date Below --}}
                                            @if(Auth::user()->restricted_until)
                                                <small class="text-danger fw-bold mt-1">
                                                    Ends: {{ Auth::user()->restricted_until->format('F d, Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    @elseif(Auth::user()->account_status === 'banned')
                                        <span class="fw-bold text-dark fs-5">Banned</span>
                                    @else
                                        <span class="fw-bold text-success fs-5">Active</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 h-100">
                                <h6 class="fw-bold text-uppercase text-secondary small mb-4 ls-1">Contact Details</h6>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Phone Number</label>
                                    @if(Auth::user()->phone_number)
                                        <span class="fw-bold text-dark fs-5">{{ Auth::user()->phone_number }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Not provided</span>
                                        <a href="{{ route('settings') }}" class="small ms-2 fw-bold text-decoration-none">Add</a>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Member Since</label>
                                    <span class="fw-bold text-dark fs-5">{{ Auth::user()->created_at->format('M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection