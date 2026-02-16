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
    
    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>{{ session('error') }}</div>
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
        
        {{-- LEFT COLUMN --}}
        <div class="col-lg-6">
            
            {{-- 1. PERSONAL INFORMATION CARD --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-indigo-50 text-dark bg-secondary bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi bi-person-lines-fill fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Personal Information</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(Auth::user()->is_verified)
                        {{-- READ ONLY VIEW --}}
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success d-flex align-items-center rounded-3 mb-3">
                            <i class="bi bi-lock-fill me-2"></i>
                            <small class="fw-bold">Locked: Identity Verified</small>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted fw-bold">Full Name</label>
                            <div class="form-control bg-light">{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="small text-muted fw-bold">Birthday</label>
                                <div class="form-control bg-light">{{ Auth::user()->birthday }}</div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small text-muted fw-bold">Gender</label>
                                <div class="form-control bg-light">{{ ucfirst(Auth::user()->gender) }}</div>
                            </div>
                        </div>
                    @else
                        {{-- EDIT FORM (UNVERIFIED) --}}
                        <p class="text-muted small mb-3">
                            Ensure your details match your ID <strong>exactly</strong>. 
                        </p>
                        <form action="{{ route('settings.profile') }}" method="POST">
                            @csrf
                            <div class="row g-2 mb-3">
                                <div class="col-md-5">
                                    <label class="small text-muted">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="{{ Auth::user()->first_name }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="small text-muted">M.I.</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ Auth::user()->middle_name }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="small text-muted">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="{{ Auth::user()->last_name }}" required>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="small text-muted">Birthday</label>
                                    {{-- UPDATED: Added max attribute to disable dates younger than 18 years --}}
                                    <input type="date" 
                                           name="birthday" 
                                           class="form-control" 
                                           value="{{ Auth::user()->birthday }}" 
                                           max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                           required>
                                    <div class="form-text small">Must be 18+ years old.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" {{ Auth::user()->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ Auth::user()->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-dark rounded-pill w-100 fw-bold btn-sm">
                                Save Changes
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- 2. IDENTITY VERIFICATION CARD --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
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
                                <small class="text-muted d-block mb-2">Current ID on file (Click to view):</small>
                                <a href="{{ asset('storage/' . Auth::user()->id_photo_path) }}" target="_blank" title="View Full Size">
                                    <img src="{{ asset('storage/' . Auth::user()->id_photo_path) }}" class="img-fluid rounded-3 border shadow-sm cursor-pointer" style="max-height: 150px; object-fit: contain; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                            </div>
                        @endif
                    @else
                        
                        {{-- REJECTION ALERT --}}
                        @if(Auth::user()->rejection_reason)
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 mb-3">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle-fill fs-5 me-3"></i>
                                    <div>
                                        <strong>Verification Rejected</strong><br>
                                        <span class="small">{{ Auth::user()->rejection_reason }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                        
                        @if(Auth::user()->id_photo_path && !Auth::user()->rejection_reason)
                            <div class="mt-3 text-center">
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Review Pending</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-6">
            {{-- Contact Card --}}
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
                        <div class="mb-3">
                            <label class="form-label small text-muted">Mobile Number (PH Format)</label>
                            <div class="input-group">
                                <input type="text" name="phone_number" class="form-control" 
                                       placeholder="09123456789" 
                                       value="{{ Auth::user()->phone_number }}"
                                       pattern="^09[0-9]{9}$" 
                                       maxlength="11"
                                       title="Please enter a valid 11-digit Philippine mobile number starting with 09."
                                       required>
                                <button class="btn btn-outline-primary" type="submit">Update</button>
                            </div>
                        </div>
                        <div class="form-text text-muted small">
                            Format: 09xxxxxxxxx
                        </div>
                    </form>
                </div>
            </div>

            {{-- Password Card --}}
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