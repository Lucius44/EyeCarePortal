@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card p-4">
            <h3 class="text-center mb-4 text-primary">Patient Registration</h3>

            <div class="progress mb-4" style="height: 5px;">
                <div class="progress-bar" role="progressbar" style="width: 25%;" id="progressBar"></div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" id="signupForm">
                @csrf

                <div class="step" id="step1">
                    <h5 class="mb-3">Step 1: Personal Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Middle Name (Optional)</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                    </div>
                </div>

                <div class="step d-none" id="step2">
                    <h5 class="mb-3">Step 2: Demographics</h5>
                    <div class="mb-3">
                        <label class="form-label">Birthday (18+ only)</label>
                        <input type="date" name="birthday" id="dobField" class="form-control" required>
                        <div class="form-text">You must be at least 18 years old to register.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Back</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next</button>
                    </div>
                </div>

                <div class="step d-none" id="step3">
                    <h5 class="mb-3">Step 3: Account Details</h5>
                    <div class="mb-3">
                        <label class="form-label">Email Address (Gmail Only)</label>
                        <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="form-text small" id="passwordHelp">
                                Must be 8+ chars, with 1 Uppercase & 1 Number.
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Back</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next</button>
                    </div>
                </div>

                <div class="step d-none" id="step4">
                    <h5 class="mb-3">Step 4: Finalize</h5>
                    
                    <div class="mb-3 border p-3 bg-light text-center">
                        <span class="text-muted">ReCAPTCHA Check</span>
                        <div class="form-check d-flex justify-content-center mt-2">
                             <input class="form-check-input me-2" type="checkbox" required>
                             <label class="form-check-label">I am not a robot</label>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">I agree to the Terms and Conditions</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Back</button>
                        <button type="submit" class="btn btn-success">Create Account</button>
                    </div>
                </div>

            </form>
        </div>
        <div class="text-center mt-3">
            <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
        </div>
    </div>
</div>

<script>
    // 1. Set Age Limit on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        // Calculate date 18 years ago
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        // Format to YYYY-MM-DD for the HTML input
        const formattedDate = maxDate.toISOString().split('T')[0];
        // Apply limit
        document.getElementById('dobField').setAttribute('max', formattedDate);
    });

    // 2. Toggle Password Visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        if (field.type === "password") {
            field.type = "text";
        } else {
            field.type = "password";
        }
    }

    // 3. Navigation with Strict Validation
    function nextStep(targetStep) {
        let currentStep = targetStep - 1;
        let currentStepDiv = document.getElementById('step' + currentStep);
        let inputs = currentStepDiv.querySelectorAll('input, select');
        
        for (let input of inputs) {
            input.setCustomValidity("");

            // --- Gmail Rule ---
            if (input.name === 'email') {
                if (input.value && !input.value.endsWith('@gmail.com')) {
                    input.setCustomValidity("Please use a @gmail.com address.");
                }
            }

            // --- Strict Password Rules ---
            if (input.name === 'password') {
                const val = input.value;
                // Regex: At least one Uppercase [A-Z], one Number [0-9], and 8+ chars
                const strongRegex = /^(?=.*[A-Z])(?=.*[0-9]).{8,}$/;
                
                if (!strongRegex.test(val)) {
                    input.setCustomValidity("Password must have 8+ chars, 1 Uppercase letter, and 1 Number.");
                }
            }

            // --- Password Match ---
            if (input.name === 'password_confirmation') {
                let passwordField = currentStepDiv.querySelector('input[name="password"]');
                if (input.value !== passwordField.value) {
                    input.setCustomValidity("Passwords do not match.");
                }
            }

            if (!input.checkValidity()) {
                input.reportValidity(); 
                return;
            }
        }

        // If valid, proceed
        document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
        document.getElementById('step' + targetStep).classList.remove('d-none');
        
        // Update Progress Bar
        let percent = (targetStep / 4) * 100;
        document.getElementById('progressBar').style.width = percent + '%';
    }

    function prevStep(step) {
        document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
        document.getElementById('step' + step).classList.remove('d-none');
        let percent = (step / 4) * 100;
        document.getElementById('progressBar').style.width = percent + '%';
    }
</script>
@endsection