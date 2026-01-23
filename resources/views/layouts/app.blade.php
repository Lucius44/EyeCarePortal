<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EyeCare Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Modern Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            padding: 0.8rem 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            letter-spacing: -0.5px;
            font-size: 1.5rem;
        }

        /* Nav Buttons */
        .nav-link {
            font-weight: 500;
            color: #555 !important;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-nav-primary {
            background-color: var(--primary-color);
            color: white !important;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
            color: white !important;
        }

        /* Dropdown Tweaks */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 10px;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 500;
        }
        .dropdown-item:hover {
            background-color: #f0f7ff;
            color: var(--primary-color);
        }
        .dropdown-item.text-danger:hover {
            background-color: #fff5f5;
            color: #dc3545;
        }

        /* Footer */
        footer {
            background: #fff;
            margin-top: auto;
            padding: 2rem 0;
            text-align: center;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-eye-fill fs-3"></i> ClearOptics
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    @guest
                        {{-- HIDE these buttons if we are on Login or Register page --}}
                        @if(!request()->routeIs('login') && !request()->routeIs('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-nav-primary" href="{{ route('register') }}">Create Account</a>
                            </li>
                        @endif
                    @else
                        @if(Auth::user()->role === \App\Enums\UserRole::Patient)
                            <li class="nav-item">
                                <a class="nav-link fw-bold text-primary" href="{{ route('appointments.index') }}">
                                    <i class="bi bi-calendar-plus me-1"></i> Book Appointment
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                    {{ substr(Auth::user()->first_name, 0, 1) }}
                                </div>
                                <span>{{ Auth::user()->first_name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ Auth::user()->role === \App\Enums\UserRole::Admin ? route('admin.dashboard') : route('dashboard') }}">
                                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                    </a>
                                </li>
                                @if(Auth::user()->role === \App\Enums\UserRole::Patient)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile') }}">
                                            <i class="bi bi-person me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('my.appointments') }}">
                                            <i class="bi bi-calendar-check me-2"></i> My Appointments
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('settings') }}">
                                            <i class="bi bi-gear me-2"></i> Account Settings
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="{{ Request::is('/') ? '' : 'py-4' }}">
        @yield('content')
    </main>

    <footer>
        <div class="container">
            &copy; {{ date('Y') }} ClearOptics Eye Clinic. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>