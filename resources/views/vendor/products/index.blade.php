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
                        {{-- Product Image with Badges --}}
                        <div class="position-relative">
                            @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                                <img src="{{ $product['image_urls'][0] }}" class="card-img-top" alt="{{ $product['name'] }}" style="height: 200px; object-fit: cover;">
                            @else
                                <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image" style="height: 200px; object-fit: cover;">
                            @endif
                            
                            {{-- Discount Badge --}}
                            @if($product['has_offer'])
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    @if($product['discount_type'] == 'percentage')
                                        {{ $product['discount_value'] }}% OFF
                                    @else
                                        ${{ $product['discount_value'] }} OFF
                                    @endif
                                </span>
                            @endif

                            {{-- Stock Status Badge --}}
                            @if($product['stock'] == 0)
                                <span class="badge bg-dark position-absolute top-0 start-0 m-2">
                                    Out of Stock
                                </span>
                            @elseif($product['stock'] < 10)
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">
                                    Low Stock
                                </span>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title">{{ Str::limit($product['name'], 40) }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($product['description'], 60) }}</p>
                            
                            @if(!empty($product['category']))
                                <span class="badge bg-secondary mb-2">{{ $product['category']['name'] }}</span>
                            @endif
                            
                            {{-- Price Display --}}
                            <div class="mb-3">
                                @if($product['has_offer'] && $product['final_price'] < $product['price'])
                                    <div>
                                        <small class="text-muted text-decoration-line-through">
                                            ${{ number_format($product['price'], 2) }}
                                        </small>
                                        <span class="text-danger fw-bold d-block fs-5">
                                            ${{ number_format($product['final_price'], 2) }}
                                        </span>
                                        <small class="text-success">
                                            <i class="fas fa-tag"></i> 
                                            Save ${{ number_format($product['price'] - $product['final_price'], 2) }}
                                        </small>
                                    </div>
                                @else
                                    <span class="text-primary fw-bold fs-5">
                                        ${{ number_format($product['price'], 2) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Stock Info --}}
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-boxes"></i> Stock: 
                                    <strong class="{{ $product['stock'] == 0 ? 'text-danger' : ($product['stock'] < 10 ? 'text-warning' : 'text-success') }}">
                                        {{ $product['stock'] }} units
                                    </strong>
                                </small>
                            </div>

                            {{-- Rating --}}
                            @if(!empty($product['avg_rating']) && $product['avg_rating'] > 0)
                                <div class="mb-3">
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-star"></i> {{ number_format($product['avg_rating'], 1) }}
                                    </span>
                                    <small class="text-muted">
                                        ({{ $product['review_count'] ?? 0 }} reviews)
                                    </small>
                                </div>
                            @endif
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('vendor.products.edit', $product['id']) }}" class="btn btn-warning btn-sm flex-grow-1">
                                    <i class="fas fa-edit"></i> Edit Product
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-product" 
                                        data-product-id="{{ $product['id'] }}"
                                        data-product-name="{{ $product['name'] }}">
                                    <i class="fas fa-trash"></i>
                                </button>
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

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
                <p class="mb-0"><strong id="productName"></strong></p>
                <p class="text-danger"><small>This action cannot be undone!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Delete product confirmation
document.querySelectorAll('.delete-product').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        
        document.getElementById('productName').textContent = productName;
        document.getElementById('deleteForm').action = `/vendor/products/${productId}`;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
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