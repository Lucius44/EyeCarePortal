@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-white shadow-sm" style="min-height: 80vh;">
            <div class="d-flex flex-column p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    📅 Calendar
                </a>
                <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary mb-2 text-start border-0">
                    ✅ Manage Appointments
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-primary mb-2 text-start">
                    👥 Users List
                </a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">User Management</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm mb-5 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">⚠️ Pending Verifications</h5>
                </div>
                <div class="card-body">
                    @if($pendingUsers->isEmpty())
                        <p class="text-muted text-center my-3">No pending verification requests.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>ID Photo</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingUsers as $user)
                                    <tr>
                                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <a href="{{ asset('storage/' . $user->id_photo_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $user->id_photo_path) }}" 
                                                     class="img-thumbnail" 
                                                     style="height: 50px;">
                                            </a>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">All Patients</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allUsers as $user)
                            <tr>
                                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->is_verified)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-secondary">Unverified</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection