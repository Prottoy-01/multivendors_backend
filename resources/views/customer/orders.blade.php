@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-box"></i> My Orders</h2>
            <p class="text-muted">Track and manage your orders</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Orders ({{ count($orders) }})</h5>
        </div>
        <div class="card-body">
            @forelse($orders as $order)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #{{ $order['id'] }}</strong>
                        <span class="text-muted ms-3">
                            <i class="fas fa-calendar"></i> {{ date('F d, Y', strtotime($order['created_at'])) }}
                        </span>
                    </div>
                    <div>
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
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Order Items:</h6>
                            @foreach($order['items'] as $item)
                            <div class="d-flex align-items-center mb-2">
                                @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                    <img src="{{ $item['product']['image_urls'][0] }}" alt="{{ $item['product']['name'] }}" 
                                         style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                @else
                                    <div style="width: 50px; height: 50px; background: #eee;" class="me-3 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $item['product']['name'] }}</strong>
                                    <br>
                                    <small class="text-muted">Qty: {{ $item['quantity'] }} × ${{ number_format($item['price'], 2) }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="col-md-4 text-end">
                            <h5>Total: ${{ number_format($order['total_amount'], 2) }}</h5>
                            <p class="text-muted mb-2">
                                Payment: 
                                @if($order['payment_status'] === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </p>
                            <a href="{{ route('customer.orders.show', $order['id']) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4>No Orders Yet</h4>
                <p class="text-muted">Start shopping to see your orders here!</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Browse Products
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection