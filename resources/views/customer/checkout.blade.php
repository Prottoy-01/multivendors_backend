@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-credit-card"></i> Checkout</h2>
            <p class="text-muted">Complete your order</p>
        </div>
    </div>

    <form action="{{ route('customer.checkout.place') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Shipping & Payment -->
            <div class="col-md-8">
                <!-- Shipping Address -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        @if(count($addresses) > 0)
                            @foreach($addresses as $address)
                            <div class="form-check mb-3 p-3 border rounded {{ $loop->first ? 'border-primary' : '' }}">
                                <input class="form-check-input" type="radio" name="address_id" 
                                       id="address{{ $address['id'] }}" value="{{ $address['id'] }}" 
                                       {{ $loop->first ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="address{{ $address['id'] }}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong class="text-primary">{{ $address['recipient_name'] }}</strong>
                                            @if($address['is_default'])
                                                <span class="badge bg-success ms-2">Default</span>
                                            @endif
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <p class="mb-0">
                                        <i class="fas fa-phone text-primary"></i> 
                                        <strong>Phone:</strong> {{ $address['phone'] }}
                                    </p>
                                    <p class="mb-0 mt-1">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        {{ $address['address_line'] }}<br>
                                        <span class="ms-4">{{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}</span><br>
                                        <span class="ms-4">{{ $address['country'] }}</span>
                                    </p>
                                </label>
                            </div>
                            @endforeach
                            <a href="{{ route('customer.addresses') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New Address
                            </a>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                You don't have any saved addresses. 
                                <a href="{{ route('customer.addresses') }}" class="alert-link">Add an address first.</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3 p-3 border rounded border-primary">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="cod" value="cash_on_delivery" checked required>
                            <label class="form-check-label w-100" for="cod">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                    <div>
                                        <strong>Cash on Delivery</strong>
                                        <br>
                                        <small class="text-muted">Pay when you receive your order</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="card" value="card">
                            <label class="form-check-label w-100" for="card">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                    <div>
                                        <strong>Credit/Debit Card</strong>
                                        <br>
                                        <small class="text-muted">Visa, MasterCard, American Express</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="bank" value="bank_transfer">
                            <label class="form-check-label w-100" for="bank">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-university fa-2x text-info me-3"></i>
                                    <div>
                                        <strong>Bank Transfer</strong>
                                        <br>
                                        <small class="text-muted">Direct bank account transfer</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Any special instructions for your order..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="card cart-summary sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Order Items ({{ count($cart['items']) }})</h6>
                        
                        <div style="max-height: 300px; overflow-y: auto;">
                            @foreach($cart['items'] as $item)
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                    <img src="{{ $item['product']['image_urls'][0] }}" 
                                         alt="{{ $item['product']['name'] }}" 
                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                         class="rounded me-2">
                                @endif
                                <div class="flex-grow-1">
                                    <small class="d-block">{{ Str::limit($item['product']['name'], 30) }}</small>
                                    <small class="text-muted">{{ $item['quantity'] }} × ${{ number_format($item['final_price'], 2) }}</small>
                                </div>
                                <strong class="text-end">${{ number_format($item['quantity'] * $item['final_price'], 2) }}</strong>
                            </div>
                            @endforeach
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>${{ number_format($cart['subtotal'], 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <strong>${{ number_format($cart['tax'], 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong class="text-success">FREE</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">Total:</strong>
                            <h4 class="text-success mb-0">${{ number_format($cart['total'], 2) }}</h4>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Place Order
                            </button>
                            <a href="{{ route('customer.cart') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Cart
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock"></i> Secure checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection