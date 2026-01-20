@extends('layouts.app')

@section('content')
<style>
    :root {
        /* 1. Light Greenish Gradient */
        --bg-gradient: linear-gradient(135deg, #e8f5e9 0%, #a5d6a7 100%);
        /* 2. Redefine Primary Color to Green */
        --primary-color: #198754; 
    }

    /* 3. Force Bootstrap Primary elements to use our Green theme */
    .text-primary { color: var(--primary-color) !important; }
    .bg-primary { background-color: var(--primary-color) !important; }
    
    /* 4. Custom Button Override */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
    }
    .btn-primary:hover {
        background-color: #157347; 
        border-color: #146c43;
        box-shadow: 0 6px 15px rgba(25, 135, 84, 0.3);
    }
    
    /* 5. Update floating label AND Select focus color */
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.15);
    }

    /* 6. Password Toggle Icon Style */
    .password-toggle {
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        z-index: 10;
        font-size: 1.2rem;
        transition: color 0.2s;
    }
    .password-toggle:hover {
        color: var(--primary-color);
    }

    /* 7. Modal Tweaks */
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    .modal-body h6 {
        color: var(--primary-color);
        font-weight: 700;
        margin-top: 1.5rem;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-modern">
            <div class="card-body p-5">
                
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-primary mb-3">Patient Registration</h3>
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <span class="badge rounded-pill bg-primary" id="badgeStep1">1</span>
                        <div class="progress" style="width: 40px; height: 4px;"><div class="progress-bar bg-primary" id="bar1" style="width: 0%"></div></div>
                        
                        <span class="badge rounded-pill bg-secondary" id="badgeStep2">2</span>
                        <div class="progress" style="width: 40px; height: 4px;"><div class="progress-bar bg-primary" id="bar2" style="width: 0%"></div></div>
                        
                        <span class="badge rounded-pill bg-secondary" id="badgeStep3">3</span>
                        <div class="progress" style="width: 40px; height: 4px;"><div class="progress-bar bg-primary" id="bar3" style="width: 0%"></div></div>
                        
                        <span class="badge rounded-pill bg-secondary" id="badgeStep4">4</span>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 mb-4">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.post') }}" method="POST" id="signupForm">
                    @csrf

                    <div class="step" id="step1">
                        <h5 class="mb-4 fw-semibold"><i class="bi bi-person me-2"></i>Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="first_name" class="form-control" id="fn" placeholder="First" required>
                                    <label for="fn">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="middle_name" class="form-control" id="mn" placeholder="Middle">
                                    <label for="mn">Middle (Optional)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="last_name" class="form-control" id="ln" placeholder="Last" required>
                                    <label for="ln">Last Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-primary px-4" onclick="nextStep(2)">Next Step <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    <div class="step d-none" id="step2">
                        <h5 class="mb-4 fw-semibold"><i class="bi bi-calendar-event me-2"></i>Demographics</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" name="birthday" id="dobField" class="form-control" required>
                                    <label>Date of Birth</label>
                                </div>
                                <div class="form-text text-muted small ps-1"><i class="bi bi-info-circle"></i> Must be 18 or older.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="gender" class="form-select" id="gender" required>
                                        <option value="" selected disabled>Select...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <label for="gender">Gender</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(1)">Back</button>
                            <button type="button" class="btn btn-primary px-4" onclick="nextStep(3)">Next Step <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    <div class="step d-none" id="step3">
                        <h5 class="mb-4 fw-semibold"><i class="bi bi-shield-lock me-2"></i>Account Security</h5>
                        
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
                            <label for="email">Email Address (Gmail Only)</label>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating position-relative">
                                    <input type="password" name="password" class="form-control" id="password" placeholder="Pass" required>
                                    <label for="password">Password</label>
                                    <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating position-relative">
                                    <input type="password" name="password_confirmation" class="form-control" id="conf" placeholder="Confirm" required>
                                    <label for="conf">Confirm Password</label>
                                    <i class="bi bi-eye password-toggle" id="toggleConfirm"></i>
                                </div>
                            </div>
                            <div class="col-12 form-text text-muted small">
                                <i class="bi bi-shield-check"></i> Must include 1 Uppercase & 1 Number.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(2)">Back</button>
                            <button type="button" class="btn btn-primary px-4" onclick="nextStep(4)">Next Step <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    <div class="step d-none" id="step4">
                        <h5 class="mb-4 fw-semibold"><i class="bi bi-check-circle me-2"></i>Verification & Terms</h5>

                        <div class="mb-4 d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="6Ldfi08sAAAAAGc0iqVrllnpeXvNNDM07shQ8MDe"></div>
                        </div>

                        <div class="form-check bg-light p-3 rounded border mb-4">
                            <input class="form-check-input ms-1" type="checkbox" id="terms" required>
                            <label class="form-check-label ms-2" for="terms">
                                I agree to the 
                                <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="fw-bold text-primary text-decoration-none">
                                    Terms of Service
                                </a> 
                                & Privacy Policy.
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(3)">Back</button>
                            <button type="submit" class="btn btn-success px-5 fw-bold">Create Account</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
        <div class="text-center mt-4">
            <span class="text-muted">Already registered?</span> <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Login here</a>
        </div>
    </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-secondary">
                <div class="small mb-3">
                    <strong>Effective Date:</strong> April 1, 2025
                </div>
                <p>
                    Welcome to the <strong>Eye Care Portal</strong>, a web application designed for scheduling and managing optometric appointments for Clear Optics in Barangay San Cristobal, Calamba, Laguna. By accessing and using our Portal, you agree to the following terms and conditions. If you do not agree, please refrain from using the Portal.
                </p>

                <h6>1. User Accounts</h6>
                <ul class="list-unstyled">
                    <li>1.1. Users are required to sign up and provide accurate personal information during the registration process.</li>
                    <li>1.2. The Portal reserves the right to suspend or terminate any account if fraudulent or inaccurate information is detected.</li>
                </ul>

                <h6>2. Identity Verification</h6>
                <ul class="list-unstyled">
                    <li>2.1. Users must upload a valid document, ID, or card to verify their identity before being able to book appointments.</li>
                    <li>2.2. Uploaded documents will be reviewed by the admin to ensure authenticity. Users will receive notification upon approval or rejection of their verification request.</li>
                    <li>2.3. Only verified accounts can schedule and manage appointments through the Portal.</li>
                </ul>

                <h6>3. Data Privacy and Security</h6>
                <ul class="list-unstyled">
                    <li>3.1. The Portal collects, stores, and processes personal and sensitive information in compliance with the Philippine Data Privacy Act of 2012 and other applicable laws.</li>
                    <li>3.2. The Portal implements standard security measures to protect user data. However, users acknowledge that no system is entirely secure, and they use the Portal at their own risk.</li>
                    <li>3.3. By using the Portal, users consent to the collection and processing of their personal data for purposes directly related to the services provided.</li>
                </ul>

                <h6>4. User Responsibilities</h6>
                <ul class="list-unstyled">
                    <li>4.1. Users must ensure that the information and documents they upload are accurate, valid, and lawful.</li>
                    <li>4.2. Users are solely responsible for maintaining the confidentiality of their login credentials.</li>
                </ul>

                <h6>5. Limitation of Liability</h6>
                <ul class="list-unstyled">
                    <li>5.1. The Portal shall not be held liable for any delays, errors, or unauthorized access to user accounts caused by external factors beyond our control.</li>
                    <li>5.2. Users agree to hold the Portal harmless from any liability arising from the misuse of personal or sensitive information caused by their own negligence.</li>
                </ul>

                <h6>6. Appointment Policies</h6>
                <ul class="list-unstyled">
                    <li>6.1. Verified users may schedule appointments subject to availability.</li>
                </ul>

                <h6>7. Modifications</h6>
                <ul class="list-unstyled">
                    <li>7.1. The Portal reserves the right to modify these Terms and Conditions at any time. Users will be notified of significant changes via email.</li>
                </ul>

                <h6>8. Governing Law</h6>
                <p>
                    These Terms and Conditions are governed by the laws of the Republic of the Philippines. Any disputes arising from the use of the Portal shall be resolved under Philippine jurisdiction.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Date Logic
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);
        document.getElementById('dobField').max = maxDate.toISOString().split('T')[0];

        // Toggle Password Logic (Reusable function)
        function setupToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);

            if(toggle && input) {
                toggle.addEventListener('click', function () {
                    // Toggle Type
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle Icon
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            }
        }

        // Initialize Toggles
        setupToggle('togglePassword', 'password');
        setupToggle('toggleConfirm', 'conf');
    });

    // Multi-step Logic
    function updateIndicators(step) {
        // Updated loop to 4 steps
        for(let i=1; i<=4; i++) {
            const badge = document.getElementById('badgeStep'+i);
            if(i <= step) {
                badge.classList.remove('bg-secondary');
                badge.classList.add('bg-primary');
            } else {
                badge.classList.remove('bg-primary');
                badge.classList.add('bg-secondary');
            }
        }
        
        // Update bars
        if(step > 1) document.getElementById('bar1').style.width = '100%';
        else document.getElementById('bar1').style.width = '0%';
        
        if(step > 2) document.getElementById('bar2').style.width = '100%';
        else document.getElementById('bar2').style.width = '0%';

        if(step > 3) document.getElementById('bar3').style.width = '100%';
        else document.getElementById('bar3').style.width = '0%';
    }

    function nextStep(target) {
        // Validate current step inputs
        const currentInputs = document.getElementById('step'+(target-1)).querySelectorAll('input, select');
        for(let input of currentInputs) {
            if(!input.checkValidity()) { input.reportValidity(); return; }
        }
        
        document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
        document.getElementById('step' + target).classList.remove('d-none');
        updateIndicators(target);
    }

    function prevStep(target) {
        document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
        document.getElementById('step' + target).classList.remove('d-none');
        updateIndicators(target);
    }
</script>
@endsection