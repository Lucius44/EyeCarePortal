@extends('layouts.app')

@section('content')

<style>
    /* --- HERO SECTION --- */
    .hero-wrapper {
        position: relative;
        min-height: 85vh;
        background: radial-gradient(circle at top right, #f1f5f9 0%, #ffffff 50%);
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    
    /* Abstract decorative blobs */
    .hero-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.6;
        z-index: 0;
    }
    .blob-1 { top: -10%; right: -5%; width: 500px; height: 500px; background: #e0f2fe; }
    .blob-2 { bottom: 0; left: -10%; width: 400px; height: 400px; background: #fef3c7; }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 4.5rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -2px;
        background: linear-gradient(135deg, #0F172A 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1.5rem;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: #64748B;
        font-weight: 400;
        line-height: 1.6;
        margin-bottom: 2.5rem;
        max-width: 500px;
    }

    .hero-img-card {
        position: relative;
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: rotate(-2deg);
        transition: transform 0.5s ease;
    }
    .hero-img-card:hover {
        transform: rotate(0deg) scale(1.02);
    }

    /* --- SECTION COMMON --- */
    .section-padding { padding: 100px 0; }
    
    .section-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--accent-color);
        margin-bottom: 1rem;
        display: block;
    }

    .section-heading {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 3rem;
        letter-spacing: -1px;
    }

    /* --- OWNER SECTION (Editorial Style) --- */
    .owner-card {
        position: relative;
        padding-top: 50px;
    }
    .owner-image-wrapper {
        position: relative;
        z-index: 2;
    }
    .owner-img {
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
    }
    .owner-bg-pattern {
        position: absolute;
        top: -30px;
        left: -30px;
        width: 100%;
        height: 100%;
        background-image: radial-gradient(#cbd5e1 2px, transparent 2px);
        background-size: 20px 20px;
        z-index: 1;
        opacity: 0.5;
        border-radius: 20px;
    }
    .qualification-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 1.05rem;
        color: #475569;
    }
    .qualification-item i {
        background: #eff6ff;
        color: var(--accent-color);
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 15px;
        font-size: 0.9rem;
    }

    /* --- SERVICES (Hover Cards) --- */
    .service-card {
        background: white;
        padding: 3rem 2rem;
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        height: 100%;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        border-color: transparent;
    }
    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--accent-color);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    .service-card:hover::before {
        transform: scaleX(1);
    }
    .service-icon-box {
        width: 60px;
        height: 60px;
        background: #f8fafc;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        transition: background 0.3s;
    }
    .service-card:hover .service-icon-box {
        background: var(--primary-color);
        color: white;
    }

    /* --- CLIENTS (Marquee) --- */
    .client-scroller {
        overflow: hidden;
        white-space: nowrap;
        position: relative;
    }
    .client-scroller::before, .client-scroller::after {
        content: "";
        position: absolute;
        top: 0;
        width: 100px;
        height: 100%;
        z-index: 2;
    }
    .client-scroller::before { left: 0; background: linear-gradient(to right, white, transparent); }
    .client-scroller::after { right: 0; background: linear-gradient(to left, white, transparent); }
    
    .client-track {
        display: inline-block;
        animation: scroll 40s linear infinite;
    }
    .client-tag {
        display: inline-block;
        padding: 10px 25px;
        margin: 0 10px;
        background: #f8fafc;
        border-radius: 50px;
        color: #64748B;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    @keyframes scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    /* --- CONTACT --- */
    .contact-wrapper {
        background: #0F172A;
        border-radius: 30px;
        color: white;
        overflow: hidden;
        position: relative;
    }
    .contact-info { padding: 4rem; }
    .contact-map-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.6;
        transition: opacity 0.5s;
    }
    .contact-wrapper:hover .contact-map-img { opacity: 0.8; }
    
    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .hero-title { font-size: 3rem; }
        .hero-img-card { display: none; } /* Hide heavy image on mobile */
        .contact-info { padding: 2rem; }
    }
</style>

<section class="hero-wrapper">
    <div class="hero-blob blob-1"></div>
    <div class="hero-blob blob-2"></div>
    
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-4 fw-bold">
                    <i class="bi bi-award-fill me-2"></i> Trusted by 50+ Corporations
                </span>
                <h1 class="hero-title">Vision Care Reimagined.</h1>
                <p class="hero-subtitle">
                    Experience world-class optometry with advanced diagnostics and personalized care. Your eyes deserve the expert touch of ClearOptics.
                </p>
                
                <div class="d-flex gap-3">
                    @auth
                        <a href="{{ route('appointments.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg fw-bold">
                            Book Appointment
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg fw-bold">
                            Book Appointment
                        </a>
                        <a href="#services" class="btn btn-white bg-white text-dark btn-lg rounded-pill px-4 py-3 shadow-sm fw-bold border">
                            Our Services
                        </a>
                    @endauth
                </div>
            </div>
            
            <div class="col-lg-5 offset-lg-1 d-none d-lg-block">
                <div class="hero-img-card">
                    <img src="{{ asset('images/hero-bg.jpg') }}" alt="Eye Clinic Interior" class="w-100 d-block">
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Our Expertise</span>
            <h2 class="section-heading">Clinical Excellence</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon-box">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Precision Practice</h5>
                    <p class="text-muted small mb-0">
                        Our methods are constantly updated through annual seminars. We utilize a profound method of refraction ensuring every diagnosis is accurate and tailored to your lifestyle.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon-box">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Expert Staff</h5>
                    <p class="text-muted small mb-0">
                        Polite, well-mannered, and highly trained. Our team doesn't just assist; they analyze your face contour to suggest the perfect frame that enhances your personality.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon-box">
                        <i class="bi bi-motherboard"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Advanced Tech</h5>
                    <p class="text-muted small mb-0">
                        We've invaded the new era of eye refraction. Our clinic is equipped with computerized auto-refractors and objective diagnostic machines for flawless results.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon-box">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Holistic Care</h5>
                    <p class="text-muted small mb-0">
                        We go beyond the reading. Our doctors explain the "Why" and "How" of your eye condition, empowering you with knowledge on how to cope and improve your vision health.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="section-padding bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-5 mb-lg-0">
                <div class="owner-card text-center text-lg-start">
                    <div class="owner-image-wrapper">
                        <img src="{{ asset('images/owner.jpg') }}" alt="Dr. Aileen Reyes-Mangao" class="owner-img">
                    </div>
                    <div class="owner-bg-pattern d-none d-lg-block"></div>
                </div>
            </div>
            
            <div class="col-lg-6 offset-lg-1">
                <span class="section-label">Meet the Doctor</span>
                <h2 class="section-heading mb-4">Dra. Aileen Reyes-Mangao</h2>
                <p class="lead text-muted mb-5">
                    With decades of experience and two successful branches, Dr. Reyes-Mangao combines medical expertise with a passion for community health.
                </p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="qualification-item">
                            <i class="bi bi-check-lg"></i>
                            <span>Doctor of Optometry</span>
                        </div>
                        <div class="qualification-item">
                            <i class="bi bi-mortarboard"></i>
                            <span>Centro Escolar University</span>
                        </div>
                        <div class="qualification-item">
                            <i class="bi bi-award"></i>
                            <span>Licensed Optometrist</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="qualification-item">
                            <i class="bi bi-hospital"></i>
                            <span>Affiliated: Sta. Rosa Hospital</span>
                        </div>
                        <div class="qualification-item">
                            <i class="bi bi-building"></i>
                            <span>Affiliated: St. James Hospital</span>
                        </div>
                        <div class="qualification-item">
                            <i class="bi bi-shop"></i>
                            <span>Owner: Clear Optics Clinics</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding overflow-hidden">
    <div class="container text-center mb-5">
        <p class="section-label mb-2">Trusted Partnerships</p>
        <h3 class="fw-bold">Supporting Vision for 50+ Organizations</h3>
    </div>

    <div class="client-scroller">
        <div class="client-track">
            <span class="client-tag">ACBEL</span>
            <span class="client-tag">SAMSUNG</span>
            <span class="client-tag">FUJIFILM</span>
            <span class="client-tag">MERALCO</span>
            <span class="client-tag">ENCHANTED KINGDOM</span>
            <span class="client-tag">DEPED</span>
            <span class="client-tag">NISSAN MOTOR</span>
            <span class="client-tag">JFE SHOJI</span>
            <span class="client-tag">NORTHSTAR SOLUTIONS</span>
            <span class="client-tag">OKADA</span>
            <span class="client-tag">CHIYODA</span>
            <span class="client-tag">CALAMBA WATER DISTRICT</span>
            <span class="client-tag">ACBEL</span>
            <span class="client-tag">SAMSUNG</span>
            <span class="client-tag">FUJIFILM</span>
            <span class="client-tag">MERALCO</span>
            <span class="client-tag">ENCHANTED KINGDOM</span>
            <span class="client-tag">DEPED</span>
            <span class="client-tag">NISSAN MOTOR</span>
            <span class="client-tag">JFE SHOJI</span>
            <span class="client-tag">NORTHSTAR SOLUTIONS</span>
            <span class="client-tag">OKADA</span>
            <span class="client-tag">CHIYODA</span>
            <span class="client-tag">CALAMBA WATER DISTRICT</span>
        </div>
    </div>
</section>

<section id="contact" class="container pb-5">
    <div class="contact-wrapper">
        <div class="row g-0">
            <div class="col-lg-5 order-2 order-lg-1">
                <img src="{{ asset('images/contact-bg.jpg') }}" alt="Clinic Location" class="contact-map-img">
            </div>
            <div class="col-lg-7 order-1 order-lg-2 d-flex align-items-center">
                <div class="contact-info">
                    <span class="section-label text-white-50">Get in Touch</span>
                    <h2 class="mb-5">Visit Our Clinic Today</h2>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="me-4 text-warning fs-4"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1">San Cristobal Highway</h5>
                            <p class="text-white-50 mb-0">Calamba, Laguna, Philippines</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="me-4 text-warning fs-4"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1">+63 945 826 4969</h5>
                            <p class="text-white-50 mb-0">Mon - Sun, 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="me-4 text-warning fs-4"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1">reyesaileen2370@gmail.com</h5>
                            <p class="text-white-50 mb-0">Send us a message anytime</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection