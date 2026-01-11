@extends('layouts.app')

@section('title', 'Home - Multi-Vendor E-Commerce')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="hero-section bg-light p-5 rounded mb-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4">Welcome to MultiVendor Shop</h1>
                <p class="lead">Discover amazing products from trusted vendors worldwide</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Shop Now
                </a>
                <a href="{{ route('vendor.register') }}" class="btn btn-outline-primary btn-lg ms-2">
                    <i class="fas fa-store"></i> Become a Vendor
                </a>
            </div>
            <div class="col-md-6">
                <img src="https://via.placeholder.com/500x400?text=Shop+Now" alt="Hero" class="img-fluid rounded">
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    @if(!empty($categories))
    <div class="mb-5">
        <h2 class="mb-4">Shop by Category</h2>
        <div class="row">
            @foreach($categories as $category)
            <div class="col-md-3 col-sm-6 mb-4">
                <a href="{{ route('category.show', $category['id']) }}" class="text-decoration-none">
                    <div class="card category-card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-tag fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">{{ $category['name'] }}</h5>
                            @if(!empty($category['description']))
                                <p class="card-text text-muted small">{{ Str::limit($category['description'], 60) }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Featured Products -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Featured Products</h2>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary">View All</a>
        </div>
        
      <div class="row">
    @forelse($products as $product)
    <div class="col-md-3 col-sm-6 mb-4">
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
            
            {{-- Card Body - Compact Design --}}
            <div class="card-body d-flex flex-column p-3">
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
                        @if(Session::has('user') && Session::get('user')['role'] === 'customer')
                            <form action="{{ route('customer.cart.add') }}" 
                                  method="POST" 
                                  class="flex-fill">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                                <button type="submit" 
                                        class="btn btn-cart w-100"
                                        {{ $product['stock'] == 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-shopping-cart"></i> Cart
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-cart flex-fill">
                                <i class="fas fa-shopping-cart"></i> Cart
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No products available at the moment.
        </div>
    </div>
    @endforelse
</div>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-md-4 text-center mb-4">
            <div class="feature-box p-4">
                <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                <h4>Fast Delivery</h4>
                <p>Quick and reliable shipping to your doorstep</p>
            </div>
        </div>
        <div class="col-md-4 text-center mb-4">
            <div class="feature-box p-4">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h4>Secure Payment</h4>
                <p>100% secure payment processing</p>
            </div>
        </div>
        <div class="col-md-4 text-center mb-4">
            <div class="feature-box p-4">
                <i class="fas fa-headset fa-3x text-info mb-3"></i>
                <h4>24/7 Support</h4>
                <p>Always here to help you</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .hero-section h1, .hero-section p {
        color: white;
    }
    .category-card:hover {
        transform: translateY(-5px);
        transition: all 0.3s;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .feature-box {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s;
    }
    .feature-box:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* ========================================
       COMPACT PRODUCT CARD STYLES
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
        color: white;
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

    @keyframes pulse-badge {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

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
    }
</style>
@endpush
@endsection