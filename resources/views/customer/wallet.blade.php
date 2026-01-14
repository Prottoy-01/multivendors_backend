@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">My Account</h5>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('customer.orders') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                        <a href="{{ route('customer.wallet') }}" class="list-group-item list-group-item-action active">
                            <i class="fas fa-wallet"></i> My Wallet
                        </a>
                        <a href="{{ route('customer.wishlist') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                        <a href="{{ route('customer.profile') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-md-9">
            {{-- Wallet Balance Card --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="text-white">
                        <i class="fas fa-wallet fa-3x mb-3"></i>
                        <h2 class="mb-2">Wallet Balance</h2>
                        <h1 class="display-3 fw-bold mb-0">
                            ${{ number_format(Auth::user()->wallet_balance, 2) }}
                        </h1>
                        <p class="mt-3 mb-0">
                            <small>Available for your next purchase</small>
                        </p>
                    </div>
                </div>
            </div>

            {{-- How to Use Wallet --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-primary"></i> How to Use Your Wallet
                    </h5>
                    <ul class="mb-0">
                        <li>Your wallet balance can be used for any future purchases</li>
                        <li>Refunds from cancelled orders are automatically added to your wallet</li>
                        <li>Wallet balance will be automatically applied at checkout</li>
                    </ul>
                </div>
            </div>

            {{-- Refund History --}}
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Refund History
                    </h5>
                </div>
                <div class="card-body">
                    @if($refunds->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Order #</th>
                                        <th>Cancelled At</th>
                                        <th>Original Amount</th>
                                        <th>Refund %</th>
                                        <th>Refund Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($refunds as $refund)
                                        <tr>
                                            <td>
                                                <small>{{ $refund->created_at->format('M d, Y') }}</small><br>
                                                <small class="text-muted">{{ $refund->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('customer.orders.detail', $refund->order_id) }}" class="text-decoration-none">
                                                    {{ $refund->order->order_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($refund->order_status_at_cancellation) }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($refund->original_amount, 2) }}</td>
                                            <td>
                                                @if($refund->refund_percentage == 100)
                                                    <span class="badge bg-success">100%</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $refund->refund_percentage }}%</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    +${{ number_format($refund->refund_amount, 2) }}
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Completed
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No refund history yet</p>
                            <p class="text-muted">
                                <small>Refunds from cancelled orders will appear here</small>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.list-group-item-action:hover {
    background-color: #f8f9fa;
}

.list-group-item-action.active {
    background-color: #667eea;
    border-color: #667eea;
}

.table td {
    vertical-align: middle;
}
</style>
@endsection