<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClearOptics | Professional Eye Care</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/clearoptics-logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    <style>
        :root {
            --primary-color: #0F172A; /* Deep Navy */
            --accent-color: #3B82F6;  /* Bright Blue */
            --brand-gold: #D97706;    /* Subtle Gold */
            --bg-body: #F8FAFC;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: #334155;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- Premium Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 600;
            color: #64748B !important;
            font-size: 0.9rem;
            margin: 0 5px;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--accent-color) !important;
        }

        /* Nav Action Button */
        .btn-nav-primary {
            background: var(--primary-color);
            color: white !important;
            border-radius: 50px;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }
        
        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.25);
            background: #1e293b;
        }

        /* Dropdown Polish */
        .dropdown-menu {
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 0.75rem;
            margin-top: 15px !important;
            animation: slideIn 0.2s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            font-weight: 500;
            color: #475569;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background-color: #F1F5F9;
            color: var(--accent-color);
        }

        /* --- Notification Bell Styling --- */
        .notification-bell {
            position: relative;
            font-size: 1.25rem;
            color: #64748B;
            transition: color 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
        }
        .notification-bell:hover {
            color: var(--accent-color);
            background-color: #F1F5F9;
        }
        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: #EF4444; /* Red badge */
            color: white;
            font-size: 0.65rem;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 10px;
            line-height: 1;
            border: 2px solid white;
        }
        .dropdown-menu-notifications {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            padding: 0;
        }
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            white-space: normal; /* Allow text wrapping */
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item:hover {
            background-color: #F8FAFC;
        }
        .notification-unread {
            background-color: #EFF6FF; /* Very light blue for unread */
        }
        .notification-unread:hover {
             background-color: #DBEAFE;
        }

        /* Footer - Flattened & Slimmer */
        footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 1rem 0; 
            margin-top: auto;
        }
        
        footer p {
            color: #94a3b8;
            font-size: 0.85rem;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

    {{-- CONDITIONAL NAVBAR --}}
    @if(
        !request()->routeIs('login') && 
        !request()->routeIs('register') && 
        !request()->is('admin*') && 
        !request()->routeIs('verification.notice') &&
        !request()->routeIs('password.request') && 
        !request()->routeIs('password.reset')
    )
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                    <img src="{{ asset('images/clearoptics-logo.png') }}" alt="ClearOptics Logo" style="height: 35px; width: auto;">
                    <span>Clear<span class="text-primary">Optics</span></span>
                </a>

                {{-- MOBILE RIGHT CONTROLS: Notification Bell + Hamburger Menu --}}
                <div class="d-flex align-items-center d-lg-none gap-2">
                    @auth
                        @php 
                            $unreadCount = Auth::user()->unreadNotifications->count(); 
                            $notifications = Auth::user()->notifications()->take(5)->get();
                        @endphp
                        <div class="dropdown">
                            <a class="notification-bell" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                @if($unreadCount > 0)
                                    <span class="notification-badge">{{ $unreadCount }}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notifications shadow-lg">
                                <li class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center rounded-top">
                                    <span class="fw-bold small text-uppercase text-muted">Notifications</span>
                                    @if($unreadCount > 0)
                                        <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.8rem;">Mark all as read</button>
                                        </form>
                                    @endif
                                </li>
                                @forelse($notifications as $notification)
                                    <li>
                                        <a class="dropdown-item notification-item {{ $notification->read_at ? '' : 'notification-unread' }}" 
                                           href="{{ route('notifications.read', $notification->id) }}">
                                            <div class="d-flex flex-column">
                                                <span class="small fw-semibold text-dark">{{ $notification->data['message'] ?? 'New notification' }}</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item text-muted small py-3 text-center">No notifications yet.</span></li>
                                @endforelse
                            </ul>
                        </div>
                    @endauth

                    <button class="navbar-toggler border-0 shadow-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center gap-2">
                        
                        @if(request()->routeIs('home'))
                            <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                            <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                            <li class="nav-item"><span class="text-muted mx-2 opacity-25">|</span></li>
                        @endif

                        @guest
                            @if(!request()->routeIs('login') && !request()->routeIs('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Log In</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn-nav-primary ms-2" href="{{ route('register') }}">Get Started</a>
                                </li>
                            @endif
                        @else
                            {{-- If Patient, show Book Appointment --}}
                            @if(Auth::user()->role === \App\Enums\UserRole::Patient && !request()->routeIs('appointments.index'))
                                <li class="nav-item">
                                    <a class="nav-link text-primary fw-bold" href="{{ route('appointments.index') }}">
                                        Book Appointment
                                    </a>
                                </li>
                            @endif

                            {{-- DESKTOP NOTIFICATION BELL --}}
                            <li class="nav-item dropdown ms-2 d-none d-lg-block">
                                <a class="notification-bell" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-bell"></i>
                                    @if($unreadCount > 0)
                                        <span class="notification-badge">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notifications shadow-lg">
                                    <li class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center rounded-top">
                                        <span class="fw-bold small text-uppercase text-muted">Notifications</span>
                                        @if($unreadCount > 0)
                                            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                                @csrf
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.8rem;">Mark all as read</button>
                                            </form>
                                        @endif
                                    </li>
                                    @forelse($notifications as $notification)
                                        <li>
                                            <a class="dropdown-item notification-item {{ $notification->read_at ? '' : 'notification-unread' }}" 
                                               href="{{ route('notifications.read', $notification->id) }}">
                                                <div class="d-flex flex-column">
                                                    <span class="small fw-semibold text-dark">{{ $notification->data['message'] ?? 'New notification' }}</span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <li><span class="dropdown-item text-muted small py-3 text-center">No notifications yet.</span></li>
                                    @endforelse
                                </ul>
                            </li>

                            {{-- User Dropdown --}}
                            <li class="nav-item dropdown ms-2 d-none d-lg-block">
                                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 1rem;">
                                        {{ substr(Auth::user()->first_name, 0, 1) }}
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="px-3 py-2 text-muted small text-uppercase fw-bold ls-1">Account</li>
                                    <li>
                                        <a class="dropdown-item" href="{{ Auth::user()->role === \App\Enums\UserRole::Admin ? route('admin.dashboard') : route('dashboard') }}">
                                            <i class="bi bi-grid-1x2 me-2"></i> Dashboard
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
                                                <i class="bi bi-gear me-2"></i> Settings
                                            </a>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider my-2"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>

                            {{-- Mobile Menu Content --}}
                            <li class="nav-item d-lg-none mt-2 w-100">
                                <hr class="text-secondary opacity-10">
                                <div class="px-2 mb-2 text-uppercase text-muted small fw-bold">
                                    Hi, {{ Auth::user()->first_name }}
                                </div>
                                
                                <a class="nav-link py-2 ps-2" href="{{ Auth::user()->role === \App\Enums\UserRole::Admin ? route('admin.dashboard') : route('dashboard') }}">
                                    <i class="bi bi-grid-1x2 me-2"></i> Dashboard
                                </a>
                                
                                @if(Auth::user()->role === \App\Enums\UserRole::Patient)
                                    <a class="nav-link py-2 ps-2" href="{{ route('profile') }}">
                                        <i class="bi bi-person me-2"></i> My Profile
                                    </a>
                                    <a class="nav-link py-2 ps-2" href="{{ route('my.appointments') }}">
                                        <i class="bi bi-calendar-check me-2"></i> My Appointments
                                    </a>
                                    <a class="nav-link py-2 ps-2" href="{{ route('settings') }}">
                                        <i class="bi bi-gear me-2"></i> Settings
                                    </a>
                                @endif
                                
                                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="nav-link text-danger py-2 ps-2 bg-transparent border-0 w-100 text-start">
                                        <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                    </button>
                                </form>
                            </li>

                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        
        {{-- Padding Adjuster when Navbar is present --}}
        <div style="padding-top: 80px; flex: 1;">
            @yield('content')
        </div>
    @else
        {{-- No Navbar Padding for Clean Pages --}}
        <div style="flex: 1;">
            @yield('content')
        </div>
    @endif

    <footer>
        <div class="container text-center">
            <p>
                <span>&copy; {{ date('Y') }} ClearOptics Eye Clinic. Excellence in Vision Care.</span>
            </p>
        </div>
    </footer>

    {{-- NEW: ADMIN OFFCANVAS NOTIFICATION PANEL --}}
    @if(Auth::check() && Auth::user()->role === \App\Enums\UserRole::Admin)
    <div class="offcanvas offcanvas-end" tabindex="-1" id="adminNotificationsOffcanvas" aria-labelledby="adminNotificationsLabel" style="width: 350px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold text-dark" id="adminNotificationsLabel"><i class="bi bi-bell-fill text-primary me-2"></i>Notifications</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @php
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $adminUnread = $user->unreadNotifications->count();
                $adminNotifs = $user->notifications()->take(10)->get();
            @endphp
            @if($adminUnread > 0)
                <div class="p-2 border-bottom text-end bg-light">
                    <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm text-decoration-none fw-semibold">Mark all as read</button>
                    </form>
                </div>
            @endif
            <div class="list-group list-group-flush">
                @forelse($adminNotifs as $notification)
                    <a href="{{ route('admin.notifications.read', $notification->id) }}" 
                       class="list-group-item list-group-item-action p-3 border-bottom {{ $notification->read_at ? '' : 'bg-primary bg-opacity-10' }}">
                        <div class="d-flex w-100 justify-content-between mb-1">
                            <small class="text-muted fw-semibold" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</small>
                            @if(!$notification->read_at)
                                <span class="badge bg-primary rounded-circle p-1"><span class="visually-hidden">Unread</span></span>
                            @endif
                        </div>
                        <p class="mb-0 small text-dark">{{ $notification->data['message'] ?? 'New notification' }}</p>
                    </a>
                @empty
                    <div class="p-5 text-center text-muted d-flex flex-column align-items-center">
                        <i class="bi bi-bell-slash fs-1 mb-2 opacity-25"></i>
                        <span class="small fw-semibold">No notifications yet.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>