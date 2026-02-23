<style>
    /* Custom Logout Hover Effect */
    .logout-btn {
        color: #dc3545 !important; /* Bootstrap Danger Red */
        transition: all 0.25s ease;
    }
    
    .logout-btn:hover {
        background-color: rgba(220, 53, 69, 0.15) !important; /* Light Red Background */
        color: #b02a37 !important; /* Darker Red Text */
        transform: translateX(5px); /* Slide Right Animation */
        box-shadow: 2px 2px 8px rgba(220, 53, 69, 0.1);
    }
</style>

<nav class="nav flex-column gap-1">
    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="{{ route('admin.calendar') }}" class="admin-nav-link {{ request()->routeIs('admin.calendar') ? 'active' : '' }}">
        <i class="bi bi-calendar-week"></i> Calendar
    </a>
    <a href="{{ route('admin.appointments') }}" class="admin-nav-link {{ request()->routeIs('admin.appointments') ? 'active' : '' }}">
        <i class="bi bi-calendar-check"></i> Appointments
    </a>
    <a href="{{ route('admin.history') }}" class="admin-nav-link {{ request()->routeIs('admin.history') ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i> History
    </a>
    <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Users & Patients
    </a>
    
    <a href="{{ route('services.index') }}" class="admin-nav-link {{ request()->routeIs('services.index') ? 'active' : '' }}">
        <i class="bi bi-journal-medical"></i> Services
    </a>

    <a href="{{ route('admin.settings') }}" class="admin-nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
        <i class="bi bi-gear-fill"></i> Settings
    </a>

    {{-- NEW: Admin Notification Offcanvas Trigger --}}
    <a href="#adminNotificationsOffcanvas" data-bs-toggle="offcanvas" role="button" aria-controls="adminNotificationsOffcanvas" class="admin-nav-link d-flex align-items-center mt-3 border-top border-secondary border-opacity-25 pt-3">
        <i class="bi bi-bell-fill"></i> Notifications
        @php 
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $adminUnreadCount = $user->unreadNotifications->count(); 
        @endphp
        @if($adminUnreadCount > 0)
            <span class="badge bg-danger ms-auto rounded-pill" style="font-size: 0.75rem;">{{ $adminUnreadCount }}</span>
        @endif
    </a>
    
    <form action="{{ route('logout') }}" method="POST" class="mt-2">
        @csrf
        <button type="submit" class="admin-nav-link w-100 text-start border-0 bg-transparent logout-btn">
            <i class="bi bi-box-arrow-right"></i> Log Out
        </button>
    </form>
</nav>