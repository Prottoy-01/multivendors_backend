@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-edit"></i> Edit Product</h2>
            <p class="text-muted">Update product information</p>
        </div>
    </div>

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
        <div class="col-md-8">
            {{-- Basic Product Information Card --}}
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Product Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.products.update', $product['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $product['name']) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description', $product['description']) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Base Price ($) *</label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" step="0.01" min="0" value="{{ old('price', $product['price']) }}" required>
                                <small class="text-muted">Base price (overridden by variants if present)</small>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Base Stock *</label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                       id="stock" name="stock" min="0" value="{{ old('stock', $product['stock']) }}" required>
                                <small class="text-muted">Total stock (if no variants)</small>
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['id'] }}" 
                                            {{ old('category_id', $product['category_id']) == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Images -->
                        @if(!empty($product['image_urls']) && count($product['image_urls']) > 0)
                        <div class="mb-3">
                            <label class="form-label">Current Images</label>
                            <div class="row">
                                @foreach($product['image_urls'] as $imageUrl)
                                <div class="col-md-3 mb-2">
                                    <img src="{{ $imageUrl }}" class="img-thumbnail" alt="Product Image">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label for="images" class="form-label">Add New Images (Optional)</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                            <small class="text-muted">Select new images to add to this product</small>
                            <div id="image-preview" class="mt-2"></div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-tag"></i> Offer Settings</h5>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="has_offer" name="has_offer" 
                                   value="1" {{ old('has_offer', $product['has_offer']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_offer">
                                This product has a special offer
                            </label>
                        </div>

                        <div id="offer-fields" style="display: {{ old('has_offer', $product['has_offer']) ? 'block' : 'none' }};">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="discount_type" class="form-label">Discount Type</label>
                                    <select class="form-select" id="discount_type" name="discount_type">
                                        <option value="percentage" {{ old('discount_type', $product['discount_type']) == 'percentage' ? 'selected' : '' }}>
                                            Percentage (%)
                                        </option>
                                        <option value="fixed" {{ old('discount_type', $product['discount_type']) == 'fixed' ? 'selected' : '' }}>
                                            Fixed Amount ($)
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="discount_value" class="form-label">Discount Value</label>
                                    <input type="number" class="form-control" id="discount_value" 
                                           name="discount_value" step="0.01" min="0" value="{{ old('discount_value', $product['discount_value']) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="offer_start" class="form-label">Offer Start Date</label>
                                    <input type="date" class="form-control" id="offer_start" name="offer_start" 
                                           value="{{ old('offer_start', $product['offer_start'] ? date('Y-m-d', strtotime($product['offer_start'])) : '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="offer_end" class="form-label">Offer End Date</label>
                                    <input type="date" class="form-control" id="offer_end" name="offer_end" 
                                           value="{{ old('offer_end', $product['offer_end'] ? date('Y-m-d', strtotime($product['offer_end'])) : '') }}">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ✅✅✅ NEW: Product Variants Management Card ✅✅✅ --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-palette"></i> Product Variants</h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addVariantModal">
                        <i class="fas fa-plus"></i> Add New Variant
                    </button>
                </div>
                <div class="card-body">
                    @php
                        $variants = \App\Models\ProductVariant::where('product_id', $product['id'])->get();
                    @endphp

                    @if($variants->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Attributes</th>
                                        <th>SKU</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variants as $variant)
                                    <tr>
                                        <td><strong>{{ $variant->name }}</strong></td>
                                        <td>
                                            @foreach($variant->attributes as $key => $value)
                                                <span class="badge bg-secondary">{{ ucfirst($key) }}: {{ ucfirst($value) }}</span>
                                            @endforeach
                                        </td>
                                        <td><code>{{ $variant->sku ?? 'N/A' }}</code></td>
                                        <td class="text-success fw-bold">${{ number_format($variant->price, 2) }}</td>
                                        <td>
                                            @if($variant->stock > 10)
                                                <span class="badge bg-success">{{ $variant->stock }}</span>
                                            @elseif($variant->stock > 0)
                                                <span class="badge bg-warning">{{ $variant->stock }}</span>
                                            @else
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($variant->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editVariant({{ $variant->id }}, '{{ $variant->name }}', {{ $variant->price }}, {{ $variant->stock }}, {{ $variant->is_active ? 'true' : 'false' }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('vendor.products.variants.delete', [$product['id'], $variant->id]) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this variant?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>No variants yet. Click "Add New Variant" to create product variations.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-eye"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.show', $product['id']) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View on Store
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                        <form action="{{ route('vendor.products.destroy', $product['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" 
                                    onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="fas fa-trash"></i> Delete Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6>Product Stats:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Views:</strong> {{ $product['view_count'] ?? 0 }}</li>
                        <li><strong>Orders:</strong> {{ $product['order_count'] ?? 0 }}</li>
                        <li><strong>Reviews:</strong> {{ $product['total_reviews'] ?? 0 }}</li>
                        <li><strong>Rating:</strong> {{ number_format($product['average_rating'] ?? 0, 1) }} / 5.0</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅✅✅ NEW: Add Variant Modal ✅✅✅ --}}
<div class="modal fade" id="addVariantModal" tabindex="-1" aria-labelledby="addVariantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="addVariantModalLabel">
                    <i class="fas fa-plus-circle"></i> Add New Variant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.products.variants.store', $product['id']) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Variant Name *</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g., Red - Large" required>
                            <small class="text-muted">Display name for this variant</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" name="sku" placeholder="e.g., RED-L-001">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" placeholder="e.g., Red">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Size</label>
                            <select class="form-select" name="size">
                                <option value="">None</option>
                                <option value="xs">XS</option>
                                <option value="s">S</option>
                                <option value="m">M</option>
                                <option value="l">L</option>
                                <option value="xl">XL</option>
                                <option value="xxl">XXL</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Other Attributes</label>
                            <input type="text" class="form-control" name="other_attributes" placeholder="material:cotton">
                            <small class="text-muted">Format: key:value</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price ($) *</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" class="form-control" name="stock" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Add Variant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅✅✅ NEW: Edit Variant Modal ✅✅✅ --}}
<div class="modal fade" id="editVariantModal" tabindex="-1" aria-labelledby="editVariantModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editVariantModalLabel">
                    <i class="fas fa-edit"></i> Edit Variant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVariantForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit-variant-id" name="variant_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Variant Name *</label>
                        <input type="text" class="form-control" id="edit-variant-name" name="name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price ($) *</label>
                            <input type="number" class="form-control" id="edit-variant-price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" class="form-control" id="edit-variant-stock" name="stock" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="edit-variant-status" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Variant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle offer fields
document.getElementById('has_offer').addEventListener('change', function() {
    document.getElementById('offer-fields').style.display = this.checked ? 'block' : 'none';
});

// Image preview
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.margin = '5px';
            img.style.border = '2px solid #ddd';
            img.style.borderRadius = '5px';
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(file);
    }
});

// ✅ Edit variant function
function editVariant(id, name, price, stock, isActive) {
    document.getElementById('edit-variant-id').value = id;
    document.getElementById('edit-variant-name').value = name;
    document.getElementById('edit-variant-price').value = price;
    document.getElementById('edit-variant-stock').value = stock;
    document.getElementById('edit-variant-status').value = isActive ? '1' : '0';
    
    // Update form action
    const form = document.getElementById('editVariantForm');
    form.action = `{{ route('vendor.products.variants.update', [$product['id'], 'VARIANT_ID']) }}`.replace('VARIANT_ID', id);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editVariantModal'));
    modal.show();
}
</script>
@endpush
@endsection