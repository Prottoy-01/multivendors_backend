@extends('layouts.app')

@section('title', 'Manage Coupons')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-ticket-alt"></i> Coupons Management</h2>
            <p class="text-muted">Create and manage discount coupons</p>
        </div>
    </div>

    <div class="row">
        <!-- Add Coupon Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Create New Coupon</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.coupons.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" name="code" required placeholder="e.g., SAVE20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ($)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Value *</label>
                            <input type="number" class="form-control" name="value" step="0.01" required>
                        </div>
                        <div class="mb-3">
    <label class="form-label">Min Purchase Amount</label>
    <input type="number" class="form-control" name="min_purchase" step="0.01">
</div>
                        <div class="mb-3">
                            <label class="form-label">Max Discount</label>
                            <input type="number" class="form-control" name="max_discount" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" class="form-control" name="usage_limit">
                        </div>
                        <div class="mb-3">
    <label class="form-label">Per User Limit</label>
    <input type="number" class="form-control" name="per_user_limit" value="5" min="1">
    <small class="text-muted">How many times each customer can use this coupon</small>
</div>
                        <div class="mb-3">
                            <label class="form-label">Valid From *</label>
                            <input type="date" class="form-control" name="valid_from" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valid Until *</label>
                            <input type="date" class="form-control" name="valid_until" required>
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
                    <h5 class="mb-0">All Coupons</h5>
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
                                        <th>Used</th>
                                        <th>Valid Until</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coupons as $coupon)
                                    <tr>
                                        <td><strong>{{ $coupon['code'] }}</strong></td>
                                        <td>
                                            @if($coupon['type'] === 'percentage')
                                                <span class="badge bg-info">Percentage</span>
                                            @else
                                                <span class="badge bg-primary">Fixed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($coupon['type'] === 'percentage')
                                                {{ $coupon['value'] }}%
                                            @else
                                                ${{ $coupon['value'] }}
                                            @endif
                                        </td>
                                        <td>{{ $coupon['usage_count'] ?? 0 }} / {{ $coupon['usage_limit'] ?? '∞' }}</td>
                                        <td>{{ date('M d, Y', strtotime($coupon['valid_until'])) }}</td>
                                        <td>
                                            @if(strtotime($coupon['valid_until']) > time())
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
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
@endsection