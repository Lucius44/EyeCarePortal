@extends('layouts.app')

@section('content')
<style>
    /* Full Height Split Layout */
    .reset-container {
        min-height: 100vh;
        display: flex;
    }
    
    .reset-image-side {
        background: url('{{ asset("images/hero-bg.jpg") }}') no-repeat center center;
        background-size: cover;
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 4rem;
    }
    
    .reset-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.4));
    }
    
    .reset-quote {
        position: relative;
        z-index: 2;
        color: white;
        max-width: 450px;
    }

    .reset-form-side {
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        position: relative;
    }

    /* Input Styling */
    .form-floating > .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background-color: #f8fafc;
    }
    .form-floating > .form-control:focus {
        background-color: #fff;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    .btn-reset {
        background: var(--primary-color);
        color: white;
        border-radius: 12px;
        padding: 1rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-reset:hover {
        background: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);
        color: white;
    }

    @media (max-width: 991px) {
        .reset-image-side { display: none; }
        .reset-form-side { padding: 2rem; }
    }
</style>

<div class="container-fluid p-0 position-relative">
    
    <div class="row g-0 reset-container">
        <div class="col-lg-6 reset-image-side">
            <div class="reset-overlay"></div>
            <div class="reset-quote">
                <h2 class="fw-bold mb-3 display-6">"The only thing worse than being blind is having sight but no vision."</h2>
                <p class="opacity-75">— Helen Keller</p>
            </div>
        </div>

        <div class="col-lg-6 reset-form-side">
            <div style="max-width: 400px; margin: 0 auto; width: 100%;">
                
                <div class="mb-5">
                    <h3 class="fw-bold display-6 mb-2" style="color: var(--primary-color);">Reset Password</h3>
                    <p class="text-muted">Please create a new, secure password.</p>
                </div>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf 
                    
                    {{-- The Token is required by Laravel to verify the link validity --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small rounded-3 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Email (Read-only recommended so they don't accidentally change it) --}}
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" 
                               value="{{ $email ?? old('email') }}" readonly required>
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input type="password" name="password" class="form-control" id="password" placeholder="New Password" required>
                        <label for="password">New Password</label>
                    </div>

                    <div class="form-floating mb-4 position-relative">
                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm" required>
                        <label for="password_confirmation">Confirm Password</label>
                    </div>

                    <button type="submit" class="btn btn-reset w-100 mb-4">
                        Reset Password
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</div>
@endsection