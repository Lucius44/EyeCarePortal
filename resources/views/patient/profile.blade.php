@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            {{-- Profile Card --}}
            <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                {{-- Banner/Cover --}}
                <div class="bg-primary" style="height: 150px; background: linear-gradient(45deg, #0d6efd, #0dcaf0);"></div>
                
                <div class="card-body p-0">
                    <div class="row g-0">
                        {{-- Left Column: Avatar & Identity --}}
                        <div class="col-md-4 text-center border-end bg-light p-4">
                            <div class="position-relative d-inline-block mt-n5 mb-3">
                                {{-- Auto-generated Avatar based on Name --}}
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=ffffff&color=0d6efd&size=128&bold=true" 
                                     class="rounded-circle shadow-lg border border-4 border-white" 
                                     alt="Profile Avatar" width="128" height="128">
                                
                                @if($user->is_verified)
                                    <span class="position-absolute bottom-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle" title="Verified Patient">
                                        <span class="visually-hidden">Verified</span>
                                    </span>
                                @endif
                            </div>
                            
                            <h4 class="fw-bold mb-1">{{ $user->first_name }} {{ $user->last_name }}</h4>
                            <p class="text-muted small mb-3">{{ $user->email }}</p>
                            
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">
                                    Member since {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>

                            {{-- REMOVED: Edit Button --}}
                            <div class="alert alert-light text-muted small border-0 bg-transparent">
                                <i class="bi bi-info-circle me-1"></i> Identity details are locked for verification purposes.
                            </div>
                        </div>

                        {{-- Right Column: Details --}}
                        <div class="col-md-8 p-5">
                            <h5 class="fw-bold text-secondary text-uppercase small mb-4">Personal Information</h5>
                            
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">First Name</label>
                                    <span class="fs-5 fw-medium text-dark">{{ $user->first_name }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Last Name</label>
                                    <span class="fs-5 fw-medium text-dark">{{ $user->last_name }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Middle Name</label>
                                    <span class="fs-5 fw-medium text-dark">{{ $user->middle_name ?? '-' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Gender</label>
                                    <span class="fs-5 fw-medium text-dark">{{ $user->gender }}</span>
                                </div>
                                <div class="col-sm-12">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Date of Birth</label>
                                    <span class="fs-5 fw-medium text-dark">
                                        {{ \Carbon\Carbon::parse($user->birthday)->format('F d, Y') }} 
                                        <small class="text-muted fw-normal">({{ \Carbon\Carbon::parse($user->birthday)->age }} years old)</small>
                                    </span>
                                </div>
                            </div>

                            <hr class="my-4 border-light">
                            
                            <h5 class="fw-bold text-secondary text-uppercase small mb-3">Contact Information</h5>
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-3">
                                    <i class="bi bi-telephone text-primary"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold">{{ $user->phone_number ?? 'Not Set' }}</span>
                                    <small class="text-muted">Primary Mobile</small>
                                </div>
                                <a href="{{ route('settings') }}" class="ms-auto btn btn-sm btn-light text-primary fw-bold">Update</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection