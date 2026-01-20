@extends('layouts.app')

@section('content')
<style>
    :root {
        --bg-gradient: linear-gradient(135deg, #e8f5e9 0%, #a5d6a7 100%);
        --primary-color: #198754; 
    }

    .text-primary { color: var(--primary-color) !important; }
    .bg-primary { background-color: var(--primary-color) !important; }
    
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
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.15);
    }

    /* Password Toggle & Input Spacing */
    .password-toggle {
        position: absolute;
        top: 50%;
        right: 3rem; 
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
    
    #password, #conf {
        padding-right: 4.5rem !important; 
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    .modal-body h6 {
        color: var(--primary-color);
        font-weight: 700;
        margin-top: 1.5rem;
    }
    .modal-body p, .modal-body li {
        text-align: justify;
        font-size: 0.95rem;
    }

    /* Error Shake Animation */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .shake { animation: shake 0.3s ease-in-out; }
    
    .invalid-feedback-custom {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
        display: none;
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

                <form action="{{ route('register.post') }}" method="POST" id="signupForm" novalidate>
                    @csrf

                    <div class="step" id="step1">
                        <h5 class="mb-4 fw-semibold"><i class="bi bi-person me-2"></i>Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="first_name" class="form-control" id="fn" placeholder="First" required 
                                           pattern="[a-zA-Z\s\.\-]+" 
                                           title="Letters only.">
                                    <label for="fn">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="middle_name" class="form-control" id="mn" placeholder="Middle"
                                           pattern="[a-zA-Z\s\.\-]+" 
                                           title="Letters only.">
                                    <label for="mn">Middle (Optional)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="last_name" class="form-control" id="ln" placeholder="Last" required
                                           pattern="[a-zA-Z\s\.\-]+" 
                                           title="Letters only.">
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
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required
                                   pattern=".+@gmail\.com"
                                   title="Must be a valid Gmail address">
                            <label for="email">Email Address (Gmail Only)</label>
                            <div class="invalid-feedback">
                                This email is already registered or invalid.
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating position-relative">
                                    <input type="password" name="password" class="form-control" id="password" placeholder="Pass" required
                                           pattern="(?=.*\d)(?=.*[A-Z]).{8,}"
                                           title="Min 8 chars, 1 Uppercase, 1 Number">
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

                        <div class="mb-4 d-flex flex-column align-items-center">
                            <div class="g-recaptcha" id="recaptchabox" data-sitekey="6Ldfi08sAAAAAGc0iqVrllnpeXvNNDM07shQ8MDe"></div>
                            <div class="invalid-feedback-custom" id="captchaError">Please complete the captcha.</div>
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
                            <div class="invalid-feedback-custom" id="termsError">You must agree to the terms.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(3)">Back</button>
                            <button type="button" class="btn btn-success px-5 fw-bold" onclick="createAccount()">Create Account</button>
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
                <div class="small mb-3 text-muted"><strong>Last Updated:</strong> January 20, 2026</div>
                
                <p>Welcome to the <strong>Eye Care Portal</strong>. By creating an account and using our online appointment system, you agree to comply with and be bound by the following terms and conditions of use.</p>

                <h6>1. User Accounts & Security</h6>
                <ul>
                    <li><strong>1.1. Registration:</strong> You agree to provide accurate, current, and complete information during the registration process.</li>
                    <li><strong>1.2. Identity Verification:</strong> To prevent fraud and ensure the safety of our clinic, users are required to upload a valid Government ID before booking an appointment. We reserve the right to suspend accounts with suspicious or falsified documents.</li>
                    <li><strong>1.3. Account Security:</strong> You are responsible for maintaining the confidentiality of your password. You agree to notify us immediately of any unauthorized use of your account.</li>
                </ul>

                <h6>2. Appointment Booking & Cancellations</h6>
                <ul>
                    <li><strong>2.1. Booking Limits:</strong> To ensure fair access for all patients, users are limited to one (1) active appointment at a time. The system also limits the total number of bookings per day for the clinic.</li>
                    <li><strong>2.2. Cancellation Policy:</strong> If you cannot make it to your appointment, please cancel via the portal or call us at least 24 hours in advance. Repeated "no-shows" may result in the suspension of your online booking privileges.</li>
                    <li><strong>2.3. Rescheduling:</strong> The clinic reserves the right to reschedule appointments due to doctor unavailability or emergencies. We will notify you via the contact details provided.</li>
                </ul>

                <h6>3. Medical Disclaimer</h6>
                <ul>
                    <li><strong>3.1. Not for Emergencies:</strong> This portal is for scheduling routine eye examinations and check-ups only. If you are experiencing a medical emergency (e.g., sudden vision loss, severe eye pain, chemical injury), please go to the nearest emergency room immediately.</li>
                    <li><strong>3.2. No Medical Advice:</strong> The content on this portal is for informational purposes and does not substitute for professional medical advice, diagnosis, or treatment.</li>
                </ul>

                <h6>4. Privacy & Data Protection</h6>
                <ul>
                    <li><strong>4.1. Data Usage:</strong> Your personal and medical information is stored securely and used solely for the purpose of managing your appointments and clinical records in accordance with the Data Privacy Act.</li>
                    <li><strong>4.2. ID Storage:</strong> Uploaded IDs are used strictly for identity verification and are accessible only by authorized administrative staff.</li>
                </ul>

                <h6>5. User Conduct</h6>
                <p>You agree not to use the portal to harass staff, book fake appointments, or upload offensive content. Any violation of these terms will result in immediate account termination.</p>

                <h6>6. Contact Us</h6>
                <p class="mb-0">If you have any questions regarding these Terms, please contact our support team at <strong>support@eyecareportal.com</strong> or visit our clinic during business hours.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand & Agree</button>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);
        document.getElementById('dobField').max = maxDate.toISOString().split('T')[0];

        function setupToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            if(toggle && input) {
                toggle.addEventListener('click', function () {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            }
        }
        setupToggle('togglePassword', 'password');
        setupToggle('toggleConfirm', 'conf');
        
        // Clear custom validity on input
        document.getElementById('conf').addEventListener('input', function() { this.setCustomValidity(''); });
        document.getElementById('email').addEventListener('input', function() { 
            this.setCustomValidity(''); 
            this.classList.remove('is-invalid');
        });
    });

    function updateIndicators(step) {
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
        document.getElementById('bar1').style.width = step > 1 ? '100%' : '0%';
        document.getElementById('bar2').style.width = step > 2 ? '100%' : '0%';
        document.getElementById('bar3').style.width = step > 3 ? '100%' : '0%';
    }

    // MAKE THIS ASYNC to handle the fetch request
    async function nextStep(target) {
        const currentStepIndex = target - 1; 

        // 1. Password Match Check (Step 3)
        if(currentStepIndex === 3) {
            const pass = document.getElementById('password');
            const conf = document.getElementById('conf');
            if(pass && conf && pass.value !== conf.value) {
                conf.setCustomValidity("Passwords do not match.");
            } else {
                conf.setCustomValidity("");
            }
            
            // 2. Email Uniqueness Check (Step 3)
            const emailInput = document.getElementById('email');
            // Only check if it's basically valid first
            if(emailInput && emailInput.checkValidity()) {
                // Show a loading cursor maybe?
                document.body.style.cursor = 'wait';
                try {
                    const response = await fetch(`{{ route('check.email') }}?email=${encodeURIComponent(emailInput.value)}`);
                    const data = await response.json();
                    
                    if(data.exists) {
                        emailInput.setCustomValidity("This email is already registered.");
                        // Force the error to show immediately
                        emailInput.classList.add('is-invalid');
                        emailInput.reportValidity();
                        document.body.style.cursor = 'default';
                        return; // STOP HERE
                    } else {
                        emailInput.setCustomValidity("");
                        emailInput.classList.remove('is-invalid');
                        emailInput.classList.add('is-valid');
                    }
                } catch (error) {
                    console.error('Check failed', error);
                } finally {
                    document.body.style.cursor = 'default';
                }
            }
        }

        // 3. Standard Validation
        const currentInputs = document.getElementById('step' + currentStepIndex).querySelectorAll('input, select');
        for(let input of currentInputs) {
            if(!input.checkValidity()) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                input.reportValidity(); 
                return; // Stop at first error
            } else {
                input.classList.remove('is-invalid');
                // Only add is-valid if not empty (visual preference)
                if(input.value !== "") input.classList.add('is-valid');
            }
        }
        
        // Proceed
        document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
        document.getElementById('step' + target).classList.remove('d-none');
        updateIndicators(target);
    }

    function prevStep(target) {
        document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
        document.getElementById('step' + target).classList.remove('d-none');
        updateIndicators(target);
    }

    // --- NEW: Custom Submit Function ---
    function createAccount() {
        let isValid = true;

        // 1. Check Recaptcha
        const response = grecaptcha.getResponse();
        const captchaErr = document.getElementById('captchaError');
        if(response.length === 0) {
            captchaErr.style.display = 'block';
            isValid = false;
        } else {
            captchaErr.style.display = 'none';
        }

        // 2. Check Terms
        const terms = document.getElementById('terms');
        const termsErr = document.getElementById('termsError');
        if(!terms.checked) {
            termsErr.style.display = 'block';
            terms.classList.add('is-invalid');
            isValid = false;
        } else {
            termsErr.style.display = 'none';
            terms.classList.remove('is-invalid');
        }

        if(isValid) {
            document.getElementById('signupForm').submit();
        } else {
            // Shake the container to indicate error
            const step4 = document.getElementById('step4');
            step4.classList.add('shake');
            setTimeout(() => step4.classList.remove('shake'), 300);
        }
    }
</script>
@endsection