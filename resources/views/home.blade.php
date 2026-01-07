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
            @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                <img src="{{ $product['image_urls'][0] }}" class="card-img-top" alt="{{ $product['name'] }}" style="height: 200px; object-fit: cover;">
            @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image">
            @endif
            
            <div class="card-body">
                <h5 class="card-title">{{ Str::limit($product['name'], 30) }}</h5>
                <p class="card-text text-muted small">{{ Str::limit($product['description'], 50) }}</p>
                
                {{-- Rating Display --}}
                @if(!empty($product['avg_rating']) && $product['avg_rating'] > 0)
                    <div class="mb-2">
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-star"></i> {{ number_format($product['avg_rating'], 1) }}
                        </span>
                        <small class="text-muted">
                            ({{ $product['review_count'] ?? 0 }} {{ Str::plural('review', $product['review_count'] ?? 0) }})
                        </small>
                    </div>
                @else
                    <div class="mb-2">
                        <small class="text-muted">No reviews yet</small>
                    </div>
                @endif
                
                {{-- Price Display --}}
                <div class="mb-3">
                    @if($product['has_offer'] && $product['final_price'] < $product['price'])
                        <span class="text-muted text-decoration-line-through">${{ number_format($product['price'], 2) }}</span>
                        <span class="text-danger fw-bold ms-2">${{ number_format($product['final_price'], 2) }}</span>
                    @else
                        <span class="text-primary fw-bold">${{ number_format($product['price'], 2) }}</span>
                    @endif
                </div>
                
                {{-- Action Buttons --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('products.show', $product['id']) }}" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-eye"></i> View
                    </a>
                    @if(Session::has('user') && Session::get('user')['role'] === 'customer')
                        <form action="{{ route('customer.cart.add') }}" method="POST" class="flex-grow-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-cart-plus"></i> Cart
                            </button>
                        </form>
                    @endif
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
    .product-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s;
    }
    .feature-box {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s;
    }
    .feature-box:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush
@endsection