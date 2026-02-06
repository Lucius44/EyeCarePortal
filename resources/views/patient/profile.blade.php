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
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=0F172A&color=fff&size=150" 
                                 class="rounded-circle" width="100" height="100" alt="Profile">
                        </div>
                    </div>

                    <div class="text-center mt-5 mb-5">
                        <h3 class="fw-bold mb-1">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</h3>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        
                        @if($user->is_verified)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold">
                                <i class="bi bi-check-circle-fill me-1"></i> Verified Patient
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3 py-2 fw-bold">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Unverified
                            </span>
                        @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 h-100">
                                <h6 class="fw-bold text-uppercase text-secondary small mb-4 ls-1">Personal Info</h6>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Date of Birth</label>
                                    <span class="fw-bold text-dark fs-5">
                                        {{ \Carbon\Carbon::parse($user->birthday)->format('F d, Y') }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Gender</label>
                                    <span class="fw-bold text-dark fs-5">{{ $user->gender }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 h-100">
                                <h6 class="fw-bold text-uppercase text-secondary small mb-4 ls-1">Contact Details</h6>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Phone Number</label>
                                    @if($user->phone_number)
                                        <span class="fw-bold text-dark fs-5">{{ $user->phone_number }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Not provided</span>
                                        <a href="{{ route('settings') }}" class="small ms-2 fw-bold text-decoration-none">Add</a>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <label class="small text-muted d-block mb-1">Member Since</label>
                                    <span class="fw-bold text-dark fs-5">{{ $user->created_at->format('M Y') }}</span>
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