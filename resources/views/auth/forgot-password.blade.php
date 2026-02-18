@extends('layouts.app')

@section('content')
<style>
    /* Full Height Split Layout (Matched to Login) */
    .forgot-container {
        min-height: 100vh;
        display: flex;
    }
    
    .forgot-image-side {
        background: url('{{ asset("images/contact-bg.jpg") }}') no-repeat center center;
        background-size: cover;
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 4rem;
    }
    
    .forgot-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.4));
    }
    
    .forgot-quote {
        position: relative;
        z-index: 2;
        color: white;
        max-width: 450px;
    }

    .forgot-form-side {
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        position: relative;
    }

    /* Back Button Styling */
    .btn-back-home {
        position: absolute;
        top: 2rem;
        right: 2rem;
        left: auto;
        text-decoration: none;
        color: #64748B;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        z-index: 1050;
        background: white;
        padding: 8px 16px;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 1px solid #f1f5f9;
    }
    .btn-back-home:hover {
        color: var(--primary-color);
        transform: translateX(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        border-color: #e2e8f0;
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
        .forgot-image-side { display: none; }
        .forgot-form-side { padding: 2rem; }
        .btn-back-home { top: 1.5rem; right: 1.5rem; }
    }
</style>

<div class="container-fluid p-0 position-relative">
    
    {{-- Back Button --}}
    <a href="{{ route('login') }}" class="btn-back-home">
        <i class="bi bi-arrow-left"></i> Back to Login
    </a>

    <div class="row g-0 forgot-container">
        <div class="col-lg-6 forgot-image-side">
            <div class="forgot-overlay"></div>
            <div class="forgot-quote">
                <h2 class="fw-bold mb-3 display-6">"To see clearly is poetry, prophecy, and religion, all in one."</h2>
                <p class="opacity-75">— John Ruskin</p>
                <div class="mt-4">
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-shield-lock-fill text-primary me-1"></i> Secure Recovery
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-6 forgot-form-side">
            <div style="max-width: 400px; margin: 0 auto; width: 100%;">
                
                <div class="mb-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-key-fill display-4"></i>
                    </div>
                    <h3 class="fw-bold display-6 mb-2" style="color: var(--primary-color);">Forgot Password?</h3>
                    <p class="text-muted">No worries, we'll send you reset instructions.</p>
                </div>

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf 

                    @if (session('status'))
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small rounded-3 mb-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small rounded-3 mb-4">
                            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first('email') }}
                        </div>
                    @endif

                    <div class="form-floating mb-4">
                        <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        <label for="email">Enter your email</label>
                    </div>

                    <button type="submit" class="btn btn-reset w-100 mb-4">
                        Send Reset Link
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection