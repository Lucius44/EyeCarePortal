@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">My Profile</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Full Name:</div>
                    <div class="col-md-8">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Email:</div>
                    <div class="col-md-8">{{ $user->email }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Birthday:</div>
                    <div class="col-md-8">{{ \Carbon\Carbon::parse($user->birthday)->format('F d, Y') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Gender:</div>
                    <div class="col-md-8">{{ $user->gender }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Phone Number:</div>
                    <div class="col-md-8">{{ $user->phone_number ?? 'Not Set' }}</div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                    <button class="btn btn-warning">Edit Profile (Coming Soon)</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection