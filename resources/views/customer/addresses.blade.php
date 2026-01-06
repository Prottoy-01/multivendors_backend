@extends('layouts.app')

@section('title', 'Manage Addresses')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-map-marker-alt"></i> Manage Addresses</h2>
            <p class="text-muted">Add and manage your shipping addresses</p>
        </div>
    </div>

    <div class="row">
        <!-- Add Address Form -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Add New Address</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer.addresses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="label" class="form-label">Address Label *</label>
                            <input type="text" class="form-control" id="label" name="label" 
                                   placeholder="e.g., Home, Office" required>
                        </div>
                        <div class="mb-3">
                            <label for="address_line_1" class="form-label">Address Line 1 *</label>
                            <input type="text" class="form-control" id="address_line_1" 
                                   name="address_line_1" required>
                        </div>
                        <div class="mb-3">
                            <label for="address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="address_line_2" 
                                   name="address_line_2">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State *</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" id="postal_code" 
                                       name="postal_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <input type="text" class="form-control" id="country" name="country" required>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_default" 
                                   name="is_default" value="1">
                            <label class="form-check-label" for="is_default">
                                Set as default address
                            </label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Saved Addresses -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Your Addresses ({{ count($addresses) }})</h5>
                </div>
                <div class="card-body">
                    @forelse($addresses as $address)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>
                                        {{ $address['label'] }}
                                        @if($address['is_default'])
                                            <span class="badge bg-success">Default</span>
                                        @endif
                                    </h5>
                                    <p class="mb-0">
                                        {{ $address['address_line_1'] }}<br>
                                        @if($address['address_line_2']){{ $address['address_line_2'] }}<br>@endif
                                        {{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}<br>
                                        {{ $address['country'] }}
                                    </p>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No addresses saved yet. Add your first address using the form.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection