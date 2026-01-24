@extends('layouts.app')

@section('title', $product['name'])

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            @if(!empty($product['category']))
                <li class="breadcrumb-item"><a href="{{ route('category.show', $product['category']['id']) }}">{{ $product['category']['name'] }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product['name'] }}</li>
        </ol>
    </nav>

    {{-- ADMIN DELETE BUTTON SECTION --}}
    @if(Session::has('user') && Session::get('user')['role'] === 'admin')
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4" role="alert">
            <div>
                <i class="fas fa-user-shield"></i> <strong>Admin Panel:</strong> You can manage this product
            </div>
            <div>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                    <i class="fas fa-trash"></i> Delete Product
                </button>
            </div>
        </div>
    @endif

    {{-- SUCCESS/ERROR MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6">
            <div class="card">
                @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                    <img src="{{ $product['image_urls'][0] }}" class="card-img-top" alt="{{ $product['name'] }}" 
                         style="height: 400px; object-fit: contain;">
                    
                    @if(count($product['image_urls']) > 1)
                    <div class="card-body">
                        <div class="row">
                            @foreach($product['image_urls'] as $imageUrl)
                            <div class="col-3 mb-2">
                                <img src="{{ $imageUrl }}" class="img-thumbnail" alt="{{ $product['name'] }}" 
                                     style="height: 80px; object-fit: cover; cursor: pointer;">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                        <i class="fas fa-image fa-5x text-muted"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-6">
            <h1>{{ $product['name'] }}</h1>
            
            @if(!empty($product['vendor']))
                <p class="text-muted">
                    <i class="fas fa-store"></i> Sold by: 
                    <strong>{{ $product['vendor']['shop_name'] }}</strong>
                </p>
            @endif

            @if(!empty($product['avg_rating']) && $product['avg_rating'] > 0)
                <div class="mb-3">
                    <span class="rating-stars text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($product['avg_rating']))
                                <i class="fas fa-star"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </span>

                    <span class="ms-2">
                        {{ number_format($product['avg_rating'], 1) }}
                        ({{ $product['review_count'] ?? 0 }} reviews)
                    </span>
                </div>
            @endif

            <div class="mb-3">
                @if($product['has_offer'] && $product['final_price'] < $product['price'])
                    <h3 class="text-success">
                        ${{ number_format($product['final_price'], 2) }}
                        <span class="text-muted text-decoration-line-through fs-5 ms-2">
                            ${{ number_format($product['price'], 2) }}
                        </span>
                    </h3>
                    <span class="badge bg-success">
                        @if($product['discount_type'] === 'percentage')
                            {{ $product['discount_value'] }}% OFF
                        @else
                            ${{ $product['discount_value'] }} OFF
                        @endif
                    </span>
                @else
                    <h3 class="text-primary">${{ number_format($product['price'], 2) }}</h3>
                @endif
            </div>

            <div class="mb-3">
                <strong>Availability:</strong>
                @if($product['stock'] > 0)
                    <span class="text-primary">In Stock ({{ $product['stock'] }} available)</span>
                @else
                    <span class="text-danger">Out of Stock</span>
                @endif
            </div>

            <div class="mb-4">
                <h5>Description:</h5>
                <p>{{ $product['description'] }}</p>
            </div>

            {{--  NEW: VARIANT SELECTOR SECTION  --}}
            @if($product['stock'] > 0 || ($hasVariants ?? false))
                
                {{-- Variant Selection Card --}}
                @if(($hasVariants ?? false) && count($variantAttributes ?? []) > 0)
                <div class="card mb-3 border-primary">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-sliders-h"></i> Select Options:
                        </h5>
                        
                        @foreach($variantAttributes as $attributeName => $values)
                        <div class="mb-3">
                            <label class="form-label">
                                <strong>{{ ucfirst($attributeName) }}:</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($values as $value)
                                <button type="button" 
                                        class="btn btn-outline-primary variant-option" 
                                        data-attribute="{{ $attributeName }}"
                                        data-value="{{ $value }}"
                                        onclick="selectVariantAttribute('{{ $attributeName }}', '{{ $value }}')">
                                    @if($attributeName === 'color')
                                        <i class="fas fa-circle me-1" style="color: {{ strtolower($value) }}"></i>
                                    @endif
                                    {{ ucfirst($value) }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                        
                        {{-- Selected Variant Info --}}
                        <div id="variant-info" class="alert alert-success d-none mt-3">
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <strong><i class="fas fa-check-circle"></i> Selected:</strong> 
                                    <span id="selected-variant-name" class="ms-2"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Price:</strong> 
                                    <span id="variant-price" class="text-primary fs-5"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Stock:</strong> 
                                    <span id="variant-stock" class="badge bg-success"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>SKU:</strong> 
                                    <span id="variant-sku" class="text-muted small"></span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Warning if no variant selected --}}
                        <div id="variant-warning" class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Please select all options above to continue
                        </div>
                        
                        <input type="hidden" id="selected-variant-id" value="">
                    </div>
                </div>
                @endif
                
                {{--  UPDATED: Add to Cart Section  --}}
                <div class="d-grid gap-2 mb-3">
                    @if(Auth::check() && Auth::user()->role === 'customer')
                        <form action="{{ route('customer.cart.add') }}" method="POST" id="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <input type="hidden" name="variant_id" id="cart-variant-id" value="">
                            <div class="input-group mb-3">
                                <input type="number" 
                                       class="form-control" 
                                       name="quantity" 
                                       id="quantity-input"
                                       value="1" 
                                       min="1" 
                                       max="{{ ($hasVariants ?? false) ? '999' : $product['stock'] }}">
                                <button type="button" 
                                        class="btn btn-primary btn-lg" 
                                        id="add-to-cart-btn"
                                        onclick="handleAddToCart(event)"
                                        {{ ($hasVariants ?? false) ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                            @if($hasVariants ?? false)
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Please select all options to enable add to cart
                            </small>
                            @endif
                        </form>
                        
                        <button class="btn btn-outline-danger" onclick="toggleWishlist({{ $product['id'] }})">
                            <i class="fas fa-heart"></i> Add to Wishlist
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login to Purchase
                        </a>
                    @endif
                </div>
            @endif

            @if(!empty($product['category']))
                <div class="mb-2">
                    <strong>Category:</strong> 
                    <a href="{{ route('category.show', $product['category']['id']) }}" class="badge bg-secondary">
                        {{ $product['category']['name'] }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-star"></i> Customer Reviews
                @if(isset($product['review_count']) && $product['review_count'] > 0)
                    ({{ $product['review_count'] }})
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if(isset($product['avg_rating']) && $product['avg_rating'] > 0)
                <div class="mb-4 p-3 bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <h1 class="display-3 mb-0">{{ number_format($product['avg_rating'], 1) }}</h1>
                            <div class="text-warning mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $product['avg_rating'])
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <small class="text-muted">Based on {{ $product['review_count'] }} reviews</small>
                        </div>
                        <div class="col-md-9">
                            {{-- Rating Bars --}}
                            @php
                                $reviews = \App\Models\Review::where('product_id', $product['id'])
                                    ->where('is_approved', true)
                                    ->selectRaw('rating, COUNT(*) as count')
                                    ->groupBy('rating')
                                    ->orderBy('rating', 'desc')
                                    ->get()
                                    ->pluck('count', 'rating');
                                $totalReviews = $product['review_count'];
                            @endphp
                            
                            @for($i = 5; $i >= 1; $i--)
                                <div class="d-flex align-items-center mb-2">
                                    <span class="me-2" style="width: 60px;">{{ $i }} star</span>
                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                        @php
                                            $count = $reviews->get($i, 0);
                                            $percentage = $totalReviews > 0 ? ($count / $totalReviews * 100) : 0;
                                        @endphp
                                        <div class="progress-bar bg-warning" 
                                             role="progressbar" 
                                             style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    <span style="width: 40px;">{{ $count }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endif

            {{-- Individual Reviews --}}
            @php
                $reviews = \App\Models\Review::where('product_id', $product['id'])
                    ->where('is_approved', true)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            @endphp

            @forelse($reviews as $review)
                <div class="review-item mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>{{ $review->user->name }}</strong>
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $review->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <p class="mb-0">{{ $review->comment }}</p>
                    
                    @if($review->user_id === auth()->id())
                        <span class="badge bg-info mt-2">Your Review</span>
                    @endif
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                </div>
            @endforelse

            {{-- Pagination --}}
            @if($reviews->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if(count($relatedProducts) > 0)
    <div class="row mt-5">
        <div class="col-md-12">
            <h3>Related Products</h3>
        </div>
        @foreach($relatedProducts as $relatedProduct)
        <div class="col-md-3 mb-3">
            <div class="card">
                @if(!empty($relatedProduct['image_urls']) && count($relatedProduct['image_urls']) > 0)
                    <img src="{{ $relatedProduct['image_urls'][0] }}" class="card-img-top" alt="{{ $relatedProduct['name'] }}" 
                         style="height: 200px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ Str::limit($relatedProduct['name'], 40) }}</h5>
                    <p class="text-primary fw-bold">${{ number_format($relatedProduct['final_price'] ?? $relatedProduct['price'], 2) }}</p>
                    <a href="{{ route('products.show', $relatedProduct['id']) }}" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- DELETE CONFIRMATION MODAL --}}
@if(Session::has('user') && Session::get('user')['role'] === 'admin')
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProductModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Product Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-circle"></i> <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p><strong>Product:</strong> {{ $product['name'] }}</p>
                <p><strong>Category:</strong> {{ $product['category']['name'] ?? 'N/A' }}</p>
                <p><strong>Vendor:</strong> {{ $product['vendor']['shop_name'] ?? 'N/A' }}</p>
                <p><strong>Price:</strong> ${{ number_format($product['price'], 2) }}</p>
                
                <hr>
                
                <p class="mb-2">Are you sure you want to delete this product? This will:</p>
                <ul class="mb-0">
                    <li>Remove the product from the website permanently</li>
                    <li>Delete all product images from storage</li>
                    <li>Remove it from all customer carts and wishlists</li>
                    <li><strong class="text-danger">This action cannot be reversed</strong></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form action="{{ route('admin.products.delete', $product['id']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{--  UPDATED: JavaScript with Variant Logic  --}}
@push('scripts')
<script>
// Product variants data
const variants = @json($product['active_variants'] ?? []);
const hasVariants = @json($hasVariants ?? false);
const variantAttributes = @json($variantAttributes ?? []);

// Track selected attributes
let selectedAttributes = {};
const requiredAttributes = Object.keys(variantAttributes);

// Select variant attribute
function selectVariantAttribute(attributeName, value) {
    // Update selected attributes
    selectedAttributes[attributeName] = value;
    
    // Update UI - highlight selected button
    document.querySelectorAll(`.variant-option[data-attribute="${attributeName}"]`).forEach(btn => {
        if (btn.dataset.value === value) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        }
    });
    
    // Check if all attributes are selected
    updateVariantDisplay();
}

// Update variant display based on selection
function updateVariantDisplay() {
    // Check if all required attributes are selected
    const allSelected = requiredAttributes.every(attr => selectedAttributes[attr]);
    
    if (!allSelected) {
        // Hide variant info, show warning
        document.getElementById('variant-info').classList.add('d-none');
        document.getElementById('variant-warning').classList.remove('d-none');
        document.getElementById('add-to-cart-btn').disabled = true;
        return;
    }
    
    // Find matching variant
    const matchingVariant = variants.find(variant => {
        return requiredAttributes.every(attr => {
            return variant.attributes[attr] === selectedAttributes[attr];
        });
    });
    
    if (matchingVariant) {
        // Show variant info
        document.getElementById('variant-info').classList.remove('d-none');
        document.getElementById('variant-warning').classList.add('d-none');
        
        // Update display
        const variantName = Object.entries(selectedAttributes)
            .map(([key, value]) => `${ucfirst(key)}: ${ucfirst(value)}`)
            .join(' | ');
        
        document.getElementById('selected-variant-name').textContent = variantName;
        document.getElementById('variant-price').textContent = '$' + parseFloat(matchingVariant.price).toFixed(2);
        document.getElementById('variant-stock').textContent = matchingVariant.stock + ' available';
        document.getElementById('variant-sku').textContent = matchingVariant.sku || 'N/A';
        
        // Update form
        document.getElementById('selected-variant-id').value = matchingVariant.id;
        document.getElementById('cart-variant-id').value = matchingVariant.id;
        document.getElementById('quantity-input').max = matchingVariant.stock;
        
        // Enable add to cart button
        document.getElementById('add-to-cart-btn').disabled = false;
    } else {
        // No matching variant found
        document.getElementById('variant-info').classList.add('d-none');
        document.getElementById('variant-warning').innerHTML = '<i class="fas fa-exclamation-triangle"></i> This combination is not available';
        document.getElementById('variant-warning').classList.remove('d-none');
        document.getElementById('add-to-cart-btn').disabled = true;
    }
}

// Capitalize first letter
function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Handle Add to Cart
function handleAddToCart(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Validate variant selection
    if (hasVariants && !document.getElementById('cart-variant-id').value) {
        alert('Please select all product options before adding to cart.');
        return false;
    }
    
    const form = document.getElementById('add-to-cart-form');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('add-to-cart-btn');
    const originalBtnText = submitBtn.innerHTML;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    // Submit via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest', // Mark as AJAX request
            'Accept': 'application/json' // Request JSON response
        }
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            // Try to get error message from JSON
            return response.json().then(data => {
                throw new Error(data.message || 'Server error');
            }).catch(() => {
                throw new Error('Server error: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data); // Debug log
        
        if (data.success) {
            showNotification('success', data.message || 'Product added to cart!');
            updateCartCount();
            
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Added!';
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }, 2000);
        } else {
            showNotification('error', data.message || 'Failed to add to cart');
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', error.message || 'An error occurred. Please try again.');
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
    
    return false;
}

// Also prevent form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-to-cart-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleAddToCart(e);
            return false;
        });
    }
});

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
        .catch(error => console.error('Error:', error));
}

// Wishlist function
function toggleWishlist(productId) {
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
            showNotification('success', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'An error occurred');
    });
}
</script>
@endpush
@endsection