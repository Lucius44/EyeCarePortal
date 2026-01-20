@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="fw-bold mb-0">Account Settings</h2>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i> Identity Verification</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="text-muted me-2">Current Status:</span>
                        @if(Auth::user()->is_verified)
                            <span class="badge bg-success">Verified</span>
                        @elseif(Auth::user()->id_photo_path)
                            <span class="badge bg-warning text-dark">Pending Approval</span>
                        @else
                            <span class="badge bg-danger">Unverified</span>
                        @endif
                    </div>
                    
                    <p class="text-muted small">Upload a government-issued ID to verify your account. This is required to book appointments.</p>

                    @if(Auth::user()->id_photo_path)
                        <div class="mb-3 p-2 border rounded bg-light d-inline-block">
                            <p class="fw-bold small mb-1">Uploaded ID:</p>
                            <img src="{{ asset('storage/' . Auth::user()->id_photo_path) }}" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    @endif

                    <form action="{{ route('settings.upload') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="id_photo" class="form-control" required accept="image/*">
                            <button type="submit" class="btn btn-primary">Upload ID</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-telephone me-2"></i> Contact Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.phone') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                                <input type="text" name="phone_number" class="form-control" 
                                       value="{{ Auth::user()->phone_number }}" 
                                       placeholder="e.g. 0912 345 6789">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-secondary">Update Phone</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-danger"><i class="bi bi-shield-lock me-2"></i> Security</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-danger">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection