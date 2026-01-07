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
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-phone text-primary"></i> Phone:</strong>
                            <p class="mb-0 ms-4">{{ $vendor['user']['phone'] ?? 'Not provided' }}</p>
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
                        <div class="col-md-12 mb-3">
                            <strong><i class="fas fa-map-marker-alt text-primary"></i> Address:</strong>
                            <p class="mb-0 ms-4">{{ $vendor['address'] ?? 'Not provided' }}</p>
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
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-box text-primary"></i> Total Products:</span>
                            <strong>{{ $stats['total_products'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-shopping-bag text-success"></i> Total Orders:</span>
                            <strong>{{ $stats['total_orders'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-dollar-sign text-info"></i> Total Revenue:</span>
                            <strong>${{ number_format($stats['total_revenue'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-clock text-warning"></i> Pending Orders:</span>
                            <strong>{{ $stats['pending_orders'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-check-circle text-success"></i> Completed:</span>
                            <strong>{{ $stats['completed_orders'] }}</strong>
                        </div>
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
                    @if(count($products) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    <tr>
                                        <td>
                                            @if(!empty($product['images']) && count($product['images']) > 0)
                                                <img src="{{ asset('storage/' . $product['images'][0]['image_path']) }}" 
                                                     alt="{{ $product['name'] }}" 
                                                     style="width: 50px; height: 50px; object-fit: cover;" 
                                                     class="rounded">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $product['name'] }}</strong><br>
                                            <small class="text-muted">{{ Str::limit($product['description'], 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $product['category']['name'] ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($product['has_offer'] && isset($product['final_price']) && $product['final_price'] < $product['price'])
                                                <span class="text-muted text-decoration-line-through">${{ number_format($product['price'], 2) }}</span><br>
                                                <strong class="text-danger">${{ number_format($product['final_price'], 2) }}</strong>
                                            @else
                                                <strong>${{ number_format($product['price'], 2) }}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product['stock'] > 0)
                                                <span class="badge bg-success">{{ $product['stock'] }}</span>
                                            @else
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product['avg_rating'] > 0)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-star"></i> {{ number_format($product['avg_rating'], 1) }}
                                                </span>
                                                <small class="text-muted">({{ $product['review_count'] ?? 0 }})</small>
                                            @else
                                                <small class="text-muted">No reviews</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product['stock'] > 0)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">This vendor hasn't added any products yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection