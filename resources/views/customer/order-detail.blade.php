@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <h2><i class="fas fa-receipt"></i> Order #{{ $order['id'] }}</h2>
            <p class="text-muted">Order placed on {{ date('F d, Y \a\t h:i A', strtotime($order['created_at'])) }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Order Items -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Order Items</h5>
                </div>
                <div class="card-body">
                    @foreach($order['items'] as $item)
                    <div class="row align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="col-md-2">
                            @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                <img src="{{ $item['product']['image_urls'][0] }}" alt="{{ $item['product']['name'] }}" 
                                     class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 100px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h5>{{ $item['product']['name'] }}</h5>
                            <p class="text-muted mb-1">{{ Str::limit($item['product']['description'], 100) }}</p>
                            @if(isset($item['product']['vendor']))
                                <small class="text-muted">
                                    <i class="fas fa-store"></i> Sold by: 
                                    <strong>{{ $item['product']['vendor']['shop_name'] }}</strong>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-2 text-center">
                            <p class="mb-0"><strong>Price:</strong></p>
                            ${{ number_format($item['final_price'], 2) }}
                        </div>
                        <div class="col-md-1 text-center">
                            <p class="mb-0"><strong>Qty:</strong></p>
                            {{ $item['quantity'] }}
                        </div>
                        <div class="col-md-3 text-end">
                            <p class="mb-0"><strong>Subtotal:</strong></p>
                            <h5 class="text-primary mb-2">${{ number_format($item['quantity'] * $item['final_price'], 2) }}</h5>
                            
                            {{-- Add Review Button if order is delivered --}}
                            @if($order['status'] === 'delivered')
                                <button class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#reviewModal{{ $item['product']['id'] }}">
                                    <i class="fas fa-star"></i> Write Review
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong class="text-primary">{{ $order['recipient_name'] }}</strong><br>
                        <i class="fas fa-phone text-primary"></i> <strong>Phone:</strong> {{ $order['phone'] }}<br>
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        {{ $order['address_line'] }}<br>
                        <span class="ms-4">{{ $order['city'] }}, {{ $order['state'] }} {{ $order['postal_code'] }}</span><br>
                        <span class="ms-4">{{ $order['country'] }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <!-- Order Status -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        @if($order['status'] === 'delivered')
                            <span class="badge bg-success p-3 fs-6">
                                <i class="fas fa-check-circle"></i> Delivered
                            </span>
                        @elseif($order['status'] === 'shipped')
                            <span class="badge bg-info p-3 fs-6">
                                <i class="fas fa-shipping-fast"></i> Shipped
                            </span>
                        @elseif($order['status'] === 'processing')
                            <span class="badge bg-primary p-3 fs-6">
                                <i class="fas fa-cog"></i> Processing
                            </span>
                        @elseif($order['status'] === 'cancelled')
                            <span class="badge bg-danger p-3 fs-6">
                                <i class="fas fa-times-circle"></i> Cancelled
                            </span>
                        @else
                            <span class="badge bg-secondary p-3 fs-6">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        @endif
                    </div>

                    <hr>

                    <div class="mb-2">
                        <strong><i class="fas fa-credit-card text-primary"></i> Payment Method:</strong>
                        <br>
                        <span class="ms-4">{{ ucfirst(str_replace('_', ' ', $order['payment_method'])) }}</span>
                    </div>

                    <div class="mb-2">
                        <strong><i class="fas fa-money-bill-wave text-success"></i> Payment Status:</strong>
                        <br>
                        <span class="ms-4">
                            @if($order['payment_status'] === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </span>
                    </div>
                    
                    @if(isset($order['notes']) && $order['notes'])
                        <hr>
                        <div>
                            <strong><i class="fas fa-sticky-note text-warning"></i> Order Notes:</strong>
                            <br>
                            <p class="ms-4 text-muted mb-0">{{ $order['notes'] }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Price Summary -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>${{ number_format($order['total_amount'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <strong>${{ number_format($order['tax_amount'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <strong class="text-success">FREE</strong>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong class="fs-5">Total:</strong>
                        <h4 class="text-success mb-0">${{ number_format($order['grand_total'], 2) }}</h4>
                    </div>

                    <div class="d-grid gap-2">
                        @if(in_array($order['status'], ['pending', 'processing']))
                            <button class="btn btn-outline-danger" disabled>
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Order Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Order Placed</strong>
                            <br>
                            <small class="text-muted">{{ date('M d, Y h:i A', strtotime($order['created_at'])) }}</small>
                        </div>
                        
                        @if(in_array($order['status'], ['processing', 'shipped', 'delivered']))
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Processing</strong>
                            <br>
                            <small class="text-muted">Order is being prepared</small>
                        </div>
                        @endif

                        @if(in_array($order['status'], ['shipped', 'delivered']))
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Shipped</strong>
                            <br>
                            <small class="text-muted">Order is on the way</small>
                        </div>
                        @endif

                        @if($order['status'] === 'delivered')
                        <div class="mb-0">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Delivered</strong>
                            <br>
                            <small class="text-muted">Order successfully delivered</small>
                        </div>
                        @endif

                        @if($order['status'] === 'cancelled')
                        <div class="mb-0">
                            <i class="fas fa-times-circle text-danger"></i>
                            <strong>Cancelled</strong>
                            <br>
                            <small class="text-muted">Order was cancelled</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Review Modals for each product (only if order is delivered) --}}
@if($order['status'] === 'delivered')
    @foreach($order['items'] as $item)
    <div class="modal fade" id="reviewModal{{ $item['product']['id'] }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-star text-warning"></i> 
                        Review: {{ Str::limit($item['product']['name'], 30) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('customer.reviews.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $item['product']['id'] }}">
                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                    
                    <div class="modal-body">
                        {{-- Product Info --}}
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                                <img src="{{ $item['product']['image_urls'][0] }}" 
                                     alt="{{ $item['product']['name'] }}" 
                                     style="width: 60px; height: 60px; object-fit: cover;" 
                                     class="rounded me-3">
                            @endif
                            <div>
                                <strong>{{ $item['product']['name'] }}</strong><br>
                                <small class="text-muted">Order #{{ $order['id'] }}</small>
                            </div>
                        </div>

                        {{-- Rating Stars --}}
                        <div class="mb-3">
                            <label class="form-label">Rating *</label>
                            <div class="rating-stars">
                                <input type="radio" name="rating" value="5" id="star5-{{ $item['product']['id'] }}" required>
                                <label for="star5-{{ $item['product']['id'] }}" class="star">
                                    <i class="fas fa-star"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="4" id="star4-{{ $item['product']['id'] }}">
                                <label for="star4-{{ $item['product']['id'] }}" class="star">
                                    <i class="fas fa-star"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="3" id="star3-{{ $item['product']['id'] }}">
                                <label for="star3-{{ $item['product']['id'] }}" class="star">
                                    <i class="fas fa-star"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="2" id="star2-{{ $item['product']['id'] }}">
                                <label for="star2-{{ $item['product']['id'] }}" class="star">
                                    <i class="fas fa-star"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="1" id="star1-{{ $item['product']['id'] }}">
                                <label for="star1-{{ $item['product']['id'] }}" class="star">
                                    <i class="fas fa-star"></i>
                                </label>
                            </div>
                            <small class="text-muted">Click to rate from 1 to 5 stars</small>
                        </div>

                        {{-- Review Comment --}}
                        <div class="mb-3">
                            <label for="comment{{ $item['product']['id'] }}" class="form-label">
                                Your Review *
                            </label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment{{ $item['product']['id'] }}" 
                                      name="comment" 
                                      rows="4" 
                                      placeholder="Share your experience with this product..." 
                                      required></textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 10 characters</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif

{{-- CSS for Star Rating --}}
<style>
/* Star Rating Styles */
.rating-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    font-size: 2rem;
    gap: 5px;
}

.rating-stars input[type="radio"] {
    display: none;
}

.rating-stars label {
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s, transform 0.2s;
}

.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #ffc107;
}

.rating-stars input[type="radio"]:checked ~ label {
    color: #ffc107;
}

.rating-stars label:hover {
    transform: scale(1.1);
}
</style>
@endsection