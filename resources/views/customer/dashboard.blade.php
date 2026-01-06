@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Customer Dashboard</h2>
            <p class="text-muted">Welcome back, {{ Session::get('user')['name'] }}!</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-number">{{ count($orders) }}</div>
                    <p>Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-heart"></i>
                    <div class="stat-number">{{ $wishlistCount }}</div>
                    <p>Wishlist Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="stat-number">{{ $cartCount }}</div>
                    <p>Cart Items</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    @if(count($orders) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($orders, 0, 5) as $order)
                                    <tr>
                                        <td>#{{ $order['id'] }}</td>
                                        <td>{{ date('M d, Y', strtotime($order['created_at'])) }}</td>
                                        <td>${{ number_format($order['total_amount'], 2) }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($order['status']) }}</span></td>
                                        <td>
                                            <a href="{{ route('customer.orders.show', $order['id']) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No orders yet. <a href="{{ route('products.index') }}">Start shopping!</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection