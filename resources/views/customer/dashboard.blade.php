@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-tachometer-alt"></i> My Dashboard</h2>
            <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Orders</h6>
                            <h2 class="mb-0">{{ count($orders) }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-shopping-bag fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('customer.orders') }}" class="btn btn-light btn-sm mt-3">View Orders</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Wishlist Items</h6>
                            <h2 class="mb-0">{{ $wishlistCount }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-heart fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('customer.wishlist') }}" class="btn btn-light btn-sm mt-3">View Wishlist</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Cart Items</h6>
                            <h2 class="mb-0">{{ $cartCount }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <a href="{{ route('customer.cart') }}" class="btn btn-light btn-sm mt-3">View Cart</a>
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
                            <a href="{{ route('products.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-shopping-bag fa-2x d-block mb-2"></i>
                                Continue Shopping
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('customer.orders') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-box fa-2x d-block mb-2"></i>
                                My Orders
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('customer.wishlist') }}" class="btn btn-outline-danger w-100 py-3">
                                <i class="fas fa-heart fa-2x d-block mb-2"></i>
                                My Wishlist
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('customer.profile') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-user fa-2x d-block mb-2"></i>
                                My Profile
                            </a>
                        </div>
                    </div>
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
                    @if(count($orders) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($orders, 0, 5) as $order)
                                    <tr>
                                        <td><strong>#{{ $order['id'] }}</strong></td>
                                        <td>{{ date('M d, Y', strtotime($order['created_at'])) }}</td>
                                        <td>{{ count($order['items']) }} items</td>
                                        <td><strong>${{ number_format($order['grand_total'], 2) }}</strong></td>
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
                                            <a href="{{ route('customer.orders.show', $order['id']) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($orders) > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('customer.orders') }}" class="btn btn-outline-primary">
                                    View All Orders <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No orders yet. Start shopping to see your orders here!</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Browse Products
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection