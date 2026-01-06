@extends('layouts.app')

@section('title', 'My Products')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-box"></i> My Products</h2>
                <p class="text-muted">Manage your product inventory</p>
            </div>
            <div>
                <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Products ({{ count($products) }})</h5>
        </div>
        <div class="card-body">
            @forelse($products as $product)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                                <img src="{{ $product['image_urls'][0] }}" alt="{{ $product['name'] }}" 
                                     class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 100px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h5>{{ $product['name'] }}</h5>
                            <p class="text-muted mb-1">{{ Str::limit($product['description'], 80) }}</p>
                            @if(!empty($product['category']))
                                <span class="badge bg-secondary">{{ $product['category']['name'] }}</span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <strong>Price:</strong><br>
                            ${{ number_format($product['price'], 2) }}
                            @if($product['has_offer'])
                                <br><small class="text-danger">Offer: ${{ number_format($product['final_price'], 2) }}</small>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <strong>Stock:</strong><br>
                            @if($product['stock'] > 10)
                                <span class="text-success">{{ $product['stock'] }} units</span>
                            @elseif($product['stock'] > 0)
                                <span class="text-warning">{{ $product['stock'] }} units</span>
                            @else
                                <span class="text-danger">Out of Stock</span>
                            @endif
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('vendor.products.edit', $product['id']) }}" class="btn btn-sm btn-warning mb-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('vendor.products.destroy', $product['id']) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" 
                                        onclick="return confirm('Delete this product?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            <a href="{{ route('products.show', $product['id']) }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4>No Products Yet</h4>
                <p class="text-muted">Start by adding your first product!</p>
                <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Add Your First Product
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection