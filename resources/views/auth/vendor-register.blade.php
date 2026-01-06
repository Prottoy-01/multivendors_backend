@extends('layouts.app')

@section('title', 'Vendor Registration')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-store"></i> Become a Vendor</h4>
                    <p class="mb-0 mt-2 small">Sell your products on our platform</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('vendor.register') }}">
                        @csrf
                        
                        <h5 class="mb-3"><i class="fas fa-user"></i> Personal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <small class="text-muted">Minimum 8 characters</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-store"></i> Shop Information</h5>

                        <div class="mb-3">
                            <label for="shop_name" class="form-label">Shop Name *</label>
                            <input type="text" class="form-control @error('shop_name') is-invalid @enderror" 
                                   id="shop_name" name="shop_name" value="{{ old('shop_name') }}" required>
                            @error('shop_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shop_description" class="form-label">Shop Description</label>
                            <textarea class="form-control" id="shop_description" name="shop_description" 
                                      rows="3">{{ old('shop_description') }}</textarea>
                            <small class="text-muted">Tell customers about your shop</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Business Address *</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address') }}" required>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Your vendor account will be reviewed by our admin team before activation.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-store"></i> Submit Vendor Application
                            </button>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection