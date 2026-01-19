@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-modern">
            <div class="card-body p-5">
                
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-primary mb-3">Patient Registration</h3>
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <span class="badge rounded-pill bg-primary" id="badgeStep1">1</span>
                        <div class="progress" style="width: 50px; height: 4px;"><div class="progress-bar" id="bar1" style="width: 0%"></div></div>
                        <span class="badge rounded-pill bg-secondary" id="badgeStep2">2</span>
                        <div class="progress" style="width: 50px; height: 4px;"><div class="progress-bar" id="bar2" style="width: 0%"></div></div>
                        <span class="badge rounded-pill bg-secondary" id="badgeStep3">3</span>
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
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" name="password_confirmation" class="form-control" id="conf" placeholder="Confirm" required>
                                    <label for="conf">Confirm Password</label>
                                </div>
                            </div>
                            <div class="col-12 form-text text-muted small">
                                <i class="bi bi-shield-check"></i> Must include 1 Uppercase & 1 Number.
                            </div>
                        </div>

                        <div class="form-check bg-light p-3 rounded border mb-4">
                            <input class="form-check-input ms-1" type="checkbox" id="terms" required>
                            <label class="form-check-label ms-2" for="terms">
                                I agree to the <strong>Terms of Service</strong> & Privacy Policy.
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(2)">Back</button>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set Max Date (18 years ago)
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);
        document.getElementById('dobField').max = maxDate.toISOString().split('T')[0];
    });

    function updateIndicators(step) {
        // Update Badges
        for(let i=1; i<=3; i++) {
            const badge = document.getElementById('badgeStep'+i);
            if(i <= step) {
                badge.classList.remove('bg-secondary');
                badge.classList.add('bg-primary');
            } else {
                badge.classList.remove('bg-primary');
                badge.classList.add('bg-secondary');
            }
        }
        // Update Bars
        if(step > 1) document.getElementById('bar1').style.width = '100%';
        else document.getElementById('bar1').style.width = '0%';
        
        if(step > 2) document.getElementById('bar2').style.width = '100%';
        else document.getElementById('bar2').style.width = '0%';
    }

    function nextStep(target) {
        // (Validation logic remains same as your original script)
        // Check validity...
        const currentInputs = document.getElementById('step'+(target-1)).querySelectorAll('input, select');
        for(let input of currentInputs) {
            if(!input.checkValidity()) { input.reportValidity(); return; }
        }
        
        // Switch Views
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