@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
            <p class="text-muted">Create a new product listing</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Product Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price ($) *</label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" step="0.01" min="0" value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                       id="stock" name="stock" min="0" value="{{ old('stock') }}" required>
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
                                    <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Product Images *</label>
                            <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                                   id="images" name="images[]" multiple accept="image/*" required>
                            <small class="text-muted">You can select multiple images. First image will be the main image.</small>
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="image-preview" class="mt-2"></div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-tag"></i> Offer Settings (Optional)</h5>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="has_offer" name="has_offer" value="1">
                            <label class="form-check-label" for="has_offer">
                                This product has a special offer
                            </label>
                        </div>

                        <div id="offer-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="discount_type" class="form-label">Discount Type</label>
                                    <select class="form-select" id="discount_type" name="discount_type">
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount ($)</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="discount_value" class="form-label">Discount Value</label>
                                    <input type="number" class="form-control" id="discount_value" 
                                           name="discount_value" step="0.01" min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="offer_start" class="form-label">Offer Start Date</label>
                                    <input type="date" class="form-control" id="offer_start" name="offer_start">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="offer_end" class="form-label">Offer End Date</label>
                                    <input type="date" class="form-control" id="offer_end" name="offer_end">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Create Product
                            </button>
                            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tips Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Product Tips</h6>
                </div>
                <div class="card-body">
                    <h6>Title Best Practices:</h6>
                    <ul class="small">
                        <li>Use clear, descriptive titles</li>
                        <li>Include key features</li>
                        <li>Keep it under 150 characters</li>
                    </ul>

                    <h6>Description Tips:</h6>
                    <ul class="small">
                        <li>Highlight key features and benefits</li>
                        <li>Include specifications</li>
                        <li>Mention materials and dimensions</li>
                        <li>Add care instructions if applicable</li>
                    </ul>

                    <h6>Image Guidelines:</h6>
                    <ul class="small">
                        <li>Use high-quality images</li>
                        <li>Show product from multiple angles</li>
                        <li>Use good lighting</li>
                        <li>Max file size: 2MB per image</li>
                    </ul>

                    <h6>Pricing Strategy:</h6>
                    <ul class="small">
                        <li>Research competitor prices</li>
                        <li>Factor in all costs</li>
                        <li>Consider seasonal pricing</li>
                    </ul>
                </div>
            </div>
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
</script>
@endpush
@endsection