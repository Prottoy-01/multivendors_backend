@extends('layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('admin.vendors') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Vendors
            </a>
            <h2><i class="fas fa-store"></i> Vendor Details</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Vendor Information Card -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Vendor Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-store text-primary"></i> Shop Name:</strong>
                            <p class="mb-0 ms-4">{{ $vendor['shop_name'] }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-user text-primary"></i> Owner Name:</strong>
                            <p class="mb-0 ms-4">{{ $vendor['user']['name'] ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-envelope text-primary"></i> Email:</strong>
                            <p class="mb-0 ms-4">{{ $vendor['user']['email'] ?? 'N/A' }}</p>
                        </div>

                        <!-- ✅ FIXED: Phone now correctly read from users table -->
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-phone text-primary"></i> Phone:</strong>
                            <p class="mb-0 ms-4">
                                {{ $vendor['user']['phone'] ?? 'Not provided' }}
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-calendar text-primary"></i> Joined Date:</strong>
                            <p class="mb-0 ms-4">{{ date('F d, Y', strtotime($vendor['created_at'])) }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-flag text-primary"></i> Status:</strong>
                            <p class="mb-0 ms-4">
                                @if($vendor['status'] === 'approved')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                @elseif($vendor['status'] === 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @endif
                            </p>
                        </div>

                        <!-- ✅ FIXED: Address now comes from vendors table -->
                        <div class="col-md-12 mb-3">
                            <strong><i class="fas fa-map-marker-alt text-primary"></i> Address:</strong>
                            <p class="mb-0 ms-4">
                                {{ $vendor['address'] ?? 'Not provided' }}
                            </p>
                        </div>

                        <div class="col-md-12">
                            <strong><i class="fas fa-align-left text-primary"></i> Shop Description:</strong>
                            <p class="mb-0 ms-4">{{ $vendor['shop_description'] ?? 'No description provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Actions Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($vendor['status'] === 'pending')
                        <form action="{{ route('admin.vendors.approve', $vendor['id']) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Are you sure you want to approve this vendor?')">
                                <i class="fas fa-check"></i> Approve Vendor
                            </button>
                        </form>

                        <form action="{{ route('admin.vendors.reject', $vendor['id']) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Are you sure you want to reject this vendor?')">
                                <i class="fas fa-times"></i> Reject Vendor
                            </button>
                        </form>
                    @elseif($vendor['status'] === 'approved')
                        <form action="{{ route('admin.vendors.reject', $vendor['id']) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100"
                                    onclick="return confirm('Are you sure you want to revoke approval?')">
                                <i class="fas fa-ban"></i> Revoke Approval
                            </button>
                        </form>
                    @elseif($vendor['status'] === 'rejected')
                        <form action="{{ route('admin.vendors.approve', $vendor['id']) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Are you sure you want to approve this vendor?')">
                                <i class="fas fa-check"></i> Approve Vendor
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex justify-content-between">
                        <span><i class="fas fa-box text-primary"></i> Total Products:</span>
                        <strong>{{ $stats['total_products'] }}</strong>
                    </div>

                    <div class="mb-3 d-flex justify-content-between">
                        <span><i class="fas fa-shopping-bag text-success"></i> Total Orders:</span>
                        <strong>{{ $stats['total_orders'] }}</strong>
                    </div>

                    <div class="mb-3 d-flex justify-content-between">
                        <span><i class="fas fa-dollar-sign text-info"></i> Total Revenue:</span>
                        <strong>${{ number_format($stats['total_revenue'], 2) }}</strong>
                    </div>

                    <div class="mb-3 d-flex justify-content-between">
                        <span><i class="fas fa-clock text-warning"></i> Pending Orders:</span>
                        <strong>{{ $stats['pending_orders'] }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-check-circle text-success"></i> Completed:</span>
                        <strong>{{ $stats['completed_orders'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Products -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Vendor Products ({{ count($products) }})</h5>
                </div>
                <div class="card-body">
                    {{-- unchanged --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
