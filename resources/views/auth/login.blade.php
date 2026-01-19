@extends('layouts.app')

@section('content')
<style>
    :root {
        /* Light Bluish Gradient for Login */
        --bg-gradient: linear-gradient(135deg, #e3f2fd 0%, #90caf9 100%);
    }
</style>

<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card card-modern p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-person-lock fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-primary">Login</h4>
                    <p class="text-muted small">Access your patient portal securely</p>
                </div>

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf 

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small rounded-3">
                            <i class="bi bi-exclamation-circle me-1"></i> Invalid credentials. Please try again.
                        </div>
                    @endif

                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Sign In
                        </button>
                    </div>

                    <div class="text-center">
                        <span class="text-muted small">Don't have an account?</span> 
                        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-success">Create Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection