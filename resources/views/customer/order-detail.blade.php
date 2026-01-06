@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <h2><i class="fas fa-receipt"></i> Order #{{ $order['id'] }}</h2>
            <p class="text-muted">Order placed on {{ date('F d, Y', strtotime($order['created_at'])) }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Order Items -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    @foreach($order['items'] as $item)
                    <div class="row align-items-center mb-3 pb-3 border-bottom">
                        <div class="col-md-2">
                            @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                <img src="{{ $item['product']['image_urls'][0] }}" alt="{{ $item['product']['name'] }}" 
                                     class="img-fluid rounded">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 80px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>{{ $item['product']['name'] }}</h5>
                            <p class="text-muted mb-0">{{ Str::limit($item['product']['description'], 80) }}</p>
                            @if(isset($item['product']['vendor']))
                                <small class="text-muted">Sold by: {{ $item['product']['vendor']['shop_name'] }}</small>
                            @endif
                        </div>
                        <div class="col-md-2 text-center">
                            <strong>Qty: {{ $item['quantity'] }}</strong>
                        </div>
                        <div class="col-md-2 text-end">
                            <strong>${{ number_format($item['subtotal'], 2) }}</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        {{ $order['shipping_address'] }}<br>
                        {{ $order['shipping_city'] }}, {{ $order['shipping_state'] }} {{ $order['shipping_postal_code'] }}<br>
                        {{ $order['shipping_country'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>${{ number_format($order['subtotal'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <strong>${{ number_format($order['tax'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <strong class="text-success">FREE</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <h4 class="text-success mb-0">${{ number_format($order['total_amount'], 2) }}</h4>
                    </div>
                </div>
            </div>

            <!-- Order Status -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        @if($order['status'] === 'delivered')
                            <span class="badge bg-success p-3 fs-6">Delivered</span>
                        @elseif($order['status'] === 'shipped')
                            <span class="badge bg-info p-3 fs-6">Shipped</span>
                        @elseif($order['status'] === 'processing')
                            <span class="badge bg-primary p-3 fs-6">Processing</span>
                        @elseif($order['status'] === 'cancelled')
                            <span class="badge bg-danger p-3 fs-6">Cancelled</span>
                        @else
                            <span class="badge bg-secondary p-3 fs-6">Pending</span>
                        @endif
                    </div>

                    <p class="mb-2"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order['payment_method'])) }}</p>
                    <p class="mb-2">
                        <strong>Payment Status:</strong>
                        @if($order['payment_status'] === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </p>
                    
                    @if($order['notes'])
                        <hr>
                        <p class="mb-0"><strong>Order Notes:</strong><br>{{ $order['notes'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection