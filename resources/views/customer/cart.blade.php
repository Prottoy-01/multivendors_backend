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
            <div class="card mb-3" id="cart-item-{{ $item['id'] }}">
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
                                <button class="btn btn-outline-secondary btn-sm quantity-decrease" 
                                        type="button" 
                                        data-item-id="{{ $item['id'] }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       class="form-control form-control-sm text-center" 
                                       value="{{ $item['quantity'] }}" 
                                       min="1" 
                                       readonly 
                                       style="max-width: 60px;"
                                       id="quantity-{{ $item['id'] }}"
                                       data-price="{{ $item['final_price'] }}"
                                       data-max-stock="{{ $item['variant'] ? $item['variant']['stock'] : $item['product']['stock'] }}">
                                <button class="btn btn-outline-secondary btn-sm quantity-increase" 
                                        type="button"
                                        data-item-id="{{ $item['id'] }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <strong id="subtotal-{{ $item['id'] }}">${{ number_format($item['quantity'] * $item['price'], 2) }}</strong>
                            <br>
                            <button type="button" 
                                    class="btn btn-sm btn-danger mt-2" 
                                    onclick="removeCartItem({{ $item['id'] }})"
                                    id="remove-btn-{{ $item['id'] }}">
                                <i class="fas fa-trash"></i> Remove
                            </button>
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
                        <strong id="cart-subtotal">${{ number_format($cart['subtotal'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (10%):</span>
                        <strong id="cart-tax">${{ number_format($cart['tax'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <h4 class="text-primary mb-0" id="cart-total">${{ number_format($cart['total'], 2) }}</h4>
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
// Update quantity function
// Debounce timer for quantity updates
let quantityUpdateTimers = {};

// Attach event listeners to + and - buttons
document.addEventListener('DOMContentLoaded', function() {
    // Update button states on page load
    document.querySelectorAll('[id^="quantity-"]').forEach(input => {
        const itemId = input.id.replace('quantity-', '');
        updateButtonStates(itemId);
    });
    
    // Handle decrease buttons
    document.querySelectorAll('.quantity-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const input = document.getElementById(`quantity-${itemId}`);
            const currentQty = parseInt(input.value) || 1;
            const newQty = Math.max(1, currentQty - 1);
            updateQuantity(itemId, newQty);
        });
    });
    
    // Handle increase buttons
    document.querySelectorAll('.quantity-increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const input = document.getElementById(`quantity-${itemId}`);
            const currentQty = parseInt(input.value) || 1;
            const maxStock = parseInt(input.dataset.maxStock) || 999;
            const newQty = currentQty + 1;
            
            // Check stock limit
            if (newQty > maxStock) {
                showNotification('error', `Cannot add more. Only ${maxStock} available in stock.`);
                return;
            }
            
            updateQuantity(itemId, newQty);
        });
    });
});

function updateQuantity(itemId, newQuantity) {
    if (newQuantity < 1) return;
    
    // Optimistically update the UI immediately
    const quantityInput = document.getElementById(`quantity-${itemId}`);
    if (quantityInput) {
        quantityInput.value = newQuantity;
        
        // Get price from data attribute
        const price = parseFloat(quantityInput.dataset.price) || 0;
        
        // Update item subtotal immediately
        const subtotalElement = document.getElementById(`subtotal-${itemId}`);
        if (subtotalElement) {
            subtotalElement.textContent = `$${(newQuantity * price).toFixed(2)}`;
        }
        
        // Calculate and update cart totals optimistically
        recalculateCartTotalsOptimistic();
        
        // Update button states (disable + if at max, etc.)
        updateButtonStates(itemId);
    }
    
    // Clear any existing timer for this item
    if (quantityUpdateTimers[itemId]) {
        clearTimeout(quantityUpdateTimers[itemId]);
    }
    
    // Set a debounce timer - only send request after 500ms of no clicks
    quantityUpdateTimers[itemId] = setTimeout(() => {
        fetch(`/customer/cart/update/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: newQuantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update with server values (in case they differ)
                document.getElementById(`quantity-${itemId}`).value = newQuantity;
                document.getElementById(`subtotal-${itemId}`).textContent = 
                    `$${(newQuantity * data.item.price).toFixed(2)}`;
                
                // Update cart totals with server values
                updateCartTotals(data.cart);
                
                // Update button states again with confirmed values
                updateButtonStates(itemId);
            } else {
                // Revert on error
                showNotification('error', data.message || 'Failed to update cart');
                // Could reload page or fetch fresh data here
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'An error occurred');
        });
    }, 500); // Wait 500ms after last click before sending request
}

// Optimistically recalculate cart totals from current DOM values
function recalculateCartTotalsOptimistic() {
    let subtotal = 0;
    
    // Sum up all item subtotals
    document.querySelectorAll('[id^="quantity-"]').forEach(input => {
        const quantity = parseInt(input.value) || 0;
        const price = parseFloat(input.dataset.price) || 0;
        subtotal += quantity * price;
    });
    
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    // Update display
    const subtotalEl = document.getElementById('cart-subtotal');
    const taxEl = document.getElementById('cart-tax');
    const totalEl = document.getElementById('cart-total');
    
    if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
    if (taxEl) taxEl.textContent = `$${tax.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
}

// Update button states based on quantity and stock
function updateButtonStates(itemId) {
    const input = document.getElementById(`quantity-${itemId}`);
    if (!input) return;
    
    const currentQty = parseInt(input.value) || 1;
    const maxStock = parseInt(input.dataset.maxStock) || 999;
    
    // Find the buttons for this item
    const decreaseBtn = document.querySelector(`.quantity-decrease[data-item-id="${itemId}"]`);
    const increaseBtn = document.querySelector(`.quantity-increase[data-item-id="${itemId}"]`);
    
    // Disable decrease if at minimum (1)
    if (decreaseBtn) {
        decreaseBtn.disabled = currentQty <= 1;
        if (currentQty <= 1) {
            decreaseBtn.classList.add('opacity-50');
        } else {
            decreaseBtn.classList.remove('opacity-50');
        }
    }
    
    // Disable increase if at max stock
    if (increaseBtn) {
        increaseBtn.disabled = currentQty >= maxStock;
        if (currentQty >= maxStock) {
            increaseBtn.classList.add('opacity-50');
            increaseBtn.title = `Maximum stock available: ${maxStock}`;
        } else {
            increaseBtn.classList.remove('opacity-50');
            increaseBtn.title = '';
        }
    }
}

// Remove item from cart
function removeCartItem(itemId) {
    if (!confirm('Remove this item from cart?')) return;
    
    const btn = document.getElementById(`remove-btn-${itemId}`);
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`/customer/cart/remove/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fade out and remove item
            const itemCard = document.getElementById(`cart-item-${itemId}`);
            itemCard.style.transition = 'opacity 0.3s';
            itemCard.style.opacity = '0';
            
            setTimeout(() => {
                itemCard.remove();
                
                // Update cart totals
                updateCartTotals(data.cart);
                
                // Check if cart is empty
                if (data.cart.items_count === 0) {
                    showEmptyCart();
                }
            }, 300);
            
            showNotification('success', 'Item removed from cart');
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showNotification('error', data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        showNotification('error', 'An error occurred');
    });
}

// Update cart totals
function updateCartTotals(cart) {
    document.getElementById('cart-subtotal').textContent = `$${cart.subtotal.toFixed(2)}`;
    document.getElementById('cart-tax').textContent = `$${cart.tax.toFixed(2)}`;
    document.getElementById('cart-total').textContent = `$${cart.total.toFixed(2)}`;
}

// Show empty cart message
function showEmptyCart() {
    const container = document.querySelector('.container > .row:last-of-type');
    container.innerHTML = `
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h3>Your Cart is Empty</h3>
                    <p class="text-muted">Add some products to your cart to get started!</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
            </div>
        </div>
    `;
}

// Show notification
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 150);
    }, 3000);
}
</script>
@endpush
@endsection