@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.index') }}" method="GET">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search products...">
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['id'] }}" {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" placeholder="Min" value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Sort By -->
                        <div class="mb-3">
                            <label class="form-label">Sort By</label>
                            <select name="sort_by" class="form-select">
                                <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            @if(isset($currentCategory))
                <div class="mb-4">
                    <h2>{{ $currentCategory['name'] }}</h2>
                    @if(!empty($currentCategory['description']))
                        <p class="text-muted">{{ $currentCategory['description'] }}</p>
                    @endif
                </div>
            @else
                <div class="mb-4">
                    <h2>All Products</h2>
                    <p class="text-muted">Showing {{ $pagination['total'] ?? 0 }} products</p>
                </div>
            @endif

            <div class="row">
                @forelse($products as $product)
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card product-card h-100">
                        @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                            <img src="{{ $product['image_urls'][0] }}" class="card-img-top" alt="{{ $product['name'] }}" style="height: 200px; object-fit: cover;">
                        @else
                            <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image">
                        @endif
                        
                        <!-- Wishlist Button -->
                        @if(Session::has('user') && Session::get('user')['role'] === 'customer')
                            <button class="btn btn-sm btn-light wishlist-btn" data-product-id="{{ $product['id'] }}" style="position: absolute; top: 10px; right: 10px;">
                                <i class="far fa-heart"></i>
                            </button>
                        @endif
                        
                        <div class="card-body">
                            <h5 class="card-title">{{ Str::limit($product['name'], 40) }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($product['description'], 60) }}</p>
                            
                            @if(!empty($product['category']))
                                <span class="badge bg-secondary mb-2">{{ $product['category']['name'] }}</span>
                            @endif
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    @if($product['has_offer'] && $product['final_price'] < $product['price'])
                                        <span class="text-muted text-decoration-line-through">${{ number_format($product['price'], 2) }}</span>
                                        <span class="text-danger fw-bold d-block">${{ number_format($product['final_price'], 2) }}</span>
                                    @else
                                        <span class="text-primary fw-bold">${{ number_format($product['price'], 2) }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if(!empty($product['avg_rating']) && $product['avg_rating'] > 0)
    <span class="badge bg-warning text-dark">
        <i class="fas fa-star"></i> {{ number_format($product['avg_rating'], 1) }}
    </span>
    <small class="text-muted ms-1">
        ({{ $product['review_count'] ?? 0 }})
    </small>
@endif

                                </div>
                            </div>
                            
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
                            
                            @if($product['stock'] < 10 && $product['stock'] > 0)
                                <div class="mt-2">
                                    <small class="text-warning">Only {{ $product['stock'] }} left!</small>
                                </div>
                            @elseif($product['stock'] == 0)
                                <div class="mt-2">
                                    <small class="text-danger fw-bold">Out of Stock</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No products found matching your criteria.
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if(isset($pagination) && $pagination['last_page'] > 1)
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        @for($i = 1; $i <= $pagination['last_page']; $i++)
                            <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                                <a class="page-link" href="{{ route('products.index', array_merge(request()->all(), ['page' => $i])) }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        
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
            if(data.success) {
                const icon = this.querySelector('i');
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
                icon.classList.toggle('text-danger');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .product-card {
        transition: all 0.3s;
        position: relative;
    }
    .product-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-5px);
    }
    .wishlist-btn {
        z-index: 10;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        padding: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endpush
@endsection