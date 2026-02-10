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
</nav>