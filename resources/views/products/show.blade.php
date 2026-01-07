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
                    <h3 class="text-danger">
                        ${{ number_format($product['final_price'], 2) }}
                        <span class="text-muted text-decoration-line-through fs-5 ms-2">
                            ${{ number_format($product['price'], 2) }}
                        </span>
                    </h3>
                    <span class="badge bg-danger">
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
                    <span class="text-success">In Stock ({{ $product['stock'] }} available)</span>
                @else
                    <span class="text-danger">Out of Stock</span>
                @endif
            </div>

            <div class="mb-4">
                <h5>Description:</h5>
                <p>{{ $product['description'] }}</p>
            </div>

            @if($product['stock'] > 0)
                <div class="d-grid gap-2 mb-3">
                    @if(Auth::check() && Auth::user()->role === 'customer')
                        <form action="{{ route('customer.cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="quantity" value="1" min="1" max="{{ $product['stock'] }}">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
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
   {{-- Reviews Section --}}
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
                        {{-- Rating Bars (optional) --}}
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

@push('scripts')
<script>
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
            alert(data.message);
        }
    });
}
</script>
@endpush
@endsection