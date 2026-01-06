@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-users"></i> Users Management</h2>
            <p class="text-muted">View and manage all registered users</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Users</h5>
            <span class="badge bg-light text-dark">Total: {{ count($users) }}</span>
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
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>#{{ $user['id'] }}</td>
                            <td>
                                <i class="fas fa-user-circle text-primary"></i> 
                                {{ $user['name'] }}
                            </td>
                            <td>{{ $user['email'] }}</td>
                            <td>
                                @if($user['role'] === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($user['role'] === 'vendor')
                                    <span class="badge bg-success">Vendor</span>
                                @else
                                    <span class="badge bg-primary">Customer</span>
                                @endif
                            </td>
                            <td>{{ $user['phone'] ?? 'N/A' }}</td>
                            <td>{{ date('M d, Y', strtotime($user['created_at'])) }}</td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Active
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-user-shield fa-3x text-danger mb-3"></i>
                    <h3>{{ count(array_filter($users, fn($u) => $u['role'] === 'admin')) }}</h3>
                    <p class="text-muted">Administrators</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-store fa-3x text-success mb-3"></i>
                    <h3>{{ count(array_filter($users, fn($u) => $u['role'] === 'vendor')) }}</h3>
                    <p class="text-muted">Vendors</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h3>{{ count(array_filter($users, fn($u) => $u['role'] === 'customer')) }}</h3>
                    <p class="text-muted">Customers</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection