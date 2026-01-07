@extends('layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-store"></i> Vendor Dashboard</h2>
            <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
    </div>

    {{-- ✅ STEP 4: Vendor Status Alerts --}}
    @php
        $vendor = \App\Models\Vendor::where('user_id', Auth::id())->first();
        $isApproved = $vendor && $vendor->status === 'approved';
        $isPending = $vendor && $vendor->status === 'pending';
        $isRejected = $vendor && $vendor->status === 'rejected';
    @endphp
    
    @if($isRejected)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h4 class="alert-heading">
                <i class="fas fa-times-circle"></i> Vendor Account Rejected
            </h4>
            <p>
                We're sorry, but your vendor account has been <strong>rejected</strong> by the administrator. 
                This means you cannot add new products or manage orders at this time.
            </p>
            <hr>
            <p class="mb-0">
                <strong>What you can do:</strong>
            </p>
            <ul class="mb-0">
                <li>Contact our support team for more information about the rejection</li>
                <li>Review our vendor guidelines and reapply if eligible</li>
                <li>Email us at: <strong>support@multivendorshop.com</strong></li>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif($isPending)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h4 class="alert-heading">
                <i class="fas fa-clock"></i> Pending Approval
            </h4>
            <p class="mb-0">
                Your vendor account is currently <strong>under review</strong> by our administrators. 
                You'll receive a notification once your account is approved. 
                Most applications are reviewed within 24-48 hours.
            </p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif($isApproved)
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Products</h6>
                            <h2 class="mb-0">{{ $analytics['total_products'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-box fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('vendor.products.index') }}" class="btn btn-light btn-sm mt-3">
                        Manage Products
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Orders</h6>
                            <h2 class="mb-0">{{ $analytics['total_orders'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-shopping-bag fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('vendor.orders') }}" class="btn btn-light btn-sm mt-3">
                        View Orders
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Revenue</h6>
                            <h2 class="mb-0">${{ number_format($analytics['total_revenue'] ?? 0, 2) }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('vendor.analytics') }}" class="btn btn-light btn-sm mt-3">
                        View Analytics
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pending Orders</h6>
                            <h2 class="mb-0">{{ $analytics['pending_orders'] ?? 0 }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('vendor.orders') }}" class="btn btn-light btn-sm mt-3">
                        Process Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    @if(!$isApproved)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-lock"></i> Account Not Approved
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            @if($isApproved)
                                <a href="{{ route('vendor.products.create') }}" class="btn btn-outline-primary w-100 py-3">
                                    <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                    Add New Product
                                </a>
                            @else
                                <button class="btn btn-outline-secondary w-100 py-3" disabled>
                                    <i class="fas fa-lock fa-2x d-block mb-2"></i>
                                    Add New Product
                                    <small class="d-block text-muted">(Requires Approval)</small>
                                </button>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-box fa-2x d-block mb-2"></i>
                                Manage Products
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.orders') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-shopping-bag fa-2x d-block mb-2"></i>
                                View Orders
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('vendor.profile') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-store fa-2x d-block mb-2"></i>
                                Shop Settings
                            </a>
                        </div>
                    </div>
                    
                    {{-- Additional Warning for Non-Approved Vendors --}}
                    @if(!$isApproved)
                        <div class="alert alert-info mb-0 mt-3">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Limited Access:</strong> 
                            Some actions are disabled until your vendor account is approved by an administrator.
                            @if($isPending)
                                Your application is currently being reviewed.
                            @elseif($isRejected)
                                Your application has been rejected. Please contact support for more information.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Orders</h5>
                </div>
                <div class="card-body">
                    @if(count($recentOrders) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($recentOrders, 0, 5) as $order)
                                    <tr>
                                        <td><strong>#{{ $order['id'] }}</strong></td>
                                        <td>{{ $order['user']['name'] ?? 'N/A' }}</td>
                                        <td>{{ date('M d, Y', strtotime($order['created_at'])) }}</td>
                                        <td>{{ count($order['items']) }} items</td>
                                        <td><strong>${{ number_format($order['grand_total'] ?? $order['total_amount'], 2) }}</strong></td>
                                        <td>
                                            @if($order['status'] === 'delivered')
                                                <span class="badge bg-success">Delivered</span>
                                            @elseif($order['status'] === 'shipped')
                                                <span class="badge bg-info">Shipped</span>
                                            @elseif($order['status'] === 'processing')
                                                <span class="badge bg-primary">Processing</span>
                                            @elseif($order['status'] === 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vendor.orders') }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($recentOrders) > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('vendor.orders') }}" class="btn btn-outline-primary">
                                    View All Orders <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <p class="text-muted">
                                @if($isApproved)
                                    No orders yet. Your products are waiting to be discovered!
                                @else
                                    Orders will appear here once your vendor account is approved.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Account Status Card --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-{{ $isApproved ? 'success' : ($isRejected ? 'danger' : 'warning') }}">
                <div class="card-header bg-{{ $isApproved ? 'success' : ($isRejected ? 'danger' : 'warning') }} text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-{{ $isApproved ? 'check-circle' : ($isRejected ? 'times-circle' : 'clock') }}"></i>
                        Account Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            @if($isApproved)
                                <h5 class="text-success mb-2">
                                    <i class="fas fa-check-circle"></i> Your vendor account is approved!
                                </h5>
                                <p class="mb-0">You have full access to all vendor features. Start adding products and managing orders.</p>
                            @elseif($isPending)
                                <h5 class="text-warning mb-2">
                                    <i class="fas fa-clock"></i> Your vendor account is pending approval
                                </h5>
                                <p class="mb-0">Our team is reviewing your application. You'll be notified once it's approved.</p>
                            @elseif($isRejected)
                                <h5 class="text-danger mb-2">
                                    <i class="fas fa-times-circle"></i> Your vendor account has been rejected
                                </h5>
                                <p class="mb-0">Please contact support for more information or to reapply.</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $isApproved ? 'success' : ($isRejected ? 'danger' : 'warning') }} p-3 fs-5">
                                {{ ucfirst($vendor->status ?? 'Unknown') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection