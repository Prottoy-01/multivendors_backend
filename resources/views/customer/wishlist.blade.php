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
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                    <img src="{{ $product['image_urls'][0] }}" class="card-img-top" alt="{{ $product['name'] }}" 
                         style="height: 200px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                @endif
                
                <div class="card-body">
                    <h5 class="card-title">{{ Str::limit($product['name'], 40) }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($product['description'], 60) }}</p>
                    
                    <div class="mb-3">
                        @if($product['has_offer'] && $product['final_price'] < $product['price'])
                            <span class="text-muted text-decoration-line-through">${{ number_format($product['price'], 2) }}</span>
                            <span class="text-danger fw-bold d-block">${{ number_format($product['final_price'], 2) }}</span>
                        @else
                            <span class="text-primary fw-bold">${{ number_format($product['price'], 2) }}</span>
                        @endif
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.show', $product['id']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <form action="{{ route('customer.cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                        <button class="btn btn-danger btn-sm" onclick="removeFromWishlist({{ $product['id'] }})">
                            <i class="fas fa-heart-broken"></i> Remove
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

@push('scripts')
<script>
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
            location.reload();
        }
    });
}
</script>
@endpush
@endsection