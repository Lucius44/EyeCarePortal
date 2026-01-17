@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">My Profile</a>
            <a href="{{ route('appointments.index') }}" class="list-group-item list-group-item-action">My Appointments</a>
            <a href="{{ route('settings') }}" class="list-group-item list-group-item-action active">Account Settings</a>
            
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action text-danger">Logout</button>
            </form>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Account Verification</h4>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="alert alert-info">
                    <strong>Current Status:</strong> 
                    @if(Auth::user()->is_verified)
                        <span class="badge bg-success">Verified</span>
                    @elseif(Auth::user()->id_photo_path)
                        <span class="badge bg-warning text-dark">Pending Approval</span>
                    @else
                        <span class="badge bg-danger">Unverified</span>
                    @endif
                </div>

                <hr>

                <h5>Upload Valid ID</h5>
                <p class="text-muted">Please upload a clear photo of a government-issued ID (Driver's License, Passport, etc.) to verify your account.</p>

                @if(Auth::user()->id_photo_path)
                    <div class="mb-3">
                        <p class="fw-bold">Uploaded ID:</p>
                        <img src="{{ asset('storage/' . Auth::user()->id_photo_path) }}" class="img-thumbnail" style="max-width: 300px;">
                        <p class="text-muted small mt-2">You have already uploaded an ID. Uploading a new one will replace it.</p>
                    </div>
                @endif

                <form action="{{ route('settings.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="id_photo" class="form-label">Select Image</label>
                        <input type="file" name="id_photo" class="form-control" required accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload & Request Verification</button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection