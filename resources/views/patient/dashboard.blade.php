@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card p-5 shadow-sm border-0" style="border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Welcome back, {{ Auth::user()->first_name }}!</h2>
                <p class="text-muted">Here is an overview of your account.</p>
            </div>
            @if(Auth::user()->is_verified)
                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-patch-check-fill me-1"></i> Verified Account</span>
            @else
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-exclamation-triangle-fill me-1"></i> Unverified</span>
            @endif
        </div>
        
        @if(!Auth::user()->is_verified)
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Action Required:</strong> Please upload your ID in <a href="{{ route('settings') }}" class="alert-link">Settings</a> to verify your account and enable booking features.
                </div>
            </div>
        @endif
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white h-100 border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-4 d-flex flex-column justify-content-center text-center">
                        <div class="mb-3">
                            <i class="bi bi-calendar-plus display-4 opacity-75"></i>
                        </div>
                        <h3 class="fw-bold">Book Appointment</h3>
                        <p class="opacity-75 mb-4">Schedule your next comprehensive eye checkup or consultation with ease.</p>
                        <a href="{{ route('appointments.index') }}" class="btn btn-light text-primary fw-bold stretched-link">Book Now</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-success text-white h-100 border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-4 d-flex flex-column justify-content-center text-center">
                        <div class="mb-3">
                            <i class="bi bi-folder2-open display-4 opacity-75"></i>
                        </div>
                        <h3 class="fw-bold">My History</h3>
                        <p class="opacity-75 mb-4">View your past appointment history, statuses, and doctor's notes.</p>
                        <a href="{{ route('my.appointments') }}" class="btn btn-light text-success fw-bold stretched-link">View History</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection