@extends('layouts.app')

@section('title', 'Manage Orders')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-shopping-cart"></i> Orders Management</h2>
            <p class="text-muted">View and track all orders</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Orders</h5>
            <span class="badge bg-light text-dark">Total: {{ count($orders) }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Vendor</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong>#{{ $order['id'] }}</strong></td>
                            <td>
                                <i class="fas fa-user"></i> 
                                {{ $order['user']['name'] ?? 'N/A' }}
                                <br><small class="text-muted">{{ $order['user']['email'] ?? '' }}</small>
                            </td>
                            <td>
                                @if(isset($order['vendor']))
                                    <i class="fas fa-store"></i> {{ $order['vendor']['shop_name'] }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ date('M d, Y', strtotime($order['created_at'])) }}</td>
                            <td>{{ count($order['items'] ?? []) }} items</td>
                            <td><strong>${{ number_format($order['total_amount'], 2) }}</strong></td>
                            <td>
                                @if($order['payment_status'] === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($order['payment_status'] === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-clock fa-3x text-secondary mb-3"></i>
                    <h3>{{ count(array_filter($orders, fn($o) => $o['status'] === 'pending')) }}</h3>
                    <p class="text-muted">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-cog fa-3x text-primary mb-3"></i>
                    <h3>{{ count(array_filter($orders, fn($o) => $o['status'] === 'processing')) }}</h3>
                    <p class="text-muted">Processing</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-shipping-fast fa-3x text-info mb-3"></i>
                    <h3>{{ count(array_filter($orders, fn($o) => $o['status'] === 'shipped')) }}</h3>
                    <p class="text-muted">Shipped</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h3>{{ count(array_filter($orders, fn($o) => $o['status'] === 'delivered')) }}</h3>
                    <p class="text-muted">Delivered</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection