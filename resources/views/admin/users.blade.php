@extends('layouts.app')

@section('content')
<style>
    /* --- ADMIN RESPONSIVE LAYOUT --- */
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); overflow-x: hidden; }
    
    .admin-sidebar { 
        width: 260px; 
        background: #0F172A; 
        color: #94a3b8; 
        flex-shrink: 0; 
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        display: none; 
    }

    .admin-content { 
        flex-grow: 1; 
        background: #F1F5F9; 
        padding: 1.5rem; 
        min-width: 0; 
    }

    @media (min-width: 992px) {
        .admin-sidebar { display: flex; }
        .admin-content { padding: 2rem; }
    }
    
    .admin-nav-link {
        display: flex; align-items: center; padding: 12px 20px;
        color: #94a3b8; text-decoration: none; font-weight: 500;
        border-radius: 8px; margin-bottom: 5px; transition: all 0.2s;
    }
    .admin-nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav-link.active { background: #3B82F6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .admin-nav-link i { font-size: 1.1rem; margin-right: 12px; }

    .table-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }

    .custom-pills .nav-link {
        color: var(--primary-color);
        background-color: #fff;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    .custom-pills .nav-link:hover { background-color: #e2e8f0; }
    .custom-pills .nav-link.active {
        background-color: var(--primary-color) !important;
        color: white !important;
        border-color: var(--primary-color);
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
    }
    
    /* Specific style for Restricted Tab */
    .custom-pills .nav-link.text-danger.active {
        background-color: #dc3545 !important;
        border-color: #dc3545;
    }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
        <div class="admin-sidebar p-3 d-none d-lg-flex">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            @include('admin.partials.nav_links')
            <div class="mt-auto p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
                <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
            </div>
        </div>

        <div class="admin-content">
            <div class="d-flex align-items-center gap-3 mb-4">
                <button class="btn btn-white border shadow-sm d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu">
                    <i class="bi bi-list fs-5 text-primary"></i>
                </button>
                <h2 class="fw-bold text-dark mb-0">User Management</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if(isset($pendingUsers) && $pendingUsers->isNotEmpty())
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
                                        <td class="fw-bold">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }} {{ $user->suffix }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.view_id', $user->id) }}" target="_blank" class="text-decoration-none">
                                                <img src="{{ route('admin.users.view_id', $user->id) }}" class="rounded border shadow-sm" style="height: 40px; width: 60px; object-fit: cover;">
                                                <small class="ms-2 text-primary fw-bold">View <i class="bi bi-box-arrow-up-right"></i></small>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">Approve</button>
                                                </form>
                                                
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $user->id }}">
                                                    Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="rejectModal-{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-danger">Reject ID Verification</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-muted mb-3">Why are you rejecting <strong>{{ $user->first_name }}</strong>'s ID?</p>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="e.g. ID is blurry, Expired ID, Name mismatch..." required></textarea>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Confirm Reject</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $activeTab = request('tab') ?? 'registered';
                if(!in_array($activeTab, ['registered', 'restricted', 'guests'])) {
                    $activeTab = 'registered';
                }
            @endphp

            <ul class="nav nav-pills custom-pills mb-4" id="usersTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'registered' ? 'active' : '' }} rounded-pill px-4 fw-bold me-2" 
                            id="registered-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#registered" 
                            type="button">Registered Patients</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'restricted' ? 'active' : '' }} text-danger rounded-pill px-4 fw-bold me-2" 
                            id="restricted-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#restricted" 
                            type="button">
                        <i class="bi bi-slash-circle me-1"></i> Restricted
                        @if(isset($restrictedUsers) && $restrictedUsers->total() > 0)
                            <span class="badge bg-danger text-white ms-1">{{ $restrictedUsers->total() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'guests' ? 'active' : '' }} rounded-pill px-4 fw-bold" 
                            id="guests-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#guests" 
                            type="button">Walk-in Guests</button>
                </li>
            </ul>

            <div class="tab-content" id="usersTabContent">
                
                {{-- TAB 1: REGISTERED --}}
                <div class="tab-pane fade {{ $activeTab === 'registered' ? 'show active' : '' }}" id="registered">
                    <div class="table-card shadow-sm">
                        <div class="p-4 border-bottom bg-light bg-opacity-50">
                            <form action="{{ route('admin.users') }}" method="GET" class="row g-2">
                                <input type="hidden" name="tab" value="registered">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="filter_status" class="form-select">
                                        <option value="" {{ blank(request('filter_status')) ? 'selected' : '' }}>Verified Emails (Default)</option>
                                        <option value="pending_email" {{ request('filter_status') == 'pending_email' ? 'selected' : '' }}>Pending Email Verification</option>
                                        <option value="pending_id" {{ request('filter_status') == 'pending_id' ? 'selected' : '' }}>Pending ID Approval</option>
                                        <option value="active" {{ request('filter_status') == 'active' ? 'selected' : '' }}>Fully Verified (Active)</option>
                                        <option value="all" {{ request('filter_status') == 'all' ? 'selected' : '' }}>Show All (Including Unverified)</option>
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
                                        <th class="py-3 text-secondary small text-uppercase">Phone</th>
                                        <th class="py-3 text-secondary small text-uppercase">ID Proof</th>
                                        <th class="py-3 text-secondary small text-uppercase">Status</th>
                                        <th class="py-3 text-secondary small text-uppercase text-center">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allUsers as $user)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }} {{ $user->suffix }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone_number ?? '-' }}</td>
                                        
                                        <td>
                                            @if($user->id_photo_path)
                                                <a href="{{ route('admin.users.view_id', $user->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-eye-fill me-1"></i> View ID
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($user->account_status === \App\Enums\UserStatus::Restricted)
                                                <span class="badge bg-danger rounded-pill px-3">Restricted</span>
                                            @elseif($user->account_status === 'banned')
                                                <span class="badge bg-dark rounded-pill px-3">Banned</span>
                                            @elseif(!$user->hasVerifiedEmail())
                                                <span class="badge bg-warning text-dark rounded-pill px-3">Unverified Email</span>
                                            @elseif($user->is_verified)
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Verified (Active)</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Pending ID</span>
                                            @endif
                                        </td>
                                        
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-light text-primary fw-bold border" data-bs-toggle="modal" data-bs-target="#userModal{{ $user->id }}">
                                                <i class="bi bi-info-circle me-1"></i> View Info
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="userModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow-lg">
                                                <div class="modal-header border-bottom-0">
                                                    <h5 class="modal-title fw-bold text-primary">
                                                        <i class="bi bi-person-lines-fill me-2"></i> Patient Information
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body px-4 pb-4">
                                                    
                                                    <div class="p-3 bg-light rounded-3 mb-3">
                                                        <h6 class="text-uppercase small text-muted fw-bold mb-3">Full Name Breakdown</h6>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label class="small text-muted">First Name</label>
                                                                <div class="fw-bold">{{ $user->first_name }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small text-muted">Middle Name</label>
                                                                <div class="fw-bold">{{ $user->middle_name ?? '-' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small text-muted">Last Name</label>
                                                                <div class="fw-bold">{{ $user->last_name }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small text-muted">Suffix</label>
                                                                <div class="fw-bold">{{ $user->suffix ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-6">
                                                            <div class="border p-2 rounded-3 text-center">
                                                                <label class="small text-muted d-block">Gender</label>
                                                                <span class="fw-bold text-dark">
                                                                    {{ $user->gender }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="border p-2 rounded-3 text-center">
                                                                <label class="small text-muted d-block">Birthday</label>
                                                                <span class="fw-bold text-dark">
                                                                    {{ \Carbon\Carbon::parse($user->birthday)->format('M d, Y') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="border-top pt-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="small text-muted">Account Created:</span>
                                                            <span class="fw-bold text-secondary">
                                                                {{ $user->created_at->format('F d, Y h:i A') }}
                                                            </span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                                            <span class="small text-muted">Email Status:</span>
                                                            @if($user->hasVerifiedEmail())
                                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Verified</span>
                                                            @else
                                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Unverified</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No users found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row align-items-center p-3 border-top g-0">
                            <div class="col-lg-4 d-none d-lg-block order-lg-1"></div>
                            <div class="col-12 col-lg-4 text-center text-muted small order-2 order-lg-2 mt-2 mt-lg-0">
                                @if($allUsers->total() > 0)
                                    Showing {{ $allUsers->firstItem() }} to {{ $allUsers->lastItem() }} of {{ $allUsers->total() }} results
                                @else
                                    No results
                                @endif
                            </div>
                            <div class="col-12 col-lg-4 text-end order-1 order-lg-3">
                                {{ $allUsers->appends(['tab' => 'registered'])->links('partials.pagination') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: RESTRICTED ACCOUNTS --}}
                <div class="tab-pane fade {{ $activeTab === 'restricted' ? 'show active' : '' }}" id="restricted">
                    <div class="table-card shadow-sm border border-danger">
                        <div class="p-4 bg-danger bg-opacity-10 border-bottom border-danger">
                            <div class="d-flex align-items-center text-danger small fw-bold">
                                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                                These accounts have been penalized for multiple No-Shows or Late Cancellations.
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary small text-uppercase">Patient Name</th>
                                        <th class="py-3 text-secondary small text-uppercase">Strikes</th>
                                        <th class="py-3 text-secondary small text-uppercase">Violation Reason</th>
                                        <th class="py-3 text-secondary small text-uppercase">Restriction Date</th>
                                        <th class="py-3 text-secondary small text-uppercase text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($restrictedUsers as $user)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            {{ $user->first_name }} 
                                            {{ $user->middle_name }} 
                                            {{ $user->last_name }} 
                                            {{ $user->suffix }}
                                        </td>
                                        <td>
                                            <span class="badge bg-danger rounded-pill">{{ $user->strikes }} / 3</span>
                                        </td>
                                        <td>
                                            <small class="text-danger fw-bold">Multiple Violations</small>
                                            <br><span class="small text-muted">(Late Cancellations / No Shows)</span>
                                        </td>
                                        <td>{{ $user->updated_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#unrestrictModal-{{ $user->id }}">
                                                <i class="bi bi-unlock-fill me-1"></i> Undo Restriction
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="unrestrictModal-{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.users.unrestrict', $user->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-success">Lift Restriction</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-center">
                                                        <div class="mb-3">
                                                            <i class="bi bi-shield-check text-success display-3"></i>
                                                        </div>
                                                        <h5 class="fw-bold text-dark">Restore Account Access?</h5>
                                                        <p class="text-muted">
                                                            You are about to lift the restriction for <strong>{{ $user->first_name }}</strong>.
                                                            <br><br>
                                                            <span class="text-success small fw-bold">This will RESET their strikes to 0 and allow them to book appointments again.</span>
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0 justify-content-center">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success text-white rounded-pill px-4 fw-bold">Yes, Restore Access</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-shield-check display-4 text-success d-block mb-2"></i>
                                            No restricted accounts found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row align-items-center p-3 border-top g-0">
                            <div class="col-lg-4 d-none d-lg-block order-lg-1"></div>
                            <div class="col-12 col-lg-4 text-center text-muted small order-2 order-lg-2 mt-2 mt-lg-0">
                                @if($restrictedUsers->total() > 0)
                                    Showing {{ $restrictedUsers->firstItem() }} to {{ $restrictedUsers->lastItem() }} of {{ $restrictedUsers->total() }} results
                                @else
                                    No results
                                @endif
                            </div>
                            <div class="col-12 col-lg-4 text-end order-1 order-lg-3">
                                {{ $restrictedUsers->appends(['tab' => 'restricted'])->links('partials.pagination') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: GUESTS --}}
                <div class="tab-pane fade {{ $activeTab === 'guests' ? 'show active' : '' }}" id="guests">
                    <div class="table-card shadow-sm">
                        <div class="p-4 bg-light border-bottom">
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Walk-in guests who were manually booked by administrators.
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary small text-uppercase">Guest Name</th>
                                        <th class="py-3 text-secondary small text-uppercase">Email</th>
                                        <th class="py-3 text-secondary small text-uppercase">Phone</th>
                                        <th class="py-3 text-secondary small text-uppercase">Latest Visit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($guests as $guest)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            {{ $guest->patient_first_name }} 
                                            {{ $guest->patient_middle_name }}
                                            {{ $guest->patient_last_name }}
                                            {{ $guest->patient_suffix }}
                                        </td>
                                        <td>{{ $guest->patient_email }}</td>
                                        <td>{{ $guest->patient_phone ?? '-' }}</td>
                                        <td>{{ $guest->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">No guest records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row align-items-center p-3 border-top g-0">
                            <div class="col-lg-4 d-none d-lg-block order-lg-1"></div>
                            <div class="col-12 col-lg-4 text-center text-muted small order-2 order-lg-2 mt-2 mt-lg-0">
                                @if($guests->total() > 0)
                                    Showing {{ $guests->firstItem() }} to {{ $guests->lastItem() }} of {{ $guests->total() }} results
                                @else
                                    No results
                                @endif
                            </div>
                            <div class="col-12 col-lg-4 text-end order-1 order-lg-3">
                                {{ $guests->appends(['tab' => 'guests'])->links('partials.pagination') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileAdminMenu" style="background: #0F172A; width: 280px;">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-white fw-bold">Admin Console</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('admin.partials.nav_links')
        <div class="mt-5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
            <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-headset me-1"></i> Support Line</small>
            <small class="text-white opacity-75" style="font-size: 0.75rem;">Tech issues? Contact developers.</small>
        </div>
    </div>
</div>
@endsection