@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
            <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        
        {{-- Basic Information --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>
                                    {{ $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" name="description" rows="4" required>{{ old('description') }}</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Base Price * ($)</label>
                        <input type="number" class="form-control" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
                        <small class="text-muted">Base price (can be overridden by variants)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Base Stock *</label>
                        <input type="number" class="form-control" name="stock" value="{{ old('stock', 0) }}" min="0" required>
                        <small class="text-muted">Total stock (if no variants)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Product Images</label>
                        <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                        <small class="text-muted">Select multiple images</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Offers/Discounts --}}
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-tags"></i> Offers & Discounts (Optional)</h5>
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_offer" id="has_offer" value="1" {{ old('has_offer') ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_offer">
                        This product has an offer/discount
                    </label>
                </div>
                
                <div id="offer-fields" style="display: {{ old('has_offer') ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Discount Type</label>
                            <select class="form-select" name="discount_type">
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" class="form-control" name="discount_value" value="{{ old('discount_value') }}" step="0.01" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Offer Start Date</label>
                            <input type="date" class="form-control" name="offer_start" value="{{ old('offer_start') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Offer End Date</label>
                            <input type="date" class="form-control" name="offer_end" value="{{ old('offer_end') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Variants --}}
        <div class="card mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-palette"></i> Product Variants (Optional)</h5>
                <button type="button" class="btn btn-light btn-sm" onclick="addVariantRow()">
                    <i class="fas fa-plus"></i> Add Variant
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle"></i> Add variants if your product comes in different colors, sizes, or other options.
                    Leave empty if product has no variations.
                </p>
                
                <div id="variants-container">
                    {{-- Variant rows will be added here dynamically --}}
                </div>
                
                <div id="no-variants-message" class="text-center text-muted py-4">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p>No variants added yet. Click "Add Variant" to create product variations.</p>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Create Product
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
.variant-row {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}
.variant-row:hover {
    border-color: #0d6efd;
}
.remove-variant-btn {
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>
@endpush

@push('scripts')
<script>
let variantIndex = 0;

// Toggle offer fields
document.getElementById('has_offer')?.addEventListener('change', function() {
    document.getElementById('offer-fields').style.display = this.checked ? 'block' : 'none';
});

// Add variant row
function addVariantRow() {
    const container = document.getElementById('variants-container');
    const noVariantsMsg = document.getElementById('no-variants-message');
    
    // Hide no variants message
    if (noVariantsMsg) {
        noVariantsMsg.style.display = 'none';
    }
    
    const variantHtml = `
        <div class="variant-row position-relative" id="variant-${variantIndex}">
            <button type="button" class="btn btn-danger btn-sm remove-variant-btn" onclick="removeVariant(${variantIndex})">
                <i class="fas fa-trash"></i>
            </button>
            
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h6 class="text-primary"><i class="fas fa-cube"></i> Variant #${variantIndex + 1}</h6>
                </div>
                
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Color</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="variants[${variantIndex}][color]" 
                           placeholder="e.g., Red, Blue">
                </div>
                
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Size</label>
                    <select class="form-select form-select-sm" name="variants[${variantIndex}][size]">
                        <option value="">None</option>
                        <option value="xs">XS</option>
                        <option value="s">S</option>
                        <option value="m">M</option>
                        <option value="l">L</option>
                        <option value="xl">XL</option>
                        <option value="xxl">XXL</option>
                    </select>
                </div>
                
                <div class="col-md-2 mb-2">
                    <label class="form-label small">SKU</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="variants[${variantIndex}][sku]" 
                           placeholder="SKU-001">
                </div>
                
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Price ($) *</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="variants[${variantIndex}][price]" 
                           step="0.01" min="0" required>
                </div>
                
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Stock *</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="variants[${variantIndex}][stock]" 
                           min="0" required>
                </div>
                
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Variant Name *</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="variants[${variantIndex}][name]" 
                           placeholder="Red - Large" required>
                    <small class="text-muted">Display name for this variant</small>
                </div>
                
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Other Attributes</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="variants[${variantIndex}][other_attributes]" 
                           placeholder="material:cotton">
                    <small class="text-muted">Format: key:value,key:value</small>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', variantHtml);
    variantIndex++;
}

// Remove variant row
function removeVariant(index) {
    const variantRow = document.getElementById(`variant-${index}`);
    if (variantRow) {
        variantRow.remove();
    }
    
    // Show no variants message if no variants left
    const container = document.getElementById('variants-container');
    const noVariantsMsg = document.getElementById('no-variants-message');
    
    if (container.children.length === 0 && noVariantsMsg) {
        noVariantsMsg.style.display = 'block';
    }
}

// Form validation
document.getElementById('product-form').addEventListener('submit', function(e) {
    const variants = document.querySelectorAll('.variant-row');
    if (variants.length > 0) {
        let valid = true;
        variants.forEach(variant => {
            const inputs = variant.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                if (!input.value) {
                    valid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required variant fields.');
        }
    }
});
</script>
@endpush
@endsection