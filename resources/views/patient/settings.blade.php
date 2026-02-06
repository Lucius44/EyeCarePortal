@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark mb-1">Account Settings</h2>
            <p class="text-muted">Manage your verification, security, and preferences.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                            <i class="bi bi-person-badge fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Identity Verification</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">
                        To book appointments, we need to verify your identity. Please upload a clear photo of a valid Government ID.
                    </p>

                    @if(Auth::user()->is_verified)
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success d-flex align-items-center rounded-3">
                            <i class="bi bi-patch-check-fill fs-4 me-3"></i>
                            <div>
                                <strong>Verified</strong><br>
                                <span class="small">You are eligible to book appointments.</span>
                            </div>
                        </div>
                        @if(Auth::user()->id_photo_path)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">Current ID on file:</small>
                                <img src="{{ asset('storage/' . Auth::user()->id_photo_path) }}" class="img-fluid rounded-3 border" style="max-height: 150px; object-fit: contain;">
                            </div>
                        @endif
                    @else
                        <form action="{{ route('settings.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="id_photo" class="form-label fw-bold small">Upload ID (JPG/PNG)</label>
                                <input class="form-control" type="file" id="id_photo" name="id_photo" required>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                                Upload for Verification
                            </button>
                        </form>
                        @if(Auth::user()->id_photo_path)
                            <div class="mt-3 text-center">
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Review Pending</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-3">
                            <i class="bi bi-phone fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Contact Number</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.phone') }}" method="POST">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="text" name="phone_number" class="form-control" placeholder="09123456789" value="{{ Auth::user()->phone_number }}">
                            <button class="btn btn-outline-primary" type="submit">Update</button>
                        </div>
                        <div class="form-text text-muted small">
                            Used for appointment notifications.
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3 me-3">
                            <i class="bi bi-shield-lock fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Change Password</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.password') }}" method="POST">
                        @csrf
                        <div class="form-floating mb-3">
                            <input type="password" name="current_password" class="form-control" id="curPass" placeholder="Current" required>
                            <label for="curPass">Current Password</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="newPass" placeholder="New" required>
                            <label for="newPass">New Password</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="password_confirmation" class="form-control" id="conPass" placeholder="Confirm" required>
                            <label for="conPass">Confirm New Password</label>
                        </div>
                        <button type="submit" class="btn btn-dark rounded-pill w-100 fw-bold">Update Password</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection