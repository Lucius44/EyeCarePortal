@extends('layouts.app')

@section('content')
<style>
    /* Full Height Split Layout */
    .login-container {
        min-height: 100vh; /* Full screen */
        display: flex;
    }
    
    .login-image-side {
        background: url('{{ asset("images/contact-bg.jpg") }}') no-repeat center center;
        background-size: cover;
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 4rem;
    }
    
    .login-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.4));
    }
    
    .login-quote {
        position: relative;
        z-index: 2;
        color: white;
        max-width: 400px;
    }

    .login-form-side {
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        position: relative;
    }

    /* Back Button Styling (Top Right) */
    .btn-back-home {
        position: absolute;
        top: 2rem;
        right: 2rem; /* Moved to Right */
        left: auto;
        text-decoration: none;
        color: #64748B;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex; /* Fix: Minimizes clickable width */
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        z-index: 10;
        background: white; /* Optional: adds background */
        padding: 8px 16px;
        border-radius: 50px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .btn-back-home:hover {
        color: var(--primary-color);
        transform: translateX(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
    
    .btn-login {
        background: var(--primary-color);
        color: white;
        border-radius: 12px;
        padding: 1rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-login:hover {
        background: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);
        color: white;
    }

    /* Mobile tweaks */
    @media (max-width: 991px) {
        .login-image-side { display: none; }
        .login-form-side { padding: 2rem; }
        .btn-back-home { top: 1.5rem; right: 1.5rem; }
    }
</style>

<div class="container-fluid p-0">
    <div class="row g-0 login-container">
        
        {{-- BACK BUTTON --}}
        <a href="{{ route('home') }}" class="btn-back-home">
            <i class="bi bi-arrow-left"></i> Home
        </a>

        <div class="col-lg-6 login-image-side">
            <div class="login-overlay"></div>
            <div class="login-quote">
                <h2 class="fw-bold mb-3 display-6">"Vision is the art of seeing what is invisible to others."</h2>
                <p class="opacity-75">— Jonathan Swift</p>
                <div class="mt-4">
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-star-fill text-warning me-1"></i> #1 Rated Clinic
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-6 login-form-side">
            <div style="max-width: 400px; margin: 0 auto; width: 100%;">
                
                <div class="mb-5">
                    <h3 class="fw-bold display-6 mb-2" style="color: var(--primary-color);">Welcome Back</h3>
                    <p class="text-muted">Please enter your details to sign in.</p>
                </div>

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf 

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small rounded-3 mb-4">
                            <i class="bi bi-exclamation-circle me-1"></i> Invalid credentials. Please try again.
                        </div>
                    @endif

                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating mb-4 position-relative">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">Password</label>
                        <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted cursor-pointer" 
                           id="togglePassword" style="cursor: pointer; z-index: 5;"></i>
                    </div>

                    <button type="submit" class="btn btn-login w-100 mb-4">
                        Sign In to Portal
                    </button>

                    <div class="text-center">
                        <span class="text-muted">Don't have an account?</span> 
                        <a href="{{ route('register') }}" class="fw-bold text-decoration-none" style="color: var(--accent-color);">Create one now</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if(togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    });
</script>
@endsection