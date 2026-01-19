<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong>Order #{{ $order['id'] }}</strong>
            <span class="text-muted ms-3">
                <i class="fas fa-calendar"></i> {{ date('F d, Y', strtotime($order['created_at'])) }}
            </span>
        </div>
        <div>
            <span class="badge" id="status-badge-{{ $order['id'] }}">
                @if($order['status'] === 'delivered')
                    <span class="bg-success">Delivered</span>
                @elseif($order['status'] === 'shipped')
                    <span class="bg-info">Shipped</span>
                @elseif($order['status'] === 'processing')
                    <span class="bg-primary">Processing</span>
                @elseif($order['status'] === 'cancelled')
                    <span class="bg-danger">Cancelled</span>
                @else
                    <span class="bg-secondary">Pending</span>
                @endif
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Customer Information:</h6>
                <p class="mb-1"><strong>Name:</strong> {{ $order['user']['name'] ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $order['user']['email'] ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $order['phone'] ?? 'N/A' }}</p>
                
                <h6 class="mt-3">Shipping Address:</h6>
                <p class="mb-0">
                    <strong>Recipient:</strong> {{ $order['recipient_name'] ?? 'N/A' }}<br>
                    {{ $order['address_line'] ?? 'N/A' }}<br>
                    {{ $order['city'] ?? '' }}@if($order['state'] ?? ''), {{ $order['state'] }}@endif {{ $order['postal_code'] ?? '' }}<br>
                    {{ $order['country'] ?? '' }}
                </p>
            </div>

            <div class="col-md-6">
                <h6>Order Items:</h6>
                @foreach($order['items'] as $item)
                <div class="d-flex align-items-center mb-2">
                    @if(!empty($item['product']['image_urls']) && count($item['product']['image_urls']) > 0)
                        <img src="{{ $item['product']['image_urls'][0] }}" alt="{{ $item['product']['name'] }}" 
                             style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                    @endif
                    <div>
                        <strong>{{ $item['product']['name'] }}</strong><br>
                        <small>Qty: {{ $item['quantity'] }} × ${{ number_format($item['price'], 2) }}</small>
                    </div>
                </div>
                @endforeach
                
                <hr>
                <h5 class="text-end">Total: ${{ number_format($order['total_amount'], 2) }}</h5>
            </div>
        </div>

        @if($order['status'] !== 'delivered' && $order['status'] !== 'cancelled')
        <hr>
        <div class="text-end" id="status-update-section-{{ $order['id'] }}">
            <strong>Update Order Status:</strong>
            <div class="d-inline-block ms-2" id="status-buttons-{{ $order['id'] }}">
                <div class="btn-group" role="group">
                    @if($order['status'] === 'pending')
                        <button type="button" 
                                class="btn btn-sm btn-primary update-status-btn"
                                data-order-id="{{ $order['id'] }}"
                                data-status="processing">
                            <i class="fas fa-cog"></i> Mark as Processing
                        </button>
                    @endif
                    @if($order['status'] === 'processing')
                        <button type="button" 
                                class="btn btn-sm btn-info update-status-btn"
                                data-order-id="{{ $order['id'] }}"
                                data-status="shipped">
                            <i class="fas fa-shipping-fast"></i> Mark as Shipped
                        </button>
                    @endif
                    @if($order['status'] === 'shipped')
                        <button type="button" 
                                class="btn btn-sm btn-success update-status-btn"
                                data-order-id="{{ $order['id'] }}"
                                data-status="delivered">
                            <i class="fas fa-check-circle"></i> Mark as Delivered
                        </button>
                    @endif
                    <button type="button" 
                            class="btn btn-sm btn-danger update-status-btn"
                            data-order-id="{{ $order['id'] }}"
                            data-status="cancelled"
                            data-confirm="true">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>