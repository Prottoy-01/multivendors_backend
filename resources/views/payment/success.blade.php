@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                        <p class="text-muted">Your order has been placed successfully and payment has been received.</p>
                    </div>
                    
                    <hr>
                    
                    {{-- Order Details --}}
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Order Date:</strong></td>
                                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Order Status:</strong></td>
                                    <td>
                                        <span class="badge bg-warning">{{ ucfirst($order->status) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Payment Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>
                                        <i class="fas fa-credit-card text-primary"></i>
                                        {{ ucfirst($order->payment_method) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Status:</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Transaction ID:</strong></td>
                                    <td>
                                        <small class="font-monospace">{{ Str::limit($order->transaction_id, 20) }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    
                    {{-- Order Items --}}
                    <h6 class="text-muted mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
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
                                                     style="width: 50px; height: 50px; object-fit: cover;" 
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
                                    <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                    <td class="text-end"><strong>${{ number_format($item->total, 2) }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Order Totals --}}
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm">
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
                                    <td><strong>Grand Total:</strong></td>
                                    <td class="text-end">
                                        <h5 class="text-success mb-0">
                                            ${{ number_format($order->grand_total, 2) }}
                                        </h5>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    
                    {{-- Shipping Address --}}
                    @if($order->shippingAddress)
                    <h6 class="text-muted mb-3">Shipping Address</h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-1"><strong>{{ $order->shippingAddress->recipient_name }}</strong></p>
                        <p class="mb-1">{{ $order->shippingAddress->phone }}</p>
                        <p class="mb-0">
                            {{ $order->shippingAddress->address_line }}<br>
                            {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}<br>
                            {{ $order->shippingAddress->country }}
                        </p>
                    </div>
                    @endif
                    
                    {{-- Action Buttons --}}
                    <div class="text-center mt-4">
                        <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-primary btn-lg me-2">
                            <i class="fas fa-eye"></i> View Order Details
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
                            <li>You will receive an email confirmation shortly</li>
                            <li>Track your order status in "My Orders"</li>
                            <li>The vendor will process your order soon</li>
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