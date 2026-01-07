@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-user"></i> My Profile</h2>
            <p class="text-muted">Manage your account information</p>
        </div>
    </div>

    <div class="row">
        <!-- Profile Info -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('customer.profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user['name']) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" 
                                   value="{{ $user['email'] }}" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user['phone'] ?? '') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" name="bio" rows="3">{{ old('bio', $user['bio'] ?? '') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Saved Addresses -->
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Saved Addresses</h5>
                    <a href="{{ route('customer.addresses') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
                <div class="card-body">
                    @if(count($addresses) > 0)
                        @foreach($addresses as $address)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="text-primary">{{ $address['recipient_name'] }}</strong>
                                    @if($address['is_default'])
                                        <span class="badge bg-success ms-2">Default</span>
                                    @endif
                                    <p class="mb-0 mt-2">
                                        <i class="fas fa-phone text-primary"></i> 
                                        <strong>Phone:</strong> {{ $address['phone'] }}
                                    </p>
                                    <p class="mb-0 mt-1">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        {{ $address['address_line'] }}<br>
                                        <span class="ms-4">{{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}</span><br>
                                        <span class="ms-4">{{ $address['country'] }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No addresses saved yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                    <h4>{{ $user['name'] }}</h4>
                    <p class="text-muted">{{ $user['email'] }}</p>
                    <span class="badge bg-primary">Customer</span>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-link"></i> Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('customer.orders') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-box text-primary"></i> My Orders
                    </a>
                    <a href="{{ route('customer.wishlist') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart text-danger"></i> My Wishlist
                    </a>
                    <a href="{{ route('customer.cart') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart text-success"></i> Shopping Cart
                    </a>
                    <a href="{{ route('customer.addresses') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt text-warning"></i> Manage Addresses
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection