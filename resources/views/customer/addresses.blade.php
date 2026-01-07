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
                            <label for="recipient_name" class="form-label">Recipient Name *</label>
                            <input type="text" class="form-control @error('recipient_name') is-invalid @enderror" 
                                   id="recipient_name" name="recipient_name" 
                                   value="{{ old('recipient_name') }}"
                                   placeholder="Full name of recipient" required>
                            @error('recipient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" 
                                   value="{{ old('phone') }}"
                                   placeholder="Contact number" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address_line" class="form-label">Address *</label>
                            <textarea class="form-control @error('address_line') is-invalid @enderror" 
                                      id="address_line" name="address_line" 
                                      rows="2" placeholder="Complete street address" required>{{ old('address_line') }}</textarea>
                            @error('address_line')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city') }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State *</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                       id="state" name="state" value="{{ old('state') }}" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code *</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country') }}" required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_default" 
                                   name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
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
                                        {{ $address['recipient_name'] }}
                                        @if($address['is_default'])
                                            <span class="badge bg-success">Default</span>
                                        @endif
                                    </h5>
                                    <p class="mb-0">
                                        <i class="fas fa-phone text-primary"></i> 
                                        <strong>Phone:</strong> {{ $address['phone'] }}
                                    </p>
                                    <p class="mb-0 mt-2">
                                        <i class="fas fa-map-marker-alt text-danger"></i> 
                                        {{ $address['address_line'] }}<br>
                                        <span class="ms-4">{{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}</span><br>
                                        <span class="ms-4">{{ $address['country'] }}</span>
                                    </p>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary mb-1" disabled>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" disabled>
                                        <i class="fas fa-trash"></i> Delete
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