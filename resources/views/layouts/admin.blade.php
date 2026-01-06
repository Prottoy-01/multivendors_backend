@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
            <p class="text-muted">Overview of your e-commerce platform</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Users</h6>
                            <h2 class="mb-0">{{ $overview['total_users'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Vendors</h6>
                            <h2 class="mb-0">{{ $overview['total_vendors'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-store fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Products</h6>
                            <h2 class="mb-0">{{ $overview['total_products'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-box fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Orders</h6>
                            <h2 class="mb-0">{{ $overview['total_orders'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-dollar-sign"></i> Revenue Overview</h5>
                    <div class="row text-center mt-4">
                        <div class="col-md-4">
                            <h3 class="text-success">${{ number_format($overview['total_revenue'] ?? 0, 2) }}</h3>
                            <p class="text-muted">Total Revenue</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-primary">${{ number_format($overview['monthly_revenue'] ?? 0, 2) }}</h3>
                            <p class="text-muted">This Month</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-info">${{ number_format($overview['today_revenue'] ?? 0, 2) }}</h3>
                            <p class="text-muted">Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-users fa-2x d-block mb-2"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.vendors') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-store fa-2x d-block mb-2"></i>
                                Manage Vendors
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.categories') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-tags fa-2x d-block mb-2"></i>
                                Manage Categories
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.orders') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-shopping-cart fa-2x d-block mb-2"></i>
                                View Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    @if(isset($overview['pending_vendors']) && $overview['pending_vendors'] > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention!</strong> You have {{ $overview['pending_vendors'] }} vendor(s) waiting for approval.
                <a href="{{ route('admin.vendors') }}" class="alert-link">Review now</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection