@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action active">Dashboard</a>
            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">My Profile</a>
            <a href="{{ route('my.appointments') }}" class="list-group-item list-group-item-action">My Appointments</a>
            <a href="{{ route('settings') }}" class="list-group-item list-group-item-action">Account Settings</a>
            
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action text-danger">Logout</button>
            </form>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card p-4 shadow-sm">
            <h2 class="mb-4">Welcome, {{ Auth::user()->first_name }}!</h2>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h3>📅 Book Appointment</h3>
                            <p>Schedule your next eye checkup.</p>
                            <a href="{{ route('appointments.index') }}" class="btn btn-light">Book Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h3>📂 My Records</h3>
                            <p>View past prescriptions and history.</p>
                            <button class="btn btn-light">View History</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-light rounded border">
                <h5>Account Status: 
                    @if(Auth::user()->is_verified)
                        <span class="badge bg-success">Verified</span>
                    @else
                        <span class="badge bg-warning text-dark">Unverified</span>
                    @endif
                </h5>
                @if(!Auth::user()->is_verified)
                    <p class="text-muted small mb-0">Please upload your ID in Settings to verify your account and enable booking.</p>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection