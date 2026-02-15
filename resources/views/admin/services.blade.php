@extends('layouts.app')

@section('content')
<style>
    /* Reuse Admin Layout Styles */
    .admin-wrapper { display: flex; min-height: calc(100vh - 80px); overflow-x: hidden; }
    .admin-sidebar { width: 260px; background: #0F172A; color: #94a3b8; flex-shrink: 0; display: none; flex-direction: column; }
    .admin-content { flex-grow: 1; background: #F1F5F9; padding: 1.5rem; }
    @media (min-width: 992px) { .admin-sidebar { display: flex; } .admin-content { padding: 2rem; } }
    .admin-nav-link { display: flex; align-items: center; padding: 12px 20px; color: #94a3b8; text-decoration: none; font-weight: 500; border-radius: 8px; margin-bottom: 5px; transition: all 0.2s; }
    .admin-nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav-link.active { background: #3B82F6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .admin-nav-link i { font-size: 1.1rem; margin-right: 12px; }
</style>

<div class="container-fluid p-0">
    <div class="admin-wrapper">
        
        {{-- SIDEBAR --}}
        <div class="admin-sidebar p-3 d-none d-lg-flex">
            <div class="mb-4 px-2 py-3">
                <small class="text-uppercase fw-bold text-white opacity-50 ls-1">Admin Console</small>
            </div>
            @include('admin.partials.nav_links')
        </div>

        {{-- MAIN CONTENT --}}
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Clinic Services</h2>
                    <p class="text-secondary mb-0 small">Manage the list of services available for booking.</p>
                </div>
                
                {{-- Add New Button --}}
                <button class="btn btn-primary rounded-pill shadow-sm fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-lg me-2"></i> Add Service
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 text-secondary text-uppercase small fw-bold">Service Name</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Description</th>
                                <th class="py-3 text-end pe-4 text-secondary text-uppercase small fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $service->name }}</td>
                                <td class="text-muted small">
                                    {{ $service->description ?? 'No description provided.' }}
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-white border shadow-sm me-1 text-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editServiceModal{{ $service->id }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    
                                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? Removing this service will not affect past appointments.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-white border shadow-sm text-danger">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit Service</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('services.update', $service->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase">Service Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase">Description</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $service->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0">
                                                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x fs-1 opacity-25"></i>
                                    <p class="mt-2 mb-0">No services found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Service Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Glaucoma Screening" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief details about the service..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection