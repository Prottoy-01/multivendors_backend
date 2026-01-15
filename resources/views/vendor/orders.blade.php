@extends('layouts.app')

@section('title', 'Vendor Orders')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-shopping-bag"></i> Order Management</h2>
            <p class="text-muted">Track and manage customer orders</p>
        </div>
    </div>

    <!-- Order Status Tabs -->
    <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                All Orders ({{ count($orders) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                Pending ({{ count(array_filter($orders, fn($o) => $o['status'] === 'pending')) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button">
                Processing ({{ count(array_filter($orders, fn($o) => $o['status'] === 'processing')) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped" type="button">
                Shipped ({{ count(array_filter($orders, fn($o) => $o['status'] === 'shipped')) }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="orderTabsContent">
        <!-- All Orders -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
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
                        <div class="col-md-6">
                            <h6>Customer Information:</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order['user']['name'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order['user']['email'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $order['phone'] ?? 'N/A' }}</p>
                            
                            <h6 class="mt-3">Shipping Address:</h6>
                            <p class="mb-0">
                                <strong>Recipient:</strong> {{ $order['recipient_name'] ?? 'N/A' }}<br>
                                {{ $order['address_line'] ?? 'N/A' }}<br>
                                {{ $order['city'] ?? '' }}@if($order['state'] ?? ''), {{ $order['state'] }}@endif {{ $order['postal_code'] ?? '' }}<br>
                                {{ $order['country'] ?? '' }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6>Order Items:</h6>
                            @foreach($order['items'] as $item)
                            <div class="d-flex align-items-center mb-2">
                                @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                    <img src="{{ $item['product']['image_urls'][0] }}" alt="{{ $item['product']['name'] }}" 
                                         style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                @endif
                                <div>
                                    <strong>{{ $item['product']['name'] }}</strong><br>
                                    <small>Qty: {{ $item['quantity'] }} × ${{ number_format($item['price'], 2) }}</small>
                                </div>
                            </div>
                            @endforeach
                            
                            <hr>
                            <h5 class="text-end">Total: ${{ number_format($order['total_amount'], 2) }}</h5>
                        </div>
                    </div>

                    @if($order['status'] !== 'delivered' && $order['status'] !== 'cancelled')
                    <hr>
                    <div class="text-end">
                        <strong>Update Order Status:</strong>
                        <form action="{{ route('vendor.orders.status', $order['id']) }}" method="POST" class="d-inline-block ms-2">
                            @csrf
                            <div class="btn-group" role="group">
                                @if($order['status'] === 'pending')
                                    <button type="submit" name="status" value="processing" class="btn btn-sm btn-primary">
                                        <i class="fas fa-cog"></i> Mark as Processing
                                    </button>
                                @endif
                                @if($order['status'] === 'processing')
                                    <button type="submit" name="status" value="shipped" class="btn btn-sm btn-info">
                                        <i class="fas fa-shipping-fast"></i> Mark as Shipped
                                    </button>
                                @endif
                                @if($order['status'] === 'shipped')
                                    <button type="submit" name="status" value="delivered" class="btn btn-sm btn-success">
                                        <i class="fas fa-check-circle"></i> Mark as Delivered
                                    </button>
                                @endif
                                <button type="submit" name="status" value="cancelled" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Cancel this order?')">
                                    <i class="fas fa-times-circle"></i> Cancel Order
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                    <h4>No Orders Yet</h4>
                    <p class="text-muted">Orders will appear here when customers purchase your products.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Other tabs would filter by status -->
        <div class="tab-pane fade" id="pending" role="tabpanel">
            @foreach(array_filter($orders, fn($o) => $o['status'] === 'pending') as $order)
                <!-- Same order card template -->
            @endforeach
        </div>
        <!-- Add similar for processing and shipped tabs -->
    </div>
</div>
@endsection