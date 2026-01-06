@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
            <p class="text-muted">Review items in your cart</p>
        </div>
    </div>

    @if(isset($cart['items']) && count($cart['items']) > 0)
    <div class="row">
        <!-- Cart Items -->
        <div class="col-md-8">
            @foreach($cart['items'] as $item)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                <img src="{{ $item['product']['image_urls'][0] }}" alt="{{ $item['product']['name'] }}" 
                                     class="img-fluid" style="max-height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h5>{{ $item['product']['name'] }}</h5>
                            <p class="text-muted mb-0">{{ Str::limit($item['product']['description'], 50) }}</p>
                            @if(isset($item['product']['vendor']))
                                <small class="text-muted">Sold by: {{ $item['product']['vendor']['shop_name'] }}</small>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <strong>${{ number_format($item['price'], 2) }}</strong>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                                <button class="btn btn-outline-secondary btn-sm" type="button" 
                                        onclick="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control form-control-sm text-center" 
                                       value="{{ $item['quantity'] }}" min="1" readonly style="max-width: 60px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <strong>${{ number_format($item['quantity'] * $item['price'], 2) }}</strong>
                            <br>
                            <form action="{{ route('customer.cart.remove', $item['id']) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mt-2" 
                                        onclick="return confirm('Remove this item?')">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="text-start">
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="col-md-4">
            <div class="card cart-summary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>${{ number_format($cart['subtotal'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (10%):</span>
                        <strong>${{ number_format($cart['tax'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <h4 class="text-primary mb-0">${{ number_format($cart['total'], 2) }}</h4>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('customer.checkout') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h3>Your Cart is Empty</h3>
                    <p class="text-muted">Add some products to your cart to see them here!</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function updateQuantity(itemId, newQuantity) {
    if (newQuantity < 1) return;
    
    fetch(`/customer/cart/update/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ quantity: newQuantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endpush
@endsection