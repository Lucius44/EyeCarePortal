@extends('layouts.app')

@section('content')
<style>
    /* Background & Container */
    .register-bg {
        background-color: #F8FAFC;
        background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
        background-size: 30px 30px;
        min-height: calc(100vh - 80px);
        padding: 40px 0;
        display: flex;
        align-items: center;
    }

    .card-wizard {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    /* Progress Indicators */
    .step-indicator {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 2.5rem;
    }
    .step-indicator::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 2px;
        background: #e2e8f0;
        z-index: 0;
        transform: translateY(-50%);
    }
    .step-dot {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
        font-weight: 700;
        color: #94a3b8;
        transition: all 0.3s;
    }
    .step-dot.active {
        border-color: var(--accent-color);
        color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .step-dot.completed {
        background: var(--accent-color);
        border-color: var(--accent-color);
        color: white;
    }

    /* Form Styles */
    .form-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .form-floating > .form-control {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    .form-floating > .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .btn-next {
        background: var(--primary-color);
        color: white;
        border-radius: 50px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-next:hover {
        background: #1e293b;
        color: white;
        transform: translateX(3px);
    }

    /* Animations */
    .step { animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
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

<div class="register-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-wizard">
                    <div class="card-body p-5">
                        
                        <div class="step-indicator px-5">
                            <div class="step-dot active" id="dot1">1</div>
                            <div class="step-dot" id="dot2">2</div>
                            <div class="step-dot" id="dot3">3</div>
                            <div class="step-dot" id="dot4"><i class="bi bi-check-lg"></i></div>
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
                                <h4 class="form-section-title">Let's start with your name</h4>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" name="first_name" class="form-control" id="fn" placeholder="First" required 
                                                   pattern="[a-zA-Z\s\.\-]+" title="Letters only.">
                                            <label for="fn">First Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" name="middle_name" class="form-control" id="mn" placeholder="Middle"
                                                   pattern="[a-zA-Z\s\.\-]+" title="Letters only.">
                                            <label for="mn">Middle (Optional)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" name="last_name" class="form-control" id="ln" placeholder="Last" required
                                                   pattern="[a-zA-Z\s\.\-]+" title="Letters only.">
                                            <label for="ln">Last Name</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-5">
                                    <button type="button" class="btn btn-next" onclick="nextStep(2)">
                                        Next Step <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="step d-none" id="step2">
                                <h4 class="form-section-title">A bit about you</h4>
                                <div class="row g-3 justify-content-center">
                                    <div class="col-md-5">
                                        <div class="form-floating">
                                            <input type="date" name="birthday" id="dobField" class="form-control" required>
                                            <label>Date of Birth</label>
                                        </div>
                                        <div class="form-text text-muted small text-center"><i class="bi bi-info-circle"></i> Must be 18 or older.</div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-floating">
                                            <select name="gender" class="form-select" id="gender" required>
                                                <option value="" selected disabled>Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                            <label for="gender">Gender</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center gap-3 mt-5">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevStep(1)">Back</button>
                                    <button type="button" class="btn btn-next" onclick="nextStep(3)">
                                        Next Step <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="step d-none" id="step3">
                                <h4 class="form-section-title">Secure your account</h4>
                                
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="form-floating mb-3">
                                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required
                                                   pattern=".+@gmail\.com" title="Must be a valid Gmail address">
                                            <label for="email">Email Address (Gmail Only)</label>
                                            <div class="invalid-feedback">This email is already registered or invalid.</div>
                                        </div>

                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <div class="form-floating position-relative">
                                                    <input type="password" name="password" class="form-control" id="password" placeholder="Pass" required
                                                           pattern="(?=.*\d)(?=.*[A-Z]).{8,}" title="Min 8 chars, 1 Uppercase, 1 Number">
                                                    <label for="password">Password</label>
                                                    <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" id="togglePassword" style="cursor: pointer; z-index: 5;"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating position-relative">
                                                    <input type="password" name="password_confirmation" class="form-control" id="conf" placeholder="Confirm" required>
                                                    <label for="conf">Confirm Password</label>
                                                    <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" id="toggleConfirm" style="cursor: pointer; z-index: 5;"></i>
                                                </div>
                                            </div>
                                            <div class="col-12 text-center form-text text-muted small">
                                                <i class="bi bi-shield-check"></i> Min 8 chars, 1 Uppercase & 1 Number.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-4">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevStep(2)">Back</button>
                                    <button type="button" class="btn btn-next" onclick="nextStep(4)">
                                        Next Step <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="step d-none" id="step4">
                                <h4 class="form-section-title">Final Verification</h4>

                                <div class="mb-5 d-flex flex-column align-items-center">
                                    <div class="g-recaptcha mb-3" id="recaptchabox" data-sitekey="6Ldfi08sAAAAAGc0iqVrllnpeXvNNDM07shQ8MDe"></div>
                                    <div class="invalid-feedback-custom text-center" id="captchaError">Please complete the captcha.</div>
                                    
                                    <div class="form-check p-3 rounded border bg-light mt-3" style="max-width: 400px; width: 100%;">
                                        <input class="form-check-input ms-1" type="checkbox" id="terms" required>
                                        <label class="form-check-label ms-2" for="terms">
                                            I agree to the 
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="fw-bold text-decoration-none" style="color: var(--accent-color);">
                                                Terms of Service
                                            </a> 
                                            & Privacy Policy.
                                        </label>
                                        <div class="invalid-feedback-custom" id="termsError">You must agree to the terms.</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevStep(3)">Back</button>
                                    <button type="button" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow-lg" onclick="createAccount()">
                                        Create Account
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span> 
                    <a href="{{ route('login') }}" class="fw-bold text-decoration-none" style="color: var(--primary-color);">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-secondary px-4">
                <p>Welcome to the <strong>Eye Care Portal</strong>...</p>
                <h6>1. User Accounts & Security</h6>
                <p>...</p>
                </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    // Logic preserved exactly as requested
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
        
        document.getElementById('conf').addEventListener('input', function() { this.setCustomValidity(''); });
        document.getElementById('email').addEventListener('input', function() { 
            this.setCustomValidity(''); 
            this.classList.remove('is-invalid');
        });
    });

    function updateIndicators(step) {
        for(let i=1; i<=4; i++) {
            const dot = document.getElementById('dot'+i);
            dot.classList.remove('active', 'completed');
            if(i < step) {
                dot.classList.add('completed');
                dot.innerHTML = '<i class="bi bi-check-lg"></i>';
            } else if (i === step) {
                dot.classList.add('active');
                dot.innerHTML = i === 4 ? '<i class="bi bi-check-lg"></i>' : i;
            } else {
                dot.innerHTML = i === 4 ? '<i class="bi bi-check-lg"></i>' : i;
            }
        }
    }

    async function nextStep(target) {
        const currentStepIndex = target - 1; 

        if(currentStepIndex === 3) {
            const pass = document.getElementById('password');
            const conf = document.getElementById('conf');
            if(pass && conf && pass.value !== conf.value) {
                conf.setCustomValidity("Passwords do not match.");
            } else {
                conf.setCustomValidity("");
            }
            
            const emailInput = document.getElementById('email');
            if(emailInput && emailInput.checkValidity()) {
                document.body.style.cursor = 'wait';
                try {
                    const response = await fetch(`{{ route('check.email') }}?email=${encodeURIComponent(emailInput.value)}`);
                    const data = await response.json();
                    
                    if(data.exists) {
                        emailInput.setCustomValidity("This email is already registered.");
                        emailInput.classList.add('is-invalid');
                        emailInput.reportValidity();
                        document.body.style.cursor = 'default';
                        return; 
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

        const currentInputs = document.getElementById('step' + currentStepIndex).querySelectorAll('input, select');
        for(let input of currentInputs) {
            if(!input.checkValidity()) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                input.reportValidity(); 
                return;
            } else {
                input.classList.remove('is-invalid');
                if(input.value !== "") input.classList.add('is-valid');
            }
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

    function createAccount() {
        let isValid = true;
        
        // Recaptcha check
        const response = grecaptcha.getResponse();
        const captchaErr = document.getElementById('captchaError');
        if(response.length === 0) {
            captchaErr.style.display = 'block';
            isValid = false;
        } else {
            captchaErr.style.display = 'none';
        }

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
            const step4 = document.getElementById('step4');
            step4.classList.add('shake');
            setTimeout(() => step4.classList.remove('shake'), 300);
        }
    }
</script>
@endsection