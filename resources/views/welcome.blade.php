@extends('layouts.app')

@section('content')

<style>
    /* Hero Section */
    .hero-section {
        /* Use a dark overlay so white text pops */
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset("images/hero-bg.jpg") }}');
        background-size: cover;
        background-position: center;
        height: 85vh;
        display: flex;
        align-items: center;
        color: white;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }
    .hero-subtitle {
        font-size: 1.5rem;
        font-weight: 300;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    /* Section Styling */
    .section-padding {
        padding: 80px 0;
    }
    .section-title {
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .section-subtitle {
        color: var(--primary-color);
        font-size: 1.1rem;
        margin-bottom: 3rem;
        font-weight: 600;
    }

    /* Owner Section */
    .owner-img {
        width: 100%;
        max-width: 400px;
        height: 400px;
        object-fit: cover;
        border-radius: 50%; /* Round placeholder */
        border: 10px solid white;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    .qualification-list li {
        margin-bottom: 10px;
        font-size: 1.05rem;
        color: #555;
    }

    /* Service Cards */
    .service-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        background: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        height: 100%;
        transition: transform 0.3s;
    }
    .service-card:hover {
        transform: translateY(-5px);
    }
    .service-img {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }
    .service-body {
        padding: 2rem;
    }

    /* Client List */
    .client-list {
        list-style: none;
        padding: 0;
    }
    .client-list li {
        padding: 5px 0;
        color: #666;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .client-list li::before {
        content: "•";
        color: var(--primary-color);
        font-weight: bold;
    }

    /* Contact Section */
    .contact-bg {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    }
</style>

<section class="hero-section">
    <div class="container text-center">
        <h1 class="hero-title">Welcome to Clear Optics</h1>
        <p class="hero-subtitle">Your vision, our care. Book an appointment online today!</p>
        <a href="#" class="btn btn-primary btn-lg px-5 py-3 rounded-pill disabled">Book Now</a>
    </div>
</section>

<section class="section-padding container">
    <div class="row align-items-center">
        <div class="col-lg-5 text-center mb-4 mb-lg-0">
            <img src="{{ asset('images/owner.jpg') }}" alt="Owner" class="owner-img">
        </div>
        <div class="col-lg-7">
            <h2 class="section-title">The Owner</h2>
            <h4 class="text-primary mb-4">DRA. Aileen Reyes-Mangao</h4>
            
            <ul class="list-unstyled qualification-list">
                <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Doctor of Optometry</li>
                <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Graduated at Centro Escolar University</li>
                <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Licensed OPTOMETRIST</li>
                <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Trained at leading optical shops before establishing her own clinic</li>
                <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Owns 2 branches - Clear Optics Optical Clinic</li>
                
                <li class="mt-4 fw-bold text-dark">Medical Affiliations:</li>
                <li class="ms-4">Sta. Rosa Hospital, Calamba, Medical Diagnostic Center (CMDC)</li>
                <li class="ms-4">St. Magdalene Polyclinic Diagnostic</li>
                <li class="ms-4">St. Michael's Diagnostic</li>
                <li class="ms-4">Proven Care</li>
                <li class="ms-4">St. James Hospital</li>
            </ul>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Our Service</h2>
            <p class="section-subtitle">What we can do</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <img src="{{ asset('images/service-practice.jpg') }}" class="service-img" alt="Practice">
                    <div class="service-body">
                        <h5 class="fw-bold mb-3">Our Practice</h5>
                        <p class="text-muted small">
                            Attending several seminars each year, it made us more INNOVATIVE and had PROFOUND METHOD of REFRACTION for our patients. We make sure that we do the correct way on treating the eyes of our patients.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <img src="{{ asset('images/service-staff.jpg') }}" class="service-img" alt="Staff">
                    <div class="service-body">
                        <h5 class="fw-bold mb-3">Our Staff</h5>
                        <p class="text-muted small">
                            Our staff are well trained to provide the best service. Polite and well mannered. They are taught to deliver the best frame for every different face contour of the patients. Suggestions/advices are always ready and open.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <img src="{{ asset('images/service-machine.jpg') }}" class="service-img" alt="Machine">
                    <div class="service-body">
                        <h5 class="fw-bold mb-3">Our Machine</h5>
                        <p class="text-muted small">
                            Our machines are bound to invade the new era of eye refraction, computerized auto refractor, trial lenses both subjective and objective findings.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <img src="{{ asset('images/service-doctor.jpg') }}" class="service-img" alt="Doctors">
                    <div class="service-body">
                        <h5 class="fw-bold mb-3">Our Doctors</h5>
                        <p class="text-muted small">
                            Our doctors are very competitive, accommodating and will not just take the reading of eyes, but will explain the state and condition of the eyes, how will it affect and how to cope with it.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding container">
    <div class="text-center mb-5">
        <h2 class="section-title">Our Clients</h2>
        <p class="section-subtitle">Trusted by Companies and Institutions</p>
    </div>

    <div class="row">
        <div class="col-md-3">
            <ul class="client-list">
                <li>ACBEL</li>
                <li>ACS</li>
                <li>AICHI</li>
                <li>ASIAN TECHNOLOGIES</li>
                <li>ADVANTEK</li>
                <li>CALAMBA STEEL</li>
                <li>CALAMBA WATER DISTRICT</li>
                <li>CLEAN FIT CORPORATION</li>
                <li>CHIYODA</li>
                <li>CYGNUS</li>
                <li>CORNER STEEL CO.</li>
                <li>DEPED</li>
                <li>KING GLOBAL, INC</li>
                <li>STA. ELENA GOLF CLUB</li>
            </ul>
        </div>
        <div class="col-md-3">
            <ul class="client-list">
                <li>DON BOSCO COLLEGE</li>
                <li>DAIKAOKU ELECTRONICS PHILS</li>
                <li>ENCHANTED KINGDOM</li>
                <li>FUJIFILM</li>
                <li>GOLDEN FIELD</li>
                <li>GENPACCO, INC</li>
                <li>GEMPHIL, INC</li>
                <li>HEWTECH</li>
                <li>I.A ALEGRE BUILDERS</li>
                <li>ITA INDUSTRIAL</li>
                <li>IMI, INC</li>
                <li>JFE SHOJI</li>
                <li>JP STEEL</li>
                <li>PHIL REALTY GROUP</li>
            </ul>
        </div>
        <div class="col-md-3">
            <ul class="client-list">
                <li>LEAD TECH</li>
                <li>LAGUNA MELTS</li>
                <li>MAGNA PRIME</li>
                <li>MANILA CORDAGE</li>
                <li>MATEX</li>
                <li>MIX PAINT, INC.</li>
                <li>MERALCO</li>
                <li>MME TECH</li>
                <li>MIYOSHI</li>
                <li>MOTOR CENTRAL</li>
                <li>NEDA</li>
                <li>NORTHSTAR SOLUTIONS</li>
                <li>NOZOMI</li>
                <li>NIPPON PAINT</li>
                <li>NISSAN MOTOR</li>
            </ul>
        </div>
        <div class="col-md-3">
            <ul class="client-list">
                <li>OKADA</li>
                <li>SHINEI</li>
                <li>SAMSUNG</li>
                <li>POWELL LITHOGRAPH</li>
                <li>SAGARA</li>
                <li>LEADENCE</li>
                <li>SAIKEI</li>
                <li>STIARE</li>
                <li>SENSOR</li>
                <li class="fw-bold text-primary">AND Many More...</li>
            </ul>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="contact-bg">
            <div class="row g-0">
                <div class="col-lg-6">
                    <img src="{{ asset('images/contact-bg.jpg') }}" style="width:100%; height:100%; object-fit:cover; min-height:400px;" alt="Clinic">
                </div>
                <div class="col-lg-6 p-5 d-flex flex-column justify-content-center">
                    <h2 class="section-title mb-4">Contact Us</h2>
                    
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-envelope-fill fs-4 text-primary me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Email</h6>
                            <p class="text-muted">reyesaileen2370@gmail.com</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-telephone-fill fs-4 text-primary me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Contact No</h6>
                            <p class="text-muted mb-0">+63 945 826 4969</p>
                            <p class="text-muted">+63 993 934 5096</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-geo-alt-fill fs-4 text-primary me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Address</h6>
                            <p class="text-muted">San Cristobal Highway, Calamba, Laguna</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <i class="bi bi-facebook fs-4 text-primary me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Facebook Page</h6>
                            <p class="text-muted">Clear Optics Eye Clinic</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection