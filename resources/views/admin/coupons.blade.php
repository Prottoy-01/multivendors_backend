@extends('layouts.app')

@section('title', 'Manage Coupons')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-ticket-alt"></i> Coupons Management</h2>
            <p class="text-muted">Create and manage discount coupons with category targeting</p>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Validation Error:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Add Coupon Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Create New Coupon</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.coupons.store') }}" method="POST" id="coupon-form">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   required 
                                   placeholder="e.g., SAVE20">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Will be converted to uppercase</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    name="type" 
                                    required>
                                <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('value') is-invalid @enderror" 
                                   name="value" 
                                   value="{{ old('value') }}"
                                   step="0.01" 
                                   min="0"
                                   required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Min Purchase Amount</label>
                            <input type="number" 
                                   class="form-control @error('min_purchase') is-invalid @enderror" 
                                   name="min_purchase" 
                                   value="{{ old('min_purchase') }}"
                                   step="0.01"
                                   min="0">
                            @error('min_purchase')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Max Discount</label>
                            <input type="number" 
                                   class="form-control @error('max_discount') is-invalid @enderror" 
                                   name="max_discount" 
                                   value="{{ old('max_discount') }}"
                                   step="0.01"
                                   min="0">
                            @error('max_discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For percentage type only</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" 
                                   class="form-control @error('usage_limit') is-invalid @enderror" 
                                   name="usage_limit" 
                                   value="{{ old('usage_limit') }}"
                                   min="1">
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty for unlimited</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Per User Limit</label>
                            <input type="number" 
                                   class="form-control @error('per_user_limit') is-invalid @enderror" 
                                   name="per_user_limit" 
                                   value="{{ old('per_user_limit', 5) }}" 
                                   min="1">
                            @error('per_user_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">How many times each customer can use</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valid From <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('valid_from') is-invalid @enderror" 
                                   name="valid_from" 
                                   value="{{ old('valid_from') }}"
                                   required>
                            @error('valid_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('valid_until') is-invalid @enderror" 
                                   name="valid_until" 
                                   value="{{ old('valid_until') }}"
                                   required>
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ========================================
                            NEW: CATEGORY SELECTION SECTION
                            ======================================== --}}
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-tags"></i> Applicable Categories
                                </h6>
                            </div>
                            <div class="card-body">
                                {{-- Apply to All Radio --}}
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="applies_to_all" 
                                               id="applies_to_all_yes" 
                                               value="1"
                                               {{ old('applies_to_all', '0') == '1' ? 'checked' : '' }}
                                               onchange="toggleCategorySelector()">
                                        <label class="form-check-label" for="applies_to_all_yes">
                                            <strong>Apply to ALL Products</strong>
                                            <small class="text-muted d-block">Works on any product</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="applies_to_all" 
                                               id="applies_to_all_no" 
                                               value="0"
                                               {{ old('applies_to_all', '0') == '0' ? 'checked' : '' }}
                                               onchange="toggleCategorySelector()">
                                        <label class="form-check-label" for="applies_to_all_no">
                                            <strong>Specific Categories Only</strong>
                                            <small class="text-muted d-block">Select categories below</small>
                                        </label>
                                    </div>
                                </div>
                                
                                {{-- Category Checkboxes --}}
                                <div id="category-selector" style="{{ old('applies_to_all', '0') == '1' ? 'display: none;' : '' }}">
                                    <label class="form-label">
                                        Select Categories <span class="text-danger">*</span>
                                    </label>
                                    
                                    @if(!empty($categories))
                                        <div class="category-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                                            @foreach($categories as $category)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input category-checkbox" 
                                                       type="checkbox" 
                                                       name="category_ids[]" 
                                                       value="{{ $category['id'] }}" 
                                                       id="category_{{ $category['id'] }}"
                                                       {{ in_array($category['id'], old('category_ids', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="category_{{ $category['id'] }}">
                                                    <i class="fas fa-tag text-primary"></i> {{ $category['name'] }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning small mb-0">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            No categories available. Please create categories first.
                                        </div>
                                    @endif
                                    
                                    @error('category_ids')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                    
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Select at least one category
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Create Coupon
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coupons List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">All Coupons ({{ count($coupons) }})</h5>
                </div>
                <div class="card-body">
                    @if(count($coupons) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Categories</th>
                                        <th>Usage</th>
                                        <th>Valid Until</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coupons as $coupon)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $coupon['code'] }}</strong>
                                            @if(!empty($coupon['description']))
                                                <br><small class="text-muted">{{ Str::limit($coupon['description'], 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($coupon['type'] === 'percentage')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-percent"></i> Percentage
                                                </span>
                                            @else
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-dollar-sign"></i> Fixed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($coupon['type'] === 'percentage')
                                                <strong>{{ $coupon['value'] }}%</strong>
                                            @else
                                                <strong>${{ $coupon['value'] }}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- NEW: Display Categories --}}
                                            @if($coupon['applies_to_all'])
                                                <span class="badge bg-success">
                                                    <i class="fas fa-globe"></i> All Products
                                                </span>
                                            @else
                                                @if(!empty($coupon['categories']))
                                                    @foreach($coupon['categories'] as $category)
                                                        <span class="badge bg-secondary mb-1">
                                                            <i class="fas fa-tag"></i> {{ $category['name'] }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-exclamation-triangle"></i> No Categories
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{ $coupon['usage_count'] ?? 0 }} / 
                                                {{ $coupon['usage_limit'] ?? '∞' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{ date('M d, Y', strtotime($coupon['valid_until'])) }}</small>
                                        </td>
                                        <td>
                                            {{-- NEW: Active/Inactive Status --}}
                                            @if($coupon['is_active'] && strtotime($coupon['valid_until']) > time())
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Active
                                                </span>
                                            @elseif(!$coupon['is_active'])
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-ban"></i> Inactive
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Expired
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- NEW: Action Buttons --}}
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                {{-- Toggle Active/Inactive --}}
                                                <form action="{{ route('admin.coupons.toggle', $coupon['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm {{ $coupon['is_active'] ? 'btn-warning' : 'btn-success' }}"
                                                            title="{{ $coupon['is_active'] ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $coupon['is_active'] ? 'toggle-on' : 'toggle-off' }}"></i>
                                                        {{ $coupon['is_active'] ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                                
                                                {{-- Delete Button --}}
                                                <form action="{{ route('admin.coupons.delete', $coupon['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger"
                                                            {{ $coupon['usage_count'] > 0 ? 'disabled title="Cannot delete used coupons"' : '' }}>
                                                        <i class="fas fa-trash"></i> Delete
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
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> No coupons yet. Create your first coupon!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Category Selector Toggle --}}
@push('scripts')
<script>
// Toggle category selector based on applies_to_all selection
function toggleCategorySelector() {
    const appliesToAll = document.getElementById('applies_to_all_yes').checked;
    const categorySelector = document.getElementById('category-selector');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    
    if (appliesToAll) {
        categorySelector.style.display = 'none';
        // Uncheck all categories when "All Products" is selected
        categoryCheckboxes.forEach(cb => cb.checked = false);
    } else {
        categorySelector.style.display = 'block';
    }
}

// Form validation before submit
document.getElementById('coupon-form').addEventListener('submit', function(e) {
    const appliesToAll = document.getElementById('applies_to_all_yes').checked;
    
    if (!appliesToAll) {
        const checkedCategories = document.querySelectorAll('.category-checkbox:checked');
        
        if (checkedCategories.length === 0) {
            e.preventDefault();
            alert('Please select at least one category or choose "Apply to ALL Products"');
            return false;
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCategorySelector();
});
</script>
@endpush

@push('styles')
<style>
    .category-checkboxes {
        background-color: #f8f9fa;
    }
    .category-checkboxes .form-check:hover {
        background-color: #e9ecef;
        border-radius: 5px;
    }
    .btn-group-vertical .btn {
        margin-bottom: 2px;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush
@endsection