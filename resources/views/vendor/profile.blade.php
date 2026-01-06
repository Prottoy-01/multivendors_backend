@extends('layouts.app')

@section('title', 'Shop Settings')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-store"></i> Shop Settings</h2>
            <p class="text-muted">Manage your shop information</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Shop Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.profile.update') }}" method="POST">
                        @csrf
                        
                        <h6 class="mb-3">Personal Information</h6>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ $vendor['name'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" 
                                   value="{{ $vendor['email'] ?? '' }}" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="{{ $vendor['phone'] ?? '' }}">
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Shop Information</h6>

                        <div class="mb-3">
                            <label for="shop_name" class="form-label">Shop Name</label>
                            <input type="text" class="form-control" id="shop_name" name="shop_name" 
                                   value="{{ $vendor['vendor']['shop_name'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="shop_description" class="form-label">Shop Description</label>
                            <textarea class="form-control" id="shop_description" name="shop_description" rows="4">{{ $vendor['vendor']['shop_description'] ?? '' }}</textarea>
                            <small class="text-muted">Tell customers about your shop and what makes it special</small>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Business Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2">{{ $vendor['vendor']['address'] ?? '' }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Shop Information
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="fas fa-store-alt fa-4x text-primary mb-3"></i>
                    <h4>{{ $vendor['vendor']['shop_name'] ?? 'Your Shop' }}</h4>
                    <p class="text-muted">{{ $vendor['email'] ?? '' }}</p>
                    <span class="badge bg-success">{{ ucfirst($vendor['vendor']['status'] ?? 'active') }}</span>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('vendor.products.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-box"></i> My Products
                    </a>
                    <a href="{{ route('vendor.orders') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag"></i> Orders
                    </a>
                    <a href="{{ route('vendor.analytics') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-line"></i> Analytics
                    </a>
                    <a href="{{ route('vendor.products.create') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection