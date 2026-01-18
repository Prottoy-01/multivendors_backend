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

    {{-- ✅ COD Form (Original) --}}
    <form id="cod-form" action="{{ route('customer.checkout.place') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="address_id" id="cod-address-id">
        <input type="hidden" name="payment_method" value="cash_on_delivery">
        <input type="hidden" name="notes" id="cod-notes">
    </form>

    {{-- ✅ Stripe Checkout Form (New) --}}
    <form id="stripe-form" action="{{ route('payment.stripe.checkout') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="address_id" id="stripe-address-id">
        <input type="hidden" name="notes" id="stripe-notes">
    </form>

    {{-- ✅ Main Form (UI Only) --}}
    <form id="checkout-form">
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
                            <div class="form-check mb-3 p-3 border rounded address-option" id="address-option-{{ $address['id'] }}">
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
                        <div class="form-check mb-3 p-3 border rounded payment-option" id="payment-cod">
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
                        
                        <div class="form-check mb-3 p-3 border rounded payment-option" id="payment-card">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="card" value="card">
                            <label class="form-check-label w-100" for="card">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                    <div>
                                        <strong>Credit/Debit Card</strong>
                                        <br>
                                        <small class="text-muted">Visa, Mastercard, American Express - Powered by Stripe</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                       
                    </div>
                </div>

                {{-- ✅✅✅ UPDATED: COUPON CODE SECTION WITH CATEGORY SUPPORT ✅✅✅ --}}
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-ticket-alt"></i> Have a Coupon Code?</h5>
                    </div>
                    <div class="card-body">
                        <div id="coupon-section">
                            @if(isset($appliedCoupon) && $appliedCoupon)
                                {{-- Applied Coupon Display --}}
                                <div class="alert alert-success d-flex justify-content-between align-items-center" id="applied-coupon-display">
                                    <div>
                                        <i class="fas fa-check-circle"></i>
                                        <strong>{{ $appliedCoupon['code'] }}</strong> applied!
                                        <br>
                                        <small>
                                            You're saving ${{ number_format($appliedCoupon['discount'], 2) }}
                                            @if(!empty($appliedCoupon['applicable_items_count']))
                                                ({{ $appliedCoupon['applicable_items_count'] }} item(s))
                                            @endif
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCoupon()">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            @else
                                {{-- Coupon Input Form --}}
                                <div id="coupon-input-form">
                                    <div class="input-group mb-3">
                                        <input type="text" 
                                               class="form-control" 
                                               id="coupon-code-input" 
                                               placeholder="Enter coupon code" 
                                               style="text-transform: uppercase;">
                                        <button type="button" 
                                                class="btn btn-warning" 
                                                onclick="applyCoupon()">
                                            <i class="fas fa-tag"></i> Apply
                                        </button>
                                    </div>
                                    <div id="coupon-message"></div>
                                    
                                    {{-- ✅ UPDATED: Show Available Coupons from Controller --}}
                                    @if(!empty($availableCoupons) && count($availableCoupons) > 0)
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-gift"></i> <strong>Available coupons for your cart:</strong>
                                        </small>
                                        <div class="mt-2">
                                            @foreach($availableCoupons as $coupon)
                                                <div class="badge-coupon-container mb-2">
                                                    <span class="badge bg-warning text-dark me-2" 
                                                          style="cursor: pointer; padding: 8px 12px; font-size: 0.9rem;" 
                                                          onclick="document.getElementById('coupon-code-input').value = '{{ $coupon['code'] }}'; applyCoupon();"
                                                          title="Click to apply">
                                                        <i class="fas fa-tag"></i> {{ $coupon['code'] }} 
                                                        @if($coupon['type'] === 'percentage')
                                                            ({{ $coupon['value'] }}% OFF)
                                                        @else
                                                            (${{ $coupon['value'] }} OFF)
                                                        @endif
                                                    </span>
                                                    
                                                    {{-- Show applicable categories --}}
                                                    @if(!$coupon['applies_to_all'] && !empty($coupon['categories']))
                                                        <br>
                                                        <small class="text-muted ms-2">
                                                            <i class="fas fa-arrow-right"></i> Applies to: 
                                                            @foreach($coupon['categories'] as $category)
                                                                <span class="badge bg-secondary">{{ $category }}</span>
                                                            @endforeach
                                                        </small>
                                                    @elseif($coupon['applies_to_all'])
                                                        <br>
                                                        <small class="text-success ms-2">
                                                            <i class="fas fa-check"></i> All products
                                                        </small>
                                                    @endif
                                                    
                                                    {{-- Show min purchase if exists --}}
                                                    @if($coupon['min_purchase'])
                                                        <br>
                                                        <small class="text-muted ms-2">
                                                            <i class="fas fa-shopping-cart"></i> Min: ${{ number_format($coupon['min_purchase'], 2) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> No coupons available for items in your cart.
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- ✅✅✅ END: UPDATED COUPON CODE SECTION ✅✅✅ --}}

                <!-- Order Notes -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="notes" id="order-notes" rows="3" 
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
                        
                        {{-- ✅✅✅ YOUR ORDER SUMMARY (PRESERVED) ✅✅✅ --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="subtotal-amount">${{ number_format($cart['subtotal'], 2) }}</strong>
                        </div>
                        
                        @if(isset($cart['coupon_discount']) && $cart['coupon_discount'] > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span><i class="fas fa-tag"></i> Coupon Discount:</span>
                            <strong id="coupon-discount-amount">-${{ number_format($cart['coupon_discount'], 2) }}</strong>
                        </div>
                        @endif
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <strong id="tax-amount">${{ number_format($cart['tax'], 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong class="text-success">FREE</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">Total:</strong>
                            <h4 class="text-success mb-0" id="total-amount">${{ number_format($cart['total'], 2) }}</h4>
                        </div>
                        {{-- ✅✅✅ END: YOUR ORDER SUMMARY ✅✅✅ --}}
                        
                        <div class="d-grid gap-2">
                            <button type="button" id="place-order-btn" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> <span id="btn-text">Place Order</span>
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

{{-- ✅✅✅ COMBINED JAVASCRIPT: YOUR COUPON CODE + STRIPE PAYMENT ✅✅✅ --}}
<script>
// ==========================================
// YOUR ORIGINAL COUPON FUNCTIONS (PRESERVED)
// ==========================================
function applyCoupon() {
    const couponCode = document.getElementById('coupon-code-input').value.trim();
    const messageDiv = document.getElementById('coupon-message');
    
    if (!couponCode) {
        messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> Please enter a coupon code<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        return;
    }
    
    // Show loading
    messageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Validating coupon...</div>';
    
    fetch('{{ route('customer.coupon.apply') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ coupon_code: couponCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            messageDiv.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
            
            // Reload page to show updated totals
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle"></i> ${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> Error applying coupon. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    });
}

function removeCoupon() {
    if (!confirm('Are you sure you want to remove this coupon?')) {
        return;
    }
    
    fetch('{{ route('customer.coupon.remove') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing coupon. Please try again.');
    });
}

// Allow Enter key to apply coupon
document.getElementById('coupon-code-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        applyCoupon();
    }
});

// ==========================================
// NEW: STRIPE PAYMENT INTEGRATION
// ==========================================

// ==========================================
// DYNAMIC BORDER STYLING FOR SELECTIONS
// ==========================================

// Function to update payment method borders
function updatePaymentBorders() {
    // Remove border-primary from all payment options
    document.querySelectorAll('.payment-option').forEach(function(el) {
        el.classList.remove('border-primary');
    });
    
    // Add border-primary to selected payment option
    const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
    if (selectedPayment) {
        const parentDiv = selectedPayment.closest('.payment-option');
        if (parentDiv) {
            parentDiv.classList.add('border-primary');
        }
    }
}

// Function to update address borders
function updateAddressBorders() {
    // Remove border-primary from all address options
    document.querySelectorAll('.address-option').forEach(function(el) {
        el.classList.remove('border-primary');
    });
    
    // Add border-primary to selected address option
    const selectedAddress = document.querySelector('input[name="address_id"]:checked');
    if (selectedAddress) {
        const parentDiv = selectedAddress.closest('.address-option');
        if (parentDiv) {
            parentDiv.classList.add('border-primary');
        }
    }
}

// Initialize borders on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentBorders();
    updateAddressBorders();
});

// Update payment method button text and borders on change
document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const btnText = document.getElementById('btn-text');
        
        if (this.value === 'card') {
            btnText.textContent = 'Pay with Card';
        } else {
            btnText.textContent = 'Place Order';
        }
        
        // Update borders when payment method changes
        updatePaymentBorders();
    });
});

// Update address borders on change
document.querySelectorAll('input[name="address_id"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        updateAddressBorders();
    });
});

// Handle Place Order button click
document.getElementById('place-order-btn').addEventListener('click', function() {
    const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const addressId = document.querySelector('input[name="address_id"]:checked')?.value;
    const notes = document.getElementById('order-notes').value;
    
    if (!addressId) {
        alert('Please select a shipping address');
        return;
    }
    
    // Disable button during processing
    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    if (selectedPaymentMethod === 'card') {
        // Stripe Checkout - Redirect
        document.getElementById('stripe-address-id').value = addressId;
        document.getElementById('stripe-notes').value = notes;
        document.getElementById('stripe-form').submit();
        
    } else {
        // Cash on Delivery - Normal submission
        document.getElementById('cod-address-id').value = addressId;
        document.getElementById('cod-notes').value = notes;
        document.getElementById('cod-form').submit();
    }
});
</script>

<style>
.badge-coupon-container {
    display: inline-block;
    vertical-align: top;
}

.badge-coupon-container .badge {
    transition: all 0.2s ease;
}

.badge-coupon-container .badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
</style>
@endsection