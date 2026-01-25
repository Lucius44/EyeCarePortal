@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-4 text-dark">Account Settings</h2>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger rounded-4 mb-4 shadow-sm border-0">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. IDENTITY VERIFICATION CARD (Visual Stepper) --}}
            <div class="card shadow-sm mb-5 border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i> Identity Verification</h5>
                </div>
                <div class="card-body p-4">
                    
                    {{-- Status Stepper --}}
                    <div class="position-relative m-4">
                        <div class="progress" style="height: 2px;">
                            @php
                                $progress = 0;
                                if(Auth::user()->id_photo_path) $progress = 50;
                                if(Auth::user()->is_verified) $progress = 100;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%;"></div>
                        </div>
                        <div class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;">1</div>
                        <div class="position-absolute top-0 start-50 translate-middle btn btn-sm {{ Auth::user()->id_photo_path ? 'btn-success' : 'btn-secondary' }} rounded-pill" style="width: 2rem; height:2rem;">2</div>
                        <div class="position-absolute top-0 start-100 translate-middle btn btn-sm {{ Auth::user()->is_verified ? 'btn-success' : 'btn-secondary' }} rounded-pill" style="width: 2rem; height:2rem;">3</div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small px-2 mb-4">
                        <span>Upload ID</span>
                        <span>Pending Review</span>
                        <span>Verified</span>
                    </div>

                    {{-- Current Status Message --}}
                    <div class="bg-light p-3 rounded-3 mb-4 text-center">
                        @if(Auth::user()->is_verified)
                            <h6 class="text-success fw-bold mb-1"><i class="bi bi-patch-check-fill me-2"></i>Account Verified</h6>
                            <p class="mb-0 small text-muted">You have full access to booking features.</p>
                        @elseif(Auth::user()->id_photo_path)
                            <h6 class="text-warning text-dark fw-bold mb-1"><i class="bi bi-hourglass-split me-2"></i>Under Review</h6>
                            <p class="mb-0 small text-muted">Our admin is reviewing your ID. This usually takes 24 hours.</p>
                        @else
                            <h6 class="text-danger fw-bold mb-1"><i class="bi bi-exclamation-circle me-2"></i>Action Required</h6>
                            <p class="mb-0 small text-muted">Please upload a valid Government ID to start booking.</p>
                        @endif
                    </div>

                    {{-- Upload Form --}}
                    @if(!Auth::user()->is_verified)
                        <label class="form-label fw-bold small text-uppercase">Update / Upload ID</label>
                        <form action="{{ route('settings.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="id_photo" class="form-control" required accept="image/*">
                                <button type="submit" class="btn btn-primary px-4">Upload</button>
                            </div>
                        </form>
                    @endif
                    
                    @if(Auth::user()->id_photo_path)
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Last Uploaded:</small>
                            <img src="{{ asset('storage/' . Auth::user()->id_photo_path) }}" class="img-thumbnail rounded shadow-sm" style="height: 60px;">
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. CONTACT INFO --}}
            <div class="card shadow-sm mb-4 border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-secondary">Contact Information</h5>
                    <form action="{{ route('settings.phone') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-muted"></i></span>
                                <input type="text" name="phone_number" class="form-control border-start-0 ps-0" 
                                       value="{{ Auth::user()->phone_number }}" 
                                       placeholder="e.g. 0912 345 6789">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-dark rounded-pill px-4">Save Number</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. SECURITY --}}
            <div class="card shadow-sm mb-4 border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-danger">Security</h5>
                    <form action="{{ route('settings.password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-4">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection