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
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="address_id" 
                                       id="address{{ $address['id'] }}" value="{{ $address['id'] }}" 
                                       {{ $loop->first ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="address{{ $address['id'] }}">
                                    <strong>{{ $address['label'] }}</strong>
                                    <br>
                                    {{ $address['address_line_1'] }}
                                    @if($address['address_line_2']), {{ $address['address_line_2'] }}@endif
                                    <br>
                                    {{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}
                                    <br>
                                    {{ $address['country'] }}
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
                                <a href="{{ route('customer.addresses') }}">Add an address first.</a>
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
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="cod" value="cash_on_delivery" checked required>
                            <label class="form-check-label" for="cod">
                                <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="card" value="card">
                            <label class="form-check-label" for="card">
                                <i class="fas fa-credit-card"></i> Credit/Debit Card
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="bank" value="bank_transfer">
                            <label class="form-check-label" for="bank">
                                <i class="fas fa-university"></i> Bank Transfer
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
                <div class="card cart-summary">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Order Items ({{ count($cart['items']) }})</h6>
                        @foreach($cart['items'] as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <small>{{ Str::limit($item['product']['name'], 20) }} × {{ $item['quantity'] }}</small>
                            <small>${{ number_format($item['quantity'] * $item['price'], 2) }}</small>
                        </div>
                        @endforeach
                        
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
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <h4 class="text-success mb-0">${{ number_format($cart['total'], 2) }}</h4>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Place Order
                            </button>
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