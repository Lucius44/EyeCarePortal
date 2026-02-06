@extends('layouts.app')

@section('content')
<style>
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); }
    .admin-sidebar { width: 260px; background: #0F172A; color: #94a3b8; flex-shrink: 0; }
    .admin-content { flex-grow: 1; background: #F1F5F9; padding: 2rem; }
    
    .admin-nav-link {
        display: flex; align-items: center; padding: 12px 20px;
        color: #94a3b8; text-decoration: none; font-weight: 500;
        border-radius: 8px; margin-bottom: 5px; transition: all 0.2s;
    }
    .admin-nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav-link.active { background: #3B82F6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .admin-nav-link i { font-size: 1.1rem; margin-right: 12px; }

    .table-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
        <div class="admin-sidebar p-3 d-none d-lg-block">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            <nav class="nav flex-column gap-1">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a href="{{ route('admin.calendar') }}" class="admin-nav-link"><i class="bi bi-calendar-week"></i> Calendar</a>
                <a href="{{ route('admin.appointments') }}" class="admin-nav-link"><i class="bi bi-calendar-check"></i> Appointments</a>
                <a href="{{ route('admin.history') }}" class="admin-nav-link"><i class="bi bi-clock-history"></i> History</a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link active"><i class="bi bi-people"></i> Users & Patients</a>
            </nav>
        </div>

        <div class="admin-content">
            <h2 class="fw-bold text-dark mb-4">User Management</h2>

            @if(session('success'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if($pendingUsers->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 mb-5 border-start border-5 border-warning overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning text-dark rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-exclamation-lg fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Pending Identity Verifications</h5>
                                <p class="text-muted small mb-0">The following users have uploaded IDs and require approval.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="text-secondary small text-uppercase">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>ID Proof</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingUsers as $user)
                                    <tr>
                                        <td class="fw-bold">{{ $user->first_name }} {{ $user->last_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <a href="{{ asset('storage/' . $user->id_photo_path) }}" target="_blank" class="text-decoration-none">
                                                <img src="{{ asset('storage/' . $user->id_photo_path) }}" class="rounded border shadow-sm" style="height: 40px; width: 60px; object-fit: cover;">
                                                <small class="ms-2 text-primary fw-bold">View <i class="bi bi-box-arrow-up-right"></i></small>
                                            </a>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tabs --}}
            <ul class="nav nav-pills mb-4" id="usersTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 fw-bold me-2" id="registered-tab" data-bs-toggle="tab" data-bs-target="#registered" type="button">Registered Patients</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="guests-tab" data-bs-toggle="tab" data-bs-target="#guests" type="button">Walk-in Guests</button>
                </li>
            </ul>

            <div class="tab-content" id="usersTabContent">
                
                {{-- REGISTERED USERS --}}
                <div class="tab-pane fade show active" id="registered">
                    <div class="table-card shadow-sm">
                        <div class="p-4 border-bottom bg-light bg-opacity-50">
                            <form action="{{ route('admin.users') }}" method="GET" class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="filter_status" class="form-select">
                                        <option value="">All Users</option>
                                        <option value="verified" {{ request('filter_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="unverified" {{ request('filter_status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">Search</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.users') }}" class="btn btn-light border w-100">Clear</a>
                                </div>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary small text-uppercase">User</th>
                                        <th class="py-3 text-secondary small text-uppercase">Email</th>
                                        <th class="py-3 text-secondary small text-uppercase">Status</th>
                                        <th class="py-3 text-secondary small text-uppercase">Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allUsers as $user)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $user->first_name }} {{ $user->last_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->is_verified)
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Verified</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Unverified</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No users found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- GUESTS TAB --}}
                <div class="tab-pane fade" id="guests">
                    <div class="table-card shadow-sm">
                        <div class="p-4 bg-light border-bottom">
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Walk-in guests who were manually booked by administrators. They do not have login credentials.
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary small text-uppercase">Guest Name</th>
                                        <th class="py-3 text-secondary small text-uppercase">Email</th>
                                        <th class="py-3 text-secondary small text-uppercase">Phone</th>
                                        <th class="py-3 text-secondary small text-uppercase">First Visit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($guests as $guest)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            {{ $guest->patient_first_name }} 
                                            {{ $guest->patient_middle_name }}
                                            {{ $guest->patient_last_name }}
                                        </td>
                                        <td>{{ $guest->patient_email }}</td>
                                        <td>{{ $guest->patient_phone ?? '-' }}</td>
                                        <td>{{ $guest->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No guest records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection