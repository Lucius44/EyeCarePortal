@extends('layouts.app')

@section('content')
<style>
    /* Reusing the Login Page Aesthetics for Consistency */
    .verify-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: url('{{ asset("images/contact-bg.jpg") }}') no-repeat center center;
        background-size: cover;
        position: relative;
    }

    .verify-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.85); /* Dark Navy Overlay */
        backdrop-filter: blur(8px);
    }

    .verify-card {
        position: relative;
        z-index: 10;
        background: white;
        padding: 3rem;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 500px;
        width: 100%;
        text-align: center;
    }

    .icon-circle {
        width: 80px;
        height: 80px;
        background: #EFF6FF; /* Light Blue */
        color: #3B82F6;      /* Brand Blue */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1.5rem auto;
    }
</style>

<div class="verify-container">
    <div class="verify-overlay"></div>

    <div class="container px-4">
        <div class="verify-card mx-auto">
            
            {{-- Icon --}}
            <div class="icon-circle">
                <i class="bi bi-envelope-check-fill"></i>
            </div>

            {{-- Title --}}
            <h2 class="fw-bold mb-3" style="color: #0F172A;">Check your inbox</h2>

            <p class="text-muted mb-4">
                Thanks for signing up! We've sent a verification link to 
                <span class="fw-bold text-dark">{{ Auth::user()->email }}</span>. 
                Please click the link to activate your account.
            </p>

            {{-- Success Message for Resend --}}
            @if (session('message'))
                <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small rounded-3 mb-4">
                    <i class="bi bi-check-circle-fill me-1"></i> A new verification link has been sent to your email address.
                </div>
            @endif

            {{-- Actions --}}
            <div class="d-grid gap-2">
                
                {{-- Resend Button --}}
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px;">
                        Resend Verification Email
                    </button>
                </form>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light w-100 py-2 text-muted fw-bold" style="border-radius: 12px;">
                        Log Out
                    </button>
                </form>

            </div>
            
            <div class="mt-4 small text-muted">
                <i class="bi bi-info-circle me-1"></i> 
                Can't find it? Check your spam folder.
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT TO AUTO-REDIRECT WHEN VERIFIED --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkInterval = setInterval(function () {
            
            // Call the endpoint to check status
            fetch("{{ route('verification.check') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // If verified, redirect to dashboard immediately
                if (data.verified) {
                    clearInterval(checkInterval);
                    window.location.href = "{{ route('dashboard') }}";
                }
            })
            .catch(error => console.error('Error checking verification:', error));
            
        }, 2000); // Checks every 2 seconds
    });
</script>

@endsection