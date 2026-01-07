@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-users"></i> Manage Users</h2>
            <p class="text-muted">View and manage all user accounts</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Users ({{ count($users) }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user['id'] }}</td>
                            <td>
                                <strong>{{ $user['name'] }}</strong>
                                @if($user['id'] === auth()->id())
                                    <span class="badge bg-info ms-2">You</span>
                                @endif
                            </td>
                            <td>{{ $user['email'] }}</td>
                            <td>
                                @if($user['role'] === 'admin')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-user-shield"></i> Admin
                                    </span>
                                @elseif($user['role'] === 'vendor')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-store"></i> Vendor
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-user"></i> Customer
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(($user['status'] ?? 'active') === 'active')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @elseif(($user['status'] ?? 'active') === 'suspended')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-pause-circle"></i> Suspended
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban"></i> Banned
                                    </span>
                                @endif
                            </td>
                            <td>{{ date('M d, Y', strtotime($user['created_at'])) }}</td>
                            <td>
                                @if($user['id'] !== auth()->id() && $user['role'] !== 'admin')
                                    @if(($user['status'] ?? 'active') === 'active')
                                        {{-- Suspend Button --}}
                                        <form action="{{ route('admin.users.suspend', $user['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" 
                                                    onclick="return confirm('Are you sure you want to suspend this user?')">
                                                <i class="fas fa-pause"></i> Suspend
                                            </button>
                                        </form>
                                        
                                        {{-- Ban Button --}}
                                        <form action="{{ route('admin.users.ban', $user['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to ban this user permanently?')">
                                                <i class="fas fa-ban"></i> Ban
                                            </button>
                                        </form>
                                    @elseif(($user['status'] ?? 'active') === 'suspended')
                                        {{-- Activate Button --}}
                                        <form action="{{ route('admin.users.activate', $user['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('Are you sure you want to activate this user?')">
                                                <i class="fas fa-check"></i> Activate
                                            </button>
                                        </form>
                                        
                                        {{-- Ban Button --}}
                                        <form action="{{ route('admin.users.ban', $user['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to ban this user permanently?')">
                                                <i class="fas fa-ban"></i> Ban
                                            </button>
                                        </form>
                                    @else
                                        {{-- Activate Button (for banned users) --}}
                                        <form action="{{ route('admin.users.activate', $user['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('Are you sure you want to activate this user?')">
                                                <i class="fas fa-check"></i> Activate
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if($user['id'] === auth()->id())
                                        <span class="text-muted">
                                            <i class="fas fa-lock"></i> Cannot modify own account
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-shield-alt"></i> Admin account
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h3>{{ collect($users)->where('status', 'active')->count() }}</h3>
                    <p class="text-muted">Active Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-pause-circle fa-3x text-warning mb-3"></i>
                    <h3>{{ collect($users)->where('status', 'suspended')->count() }}</h3>
                    <p class="text-muted">Suspended</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                    <h3>{{ collect($users)->where('status', 'banned')->count() }}</h3>
                    <p class="text-muted">Banned</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h3>{{ count($users) }}</h3>
                    <p class="text-muted">Total Users</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection