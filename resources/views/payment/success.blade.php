@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            {{-- Success Card --}}
            <div class="card border-success">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <br>
                        Payment Successful!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5 class="text-success">Thank you for your purchase!</h5>
                        <p class="text-muted">Your payment has been received and your orders have been placed successfully.</p>
                    </div>
                    
                    <hr>
                    
                    {{-- Payment Summary --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Payment Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>
                                        <i class="fas fa-credit-card text-primary"></i>
                                        Card
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Status:</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Transaction ID:</strong></td>
                                    <td>
                                        <small class="font-monospace">{{ Str::limit($transactionId, 20) }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Number of Orders:</strong></td>
                                    <td>{{ $orders->count() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Order Date:</strong></td>
                                    <td>{{ $orders->first()->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Paid:</strong></td>
                                    <td>
                                        <h5 class="text-success mb-0">
                                            ${{ number_format($totalAmount, 2) }}
                                        </h5>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    
                    {{-- Multi-Vendor Orders --}}
                    <h5 class="mb-4">
                        <i class="fas fa-box"></i> Your Orders
                        @if($orders->count() > 1)
                            <span class="badge bg-info">{{ $orders->count() }} Vendors</span>
                        @endif
                    </h5>
                    
                    @foreach($orders as $index => $order)
                    <div class="card mb-3 {{ $index > 0 ? 'border-secondary' : 'border-primary' }}">
                        <div class="card-header {{ $index > 0 ? 'bg-light' : 'bg-primary text-white' }}">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">
                                        <i class="fas fa-store"></i>
                                        @if($order->vendor)
                                            {{ $order->vendor->business_name }}
                                        @else
                                            Vendor #{{ $order->vendor_id }}
                                        @endif
                                    </h6>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <strong>Order #{{ $order->order_number }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Order Items --}}
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product && $item->product->image_urls)
                                                        <img src="{{ $item->product->image_urls[0] ?? '' }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             style="width: 40px; height: 40px; object-fit: cover;" 
                                                             class="rounded me-2">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $item->product->name ?? 'Product' }}</strong>
                                                        @if($item->variant)
                                                            <br>
                                                            <small class="text-muted">{{ $item->variant->name }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">${{ number_format($item->final_price, 2) }}</td>
                                            <td class="text-end"><strong>${{ number_format($item->quantity * $item->final_price, 2) }}</strong></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Order Totals --}}
                            <div class="row justify-content-end">
                                <div class="col-md-4">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-end">${{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                        @if($order->coupon_discount > 0)
                                        <tr class="text-success">
                                            <td><i class="fas fa-tag"></i> Discount:</td>
                                            <td class="text-end">-${{ number_format($order->coupon_discount, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td>Tax:</td>
                                            <td class="text-end">${{ number_format($order->tax_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Shipping:</td>
                                            <td class="text-end text-success">FREE</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td><strong>Order Total:</strong></td>
                                            <td class="text-end">
                                                <strong class="text-primary">${{ number_format($order->grand_total, 2) }}</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <hr>
                    
                    {{-- Shipping Address --}}
                    <h6 class="text-muted mb-3">Shipping Address</h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-1"><strong>{{ $shippingAddress['recipient_name'] }}</strong></p>
                        <p class="mb-1">{{ $shippingAddress['phone'] }}</p>
                        <p class="mb-0">
                            {{ $shippingAddress['address_line'] }}<br>
                            {{ $shippingAddress['city'] }}@if($shippingAddress['state']), {{ $shippingAddress['state'] }}@endif @if($shippingAddress['postal_code']) {{ $shippingAddress['postal_code'] }}@endif<br>
                            {{ $shippingAddress['country'] }}
                        </p>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="text-center mt-4">
                        <a href="{{ route('customer.orders') }}" class="btn btn-primary btn-lg me-2">
                            <i class="fas fa-list"></i> View All My Orders
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                    
                    {{-- Confirmation Message --}}
                    <div class="alert alert-info mt-4 mb-0">
                        <i class="fas fa-info-circle"></i>
                        <strong>What's Next?</strong>
                        <ul class="mb-0 mt-2">
                            <li>You will receive email confirmations for each order</li>
                            <li>Track your order status in "My Orders"</li>
                            @if($orders->count() > 1)
                            <li>Each vendor will process their portion of your order</li>
                            <li>Orders from different vendors may arrive separately</li>
                            @else
                            <li>The vendor will process your order soon</li>
                            @endif
                            <li>Estimated delivery: 3-7 business days</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- Additional Info --}}
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-headset"></i> Need help? 
                    <a href="#">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection