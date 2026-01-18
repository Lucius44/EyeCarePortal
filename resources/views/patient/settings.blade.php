@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">My Profile</a>
            <a href="{{ route('appointments.index') }}" class="list-group-item list-group-item-action">Book Appointment</a>
            <a href="{{ route('my.appointments') }}" class="list-group-item list-group-item-action">My Appointments</a>
            <a href="{{ route('settings') }}" class="list-group-item list-group-item-action active">Account Settings</a>
            
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action text-danger">Logout</button>
            </form>
        </div>
    </div>

    <div class="col-md-9">
        <h2 class="mb-4">Account Settings</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">🆔 Identity Verification</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Status:</strong> 
                    @if(Auth::user()->is_verified)
                        <span class="badge bg-success">Verified</span>
                    @elseif(Auth::user()->id_photo_path)
                        <span class="badge bg-warning text-dark">Pending Approval</span>
                    @else
                        <span class="badge bg-danger">Unverified</span>
                    @endif
                </div>
                
                <p class="text-muted">Upload a government-issued ID to verify your account.</p>

                @if(Auth::user()->id_photo_path)
                    <div class="mb-3">
                        <p class="fw-bold">Current ID:</p>
                        <img src="{{ asset('storage/' . Auth::user()->id_photo_path) }}" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                @endif

                <form action="{{ route('settings.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="id_photo" class="form-control" required accept="image/*">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">📞 Contact Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.phone') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" 
                               value="{{ Auth::user()->phone_number }}" 
                               placeholder="e.g. 0912 345 6789">
                    </div>
                    <button type="submit" class="btn btn-secondary">Update Phone Number</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">🔒 Change Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <div class="form-text">Min 8 chars, 1 Uppercase, 1 Number</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger">Change Password</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection