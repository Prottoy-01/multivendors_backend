@extends('layouts.app')

@section('title', 'Shop Analytics')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> Shop Analytics</h2>
            <p class="text-muted">Track your shop's performance</p>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-2x text-primary mb-2"></i>
                    <h3>{{ $analytics['total_products'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Total Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                    <h3>{{ $analytics['total_orders'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x text-info mb-2"></i>
                    <h3>${{ number_format($analytics['total_revenue'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                    <h3>${{ number_format($analytics['avg_order_value'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">Avg Order Value</p>
                </div>
            </div>
        </div>
    </div>

    <!-- More Analytics -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Performance Overview</h5>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted py-5">
                        <i class="fas fa-chart-bar fa-4x mb-3 d-block"></i>
                        Detailed analytics coming soon!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection