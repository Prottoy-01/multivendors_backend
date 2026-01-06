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
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer.profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ $user['name'] }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" 
                                   value="{{ $user['email'] }}" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="{{ $user['phone'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3">{{ $user['bio'] ?? '' }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Saved Addresses -->
            <div class="card mt-4">
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
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $address['label'] }}</strong>
                                    @if($address['is_default'])
                                        <span class="badge bg-success ms-2">Default</span>
                                    @endif
                                    <br>
                                    {{ $address['address_line_1'] }}
                                    @if($address['address_line_2']), {{ $address['address_line_2'] }}@endif
                                    <br>
                                    {{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}
                                    <br>
                                    {{ $address['country'] }}
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
                    <h6 class="mb-0">Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('customer.orders') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-box"></i> My Orders
                    </a>
                    <a href="{{ route('customer.wishlist') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart"></i> My Wishlist
                    </a>
                    <a href="{{ route('customer.cart') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart"></i> Shopping Cart
                    </a>
                    <a href="{{ route('customer.addresses') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt"></i> Manage Addresses
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection