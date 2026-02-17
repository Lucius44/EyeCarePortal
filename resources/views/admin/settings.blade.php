@extends('layouts.app')

@section('content')
<style>
    /* --- ADMIN RESPONSIVE LAYOUT (Copied from Dashboard) --- */
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); overflow-x: hidden; }
    
    .admin-sidebar { 
        width: 260px; 
        background: #0F172A; 
        color: #94a3b8; 
        flex-shrink: 0; 
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        display: none; 
    }

    .admin-content { 
        flex-grow: 1; 
        background: #F1F5F9; 
        padding: 1.5rem; 
        min-width: 0; 
    }

    @media (min-width: 992px) {
        .admin-sidebar { display: flex; }
        .admin-content { padding: 2rem; }
    }
    
    /* Nav Links Styling to match Dashboard */
    .admin-nav-link {
        display: flex; align-items: center; padding: 12px 20px;
        color: #94a3b8; text-decoration: none; font-weight: 500;
        border-radius: 8px; margin-bottom: 5px; transition: all 0.2s;
    }
    .admin-nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav-link.active { background: #3B82F6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .admin-nav-link i { font-size: 1.1rem; margin-right: 12px; }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
        {{-- DESKTOP SIDEBAR --}}
        <div class="admin-sidebar p-3 d-none d-lg-flex">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            @include('admin.partials.nav_links')
            <div class="mt-auto p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
                <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="admin-content">
            
            {{-- HEADER SECTION --}}
            <div class="d-flex align-items-center gap-3 mb-5">
                {{-- MOBILE MENU TOGGLE --}}
                <button class="btn btn-white border shadow-sm d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu">
                    <i class="bi bi-list fs-5 text-primary"></i>
                </button>
                
                <div>
                    <h2 class="fw-bold text-dark mb-1">Admin Settings</h2>
                    <p class="text-secondary mb-0 small">Manage your account credentials and security.</p>
                </div>
            </div>

            {{-- ALERTS --}}
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

            {{-- CONTENT CARD --}}
            <div class="row">
                <div class="col-12 col-xl-6">
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
                            <form action="{{ route('admin.settings.password') }}" method="POST">
                                @csrf
                                
                                {{-- Current Password --}}
                                <div class="input-group mb-3">
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" name="current_password" class="form-control rounded-start" id="curPass" placeholder="Current" required>
                                        <label for="curPass">Current Password</label>
                                    </div>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('curPass', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                {{-- New Password --}}
                                <div class="input-group mb-3">
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" name="password" class="form-control rounded-start" id="newPass" placeholder="New" required>
                                        <label for="newPass">New Password</label>
                                    </div>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPass', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                {{-- Confirm Password --}}
                                <div class="input-group mb-3">
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" name="password_confirmation" class="form-control rounded-start" id="conPass" placeholder="Confirm" required>
                                        <label for="conPass">Confirm New Password</label>
                                    </div>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('conPass', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-dark rounded-pill fw-bold py-2">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MOBILE OFF CANVAS MENU --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileAdminMenu" style="background: #0F172A; width: 280px;">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-white fw-bold">Admin Console</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('admin.partials.nav_links')
        <div class="mt-5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
            <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
            <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endsection