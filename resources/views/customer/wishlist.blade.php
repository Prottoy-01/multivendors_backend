@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-heart"></i> My Wishlist</h2>
            <p class="text-muted">Products you love</p>
        </div>
    </div>

    @if(count($wishlist) > 0)
    <div class="row">
        @foreach($wishlist as $product)
        <div class="col-md-3 col-sm-6 mb-4" id="wishlist-item-{{ $product['id'] }}">
            <div class="card product-card h-100">
                {{-- Product Image with Badges --}}
                <div class="product-image-wrapper position-relative">
                    @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                        <img src="{{ $product['image_urls'][0] }}" 
                             class="card-img-top product-image" 
                             alt="{{ $product['name'] }}">
                    @else
                        <img src="https://via.placeholder.com/300x200?text=No+Image" 
                             class="card-img-top product-image" 
                             alt="No Image">
                    @endif
                    
                    {{-- Discount Badge --}}
                    @if($product['has_offer'] && $product['final_price'] < $product['price'])
                        <span class="badge badge-discount position-absolute">
                            @if($product['discount_type'] == 'percentage')
                                {{ $product['discount_value'] }}% OFF
                            @else
                                ${{ $product['discount_value'] }} OFF
                            @endif
                        </span>
                    @endif

                    {{-- Stock Badge --}}
                    @if($product['stock'] == 0)
                        <span class="badge badge-stock badge-out-stock position-absolute">
                            Out of Stock
                        </span>
                    @elseif($product['stock'] < 10)
                        <span class="badge badge-stock badge-low-stock position-absolute">
                            Only {{ $product['stock'] }} left
                        </span>
                    @endif
                </div>
                
                {{-- Card Body - Compact Design (Same as Home) --}}
                <div class="card-body d-flex flex-column p-3">
                    {{-- Category Badge --}}
                    @if(!empty($product['category']))
                        <div class="mb-2">
                            <span class="badge bg-secondary small">{{ $product['category']['name'] }}</span>
                        </div>
                    @endif
                    
                    {{-- Product Title (2 lines max) --}}
                    <h6 class="product-title mb-2">
                        {{ Str::limit($product['name'], 50) }}
                    </h6>
                    
                    {{-- Product Description (1 line only) --}}
                    <p class="product-description text-muted small mb-3">
                        {{ Str::limit($product['description'], 60) }}
                    </p>
                    
                    {{-- Spacer to push content to bottom --}}
                    <div class="mt-auto">
                        {{-- Price and Review Row --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            {{-- LEFT: Price --}}
                            <div class="price-section">
                                @if($product['has_offer'] && $product['final_price'] < $product['price'])
                                    <div class="price-with-discount">
                                        <small class="text-muted text-decoration-line-through d-block">
                                            ${{ number_format($product['price'], 2) }}
                                        </small>
                                        <span class="text-danger fw-bold fs-6">
                                            ${{ number_format($product['final_price'], 2) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-primary fw-bold fs-6">
                                        ${{ number_format($product['price'], 2) }}
                                    </span>
                                @endif
                            </div>
                            
                            {{-- RIGHT: Review and Save --}}
                            <div class="info-section text-end">
                                {{-- Rating --}}
                                @if(!empty($product['avg_rating']) && $product['avg_rating'] > 0)
                                    <div class="rating-badge mb-1">
                                        <span class="badge bg-warning text-dark small">
                                            <i class="fas fa-star"></i> {{ number_format($product['avg_rating'], 1) }}
                                        </span>
                                    </div>
                                @endif
                                
                                {{-- Savings --}}
                                @if($product['has_offer'] && $product['final_price'] < $product['price'])
                                    <div class="save-badge">
                                        <small class="badge bg-success small">
                                            Save ${{ number_format($product['price'] - $product['final_price'], 2) }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Action Buttons - Beautiful Design --}}
                        <div class="button-group d-flex gap-2">
                            <a href="{{ route('products.show', $product['id']) }}" 
                               class="btn btn-view flex-fill">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button type="button" 
                                    class="btn btn-cart flex-fill" 
                                    id="add-cart-btn-{{ $product['id'] }}"
                                    onclick="addToCartFromWishlist({{ $product['id'] }})"
                                    {{ $product['stock'] == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-cart-plus"></i> Cart
                            </button>
                        </div>
                        
                        {{-- Remove Button (Below, Full Width) --}}
                        <button class="btn btn-remove w-100 mt-2" onclick="removeFromWishlist({{ $product['id'] }})">
                            <i class="fas fa-heart-broken"></i> Remove from Wishlist
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-heart-broken fa-4x text-muted mb-3"></i>
                    <h3>Your Wishlist is Empty</h3>
                    <p class="text-muted">Add products to your wishlist to save them for later!</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    /* ========================================
       COMPACT PRODUCT CARD STYLES
       (Exact copy from home.blade.php)
       ======================================== */
    
    .product-card {
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
        background: #fff;
    }

    .product-card:hover {
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        transform: translateY(-8px);
        border-color: #007bff;
    }

    .product-image-wrapper {
        overflow: hidden;
        height: 200px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        position: relative;
    }

    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.15);
    }

    .badge-discount {
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 700;
        padding: 8px 14px;
        font-size: 0.7rem;
        border-radius: 25px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        z-index: 3;
        animation: pulse-badge 2s infinite;
        letter-spacing: 0.5px;
    }

    .badge-stock {
        top: 12px;
        left: 12px;
        font-weight: 600;
        padding: 6px 12px;
        font-size: 0.65rem;
        border-radius: 20px;
        z-index: 3;
    }

    .badge-out-stock {
        background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(252, 74, 26, 0.3);
    }

    .badge-low-stock {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: black;
        box-shadow: 0 4px 10px rgba(240, 147, 251, 0.3);
    }

    .card-body {
        padding: 1rem;
    }

    /* Product Title - 2 Lines Max */
    .product-title {
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.3;
        min-height: 38px;
        max-height: 38px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    /* Product Description - 1 Line Only */
    .product-description {
        font-size: 0.8rem;
        line-height: 1.3;
        min-height: 20px;
        max-height: 20px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #7f8c8d;
    }

    .price-section {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .info-section {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .rating-badge .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        font-weight: 600;
    }

    .save-badge .badge {
        font-size: 0.65rem;
        padding: 3px 8px;
        font-weight: 600;
    }

    /* Beautiful Buttons */
    .button-group {
        margin-top: 0.75rem;
    }

    .btn-view,
    .btn-cart {
        font-size: 0.85rem;
        font-weight: 600;
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .btn-view {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-view:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-cart {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .btn-cart:hover:not(:disabled) {
        background: linear-gradient(135deg, #38ef7d 0%, #11998e 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(56, 239, 125, 0.4);
        color: white;
    }

    .btn-cart:disabled {
        background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Remove Button */
    .btn-remove {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(235, 51, 73, 0.3);
    }

    .btn-remove:hover {
        background: linear-gradient(135deg, #f45c43 0%, #eb3349 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(235, 51, 73, 0.5);
        color: white;
    }

    @keyframes pulse-badge {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    /* ========================================
       RESPONSIVE
       ======================================== */
    @media (max-width: 768px) {
        .product-image-wrapper {
            height: 180px;
        }
        
        .product-image {
            height: 180px;
        }
        
        .product-title {
            font-size: 0.9rem;
            min-height: 35px;
            max-height: 35px;
        }
        
        .product-description {
            font-size: 0.75rem;
        }
        
        .btn-view,
        .btn-cart {
            font-size: 0.8rem;
            padding: 8px 12px;
        }
        
        .btn-remove {
            font-size: 0.75rem;
            padding: 6px 10px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Add to cart from wishlist
function addToCartFromWishlist(productId) {
    const btn = document.getElementById(`add-cart-btn-${productId}`);
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    fetch('{{ route('customer.cart.add') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            product_id: productId, 
            quantity: 1 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Added to cart!');
            
            // Update cart count if it exists
            updateCartCount();
            
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        } else {
            showNotification('error', data.message || 'Failed to add to cart');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'An error occurred');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Remove from wishlist
function removeFromWishlist(productId) {
    if (!confirm('Remove from wishlist?')) return;
    
    fetch('{{ route('customer.wishlist.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fade out and remove item
            const item = document.getElementById(`wishlist-item-${productId}`);
            item.style.transition = 'opacity 0.3s';
            item.style.opacity = '0';
            
            setTimeout(() => {
                item.remove();
                
                // Check if wishlist is empty
                const remainingItems = document.querySelectorAll('[id^="wishlist-item-"]');
                if (remainingItems.length === 0) {
                    showEmptyWishlist();
                }
            }, 300);
            
            showNotification('success', 'Removed from wishlist');
        } else {
            showNotification('error', data.message || 'Failed to remove');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'An error occurred');
    });
}

// Show empty wishlist message
function showEmptyWishlist() {
    const container = document.querySelector('.container > .row:last-of-type');
    container.innerHTML = `
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-heart-broken fa-4x text-muted mb-3"></i>
                    <h3>Your Wishlist is Empty</h3>
                    <p class="text-muted">Add products to your wishlist to save them for later!</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
            </div>
        </div>
    `;
}

// Update cart count
function updateCartCount() {
    fetch('{{ route('customer.cart.count') }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.cart-count-badge');
            if (badge && data.count !== undefined) {
                badge.textContent = data.count;
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
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