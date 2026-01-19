@extends('layouts.app')

@section('title', 'Vendor Orders')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-shopping-bag"></i> Order Management</h2>
            <p class="text-muted">Track and manage customer orders</p>
        </div>
    </div>

    @php
        // Pre-filter orders by status
        $pendingOrders = [];
        $processingOrders = [];
        $shippedOrders = [];
        
        foreach($orders as $order) {
            if (isset($order['status'])) {
                if ($order['status'] === 'pending') {
                    $pendingOrders[] = $order;
                } elseif ($order['status'] === 'processing') {
                    $processingOrders[] = $order;
                } elseif ($order['status'] === 'shipped') {
                    $shippedOrders[] = $order;
                }
            }
        }
    @endphp

    <!-- Order Status Tabs -->
    <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                All Orders ({{ count($orders) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                Pending (<span id="pending-count">{{ count($pendingOrders) }}</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button">
                Processing (<span id="processing-count">{{ count($processingOrders) }}</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped" type="button">
                Shipped (<span id="shipped-count">{{ count($shippedOrders) }}</span>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="orderTabsContent">
        <!-- All Orders -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            @forelse($orders as $order)
                <div class="order-card-wrapper" data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}">
                    @include('vendor.partials.order-card', ['order' => $order])
                </div>
            @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                    <h4>No Orders Yet</h4>
                    <p class="text-muted">Orders will appear here when customers purchase your products.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pending Orders -->
        <div class="tab-pane fade" id="pending" role="tabpanel">
            @forelse($pendingOrders as $order)
                <div class="order-card-wrapper" data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}">
                    @include('vendor.partials.order-card', ['order' => $order])
                </div>
            @empty
            <div class="card empty-state">
                <div class="card-body text-center py-5">
                    <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                    <h4>No Pending Orders</h4>
                    <p class="text-muted">Pending orders will appear here.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Processing Orders -->
        <div class="tab-pane fade" id="processing" role="tabpanel">
            @forelse($processingOrders as $order)
                <div class="order-card-wrapper" data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}">
                    @include('vendor.partials.order-card', ['order' => $order])
                </div>
            @empty
            <div class="card empty-state">
                <div class="card-body text-center py-5">
                    <i class="fas fa-cog fa-4x text-muted mb-3"></i>
                    <h4>No Processing Orders</h4>
                    <p class="text-muted">Orders being processed will appear here.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Shipped Orders -->
        <div class="tab-pane fade" id="shipped" role="tabpanel">
            @forelse($shippedOrders as $order)
                <div class="order-card-wrapper" data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}">
                    @include('vendor.partials.order-card', ['order' => $order])
                </div>
            @empty
            <div class="card empty-state">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shipping-fast fa-4x text-muted mb-3"></i>
                    <h4>No Shipped Orders</h4>
                    <p class="text-muted">Shipped orders will appear here.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    // Use event delegation on document body
    document.body.addEventListener('click', function(e) {
        const button = e.target.closest('.update-status-btn');
        
        if (button && !button.disabled) {
            e.preventDefault();
            e.stopPropagation();
            handleStatusUpdate(button);
        }
    });
    
    // Main status update handler
    function handleStatusUpdate(button) {
        const orderId = button.dataset.orderId;
        const newStatus = button.dataset.status;
        const needsConfirm = button.dataset.confirm === 'true';
        
        // Confirm for cancel action
        if (needsConfirm && !confirm('Are you sure you want to cancel this order?')) {
            return;
        }
        
        const originalBtnHtml = button.innerHTML;
        
        // Disable all buttons for this order
        const buttonContainer = document.getElementById(`status-buttons-${orderId}`);
        if (!buttonContainer) return;
        
        const allButtons = buttonContainer.querySelectorAll('button');
        allButtons.forEach(b => b.disabled = true);
        
        // Show loading
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
        // Send AJAX request
        fetch(`/vendor/orders/${orderId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update ALL instances of this order across ALL tabs
                updateOrderStatusInAllTabs(orderId, newStatus);
                
                // Show success notification
                showNotification('success', data.message || 'Order status updated successfully!');
            } else {
                allButtons.forEach(b => b.disabled = false);
                button.innerHTML = originalBtnHtml;
                showNotification('error', data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            allButtons.forEach(b => b.disabled = false);
            button.innerHTML = originalBtnHtml;
            showNotification('error', 'An error occurred. Please try again.');
        });
    }
    
    // Update order status in ALL tabs
    function updateOrderStatusInAllTabs(orderId, newStatus) {
        // Find the order in "All Orders" tab (this is our source of truth)
        const allOrdersTab = document.getElementById('all');
        const sourceOrderWrapper = allOrdersTab.querySelector(`[data-order-id="${orderId}"]`);
        
        if (!sourceOrderWrapper) {
            console.error('Source order not found in All Orders tab');
            return;
        }
        
        const oldStatus = sourceOrderWrapper.dataset.orderStatus;
        
        // Update status in "All Orders" tab
        sourceOrderWrapper.dataset.orderStatus = newStatus;
        updateStatusBadge(orderId, newStatus);
        updateStatusButtons(orderId, newStatus);
        
        // Now handle the filtered tabs
        handleFilteredTabs(orderId, oldStatus, newStatus, sourceOrderWrapper);
        
        // Update tab counts
        updateTabCounts();
    }
    
    // Handle adding/removing from filtered tabs
    function handleFilteredTabs(orderId, oldStatus, newStatus, sourceOrderWrapper) {
        // Remove from old status tab (if not delivered/cancelled)
        if (oldStatus && oldStatus !== 'delivered' && oldStatus !== 'cancelled') {
            const oldTab = document.getElementById(oldStatus);
            if (oldTab) {
                const oldOrderWrapper = oldTab.querySelector(`[data-order-id="${orderId}"]`);
                if (oldOrderWrapper) {
                    // Fade out and remove
                    oldOrderWrapper.style.transition = 'opacity 0.3s';
                    oldOrderWrapper.style.opacity = '0';
                    setTimeout(() => {
                        oldOrderWrapper.remove();
                        checkAndShowEmptyState(oldStatus);
                    }, 300);
                }
            }
        }
        
        // Add to new status tab (if applicable)
        if (newStatus === 'pending' || newStatus === 'processing' || newStatus === 'shipped') {
            const newTab = document.getElementById(newStatus);
            if (newTab) {
                // Check if order already exists in new tab
                const existingOrder = newTab.querySelector(`[data-order-id="${orderId}"]`);
                if (!existingOrder) {
                    // Clone the order from "All Orders" tab
                    const clonedOrderWrapper = sourceOrderWrapper.cloneNode(true);
                    
                    // Make sure it has the correct status
                    clonedOrderWrapper.dataset.orderStatus = newStatus;
                    
                    // Remove any empty state if present
                    const emptyState = newTab.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.remove();
                    }
                    
                    // Add to the beginning of the tab (newest first)
                    const firstOrder = newTab.querySelector('.order-card-wrapper');
                    if (firstOrder) {
                        newTab.insertBefore(clonedOrderWrapper, firstOrder);
                    } else {
                        newTab.appendChild(clonedOrderWrapper);
                    }
                    
                    // Fade in animation
                    clonedOrderWrapper.style.opacity = '0';
                    clonedOrderWrapper.style.transition = 'opacity 0.3s';
                    setTimeout(() => {
                        clonedOrderWrapper.style.opacity = '1';
                    }, 10);
                }
            }
        }
        
        // If delivered or cancelled, just remove from filtered tabs
        if (newStatus === 'delivered' || newStatus === 'cancelled') {
            ['pending', 'processing', 'shipped'].forEach(tabName => {
                const tab = document.getElementById(tabName);
                if (tab) {
                    const orderWrapper = tab.querySelector(`[data-order-id="${orderId}"]`);
                    if (orderWrapper) {
                        orderWrapper.style.transition = 'opacity 0.3s';
                        orderWrapper.style.opacity = '0';
                        setTimeout(() => {
                            orderWrapper.remove();
                            checkAndShowEmptyState(tabName);
                        }, 300);
                    }
                }
            });
        }
    }
    
    // Check and show empty state if no orders in tab
    function checkAndShowEmptyState(tabName) {
        const tab = document.getElementById(tabName);
        if (!tab) return;
        
        const orders = tab.querySelectorAll('.order-card-wrapper');
        const emptyState = tab.querySelector('.empty-state');
        
        if (orders.length === 0 && !emptyState) {
            // Create empty state
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'card empty-state';
            
            let icon = 'fa-clock';
            let title = 'No Orders';
            
            if (tabName === 'pending') {
                icon = 'fa-clock';
                title = 'No Pending Orders';
            } else if (tabName === 'processing') {
                icon = 'fa-cog';
                title = 'No Processing Orders';
            } else if (tabName === 'shipped') {
                icon = 'fa-shipping-fast';
                title = 'No Shipped Orders';
            }
            
            emptyDiv.innerHTML = `
                <div class="card-body text-center py-5">
                    <i class="fas ${icon} fa-4x text-muted mb-3"></i>
                    <h4>${title}</h4>
                    <p class="text-muted">Orders will appear here when status changes.</p>
                </div>
            `;
            
            tab.appendChild(emptyDiv);
        } else if (orders.length > 0 && emptyState) {
            emptyState.remove();
        }
    }
    
    // Update tab counts
    function updateTabCounts() {
        // Count orders by status in "All Orders" tab
        const allOrders = document.getElementById('all').querySelectorAll('.order-card-wrapper');
        
        let pendingCount = 0;
        let processingCount = 0;
        let shippedCount = 0;
        
        allOrders.forEach(order => {
            const status = order.dataset.orderStatus;
            if (status === 'pending') pendingCount++;
            if (status === 'processing') processingCount++;
            if (status === 'shipped') shippedCount++;
        });
        
        // Update tab labels
        const pendingLabel = document.getElementById('pending-count');
        const processingLabel = document.getElementById('processing-count');
        const shippedLabel = document.getElementById('shipped-count');
        
        if (pendingLabel) pendingLabel.textContent = pendingCount;
        if (processingLabel) processingLabel.textContent = processingCount;
        if (shippedLabel) shippedLabel.textContent = shippedCount;
    }
    
    // Update status badge
    function updateStatusBadge(orderId, status) {
        // Update ALL badges for this order (one in each tab)
        const badges = document.querySelectorAll(`#status-badge-${orderId}`);
        
        let badgeClass = '';
        let badgeText = '';
        
        switch(status) {
            case 'delivered':
                badgeClass = 'bg-success';
                badgeText = 'Delivered';
                break;
            case 'shipped':
                badgeClass = 'bg-info';
                badgeText = 'Shipped';
                break;
            case 'processing':
                badgeClass = 'bg-primary';
                badgeText = 'Processing';
                break;
            case 'cancelled':
                badgeClass = 'bg-danger';
                badgeText = 'Cancelled';
                break;
            default:
                badgeClass = 'bg-secondary';
                badgeText = 'Pending';
        }
        
        badges.forEach(badge => {
            badge.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
        });
    }
    
    // Update status buttons
    function updateStatusButtons(orderId, status) {
        // Update ALL button groups for this order
        const containers = document.querySelectorAll(`#status-buttons-${orderId}`);
        
        containers.forEach(container => {
            const btnGroup = container.querySelector('.btn-group');
            if (!btnGroup) return;
            
            // If delivered or cancelled, hide the status update section
            if (status === 'delivered' || status === 'cancelled') {
                const statusUpdateSection = container.closest('[id^="status-update-section-"]');
                if (statusUpdateSection) {
                    statusUpdateSection.style.display = 'none';
                    const prevHr = statusUpdateSection.previousElementSibling;
                    if (prevHr && prevHr.tagName === 'HR') {
                        prevHr.style.display = 'none';
                    }
                }
                return;
            }
            
            // Clear and rebuild buttons
            btnGroup.innerHTML = '';
            
            if (status === 'pending') {
                btnGroup.innerHTML = `
                    <button type="button" class="btn btn-sm btn-primary update-status-btn"
                            data-order-id="${orderId}" data-status="processing">
                        <i class="fas fa-cog"></i> Mark as Processing
                    </button>
                    <button type="button" class="btn btn-sm btn-danger update-status-btn"
                            data-order-id="${orderId}" data-status="cancelled" data-confirm="true">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                `;
            } else if (status === 'processing') {
                btnGroup.innerHTML = `
                    <button type="button" class="btn btn-sm btn-info update-status-btn"
                            data-order-id="${orderId}" data-status="shipped">
                        <i class="fas fa-shipping-fast"></i> Mark as Shipped
                    </button>
                    <button type="button" class="btn btn-sm btn-danger update-status-btn"
                            data-order-id="${orderId}" data-status="cancelled" data-confirm="true">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                `;
            } else if (status === 'shipped') {
                btnGroup.innerHTML = `
                    <button type="button" class="btn btn-sm btn-success update-status-btn"
                            data-order-id="${orderId}" data-status="delivered">
                        <i class="fas fa-check-circle"></i> Mark as Delivered
                    </button>
                    <button type="button" class="btn btn-sm btn-danger update-status-btn"
                            data-order-id="${orderId}" data-status="cancelled" data-confirm="true">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                `;
            }
        });
    }
    
    // Show notification
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 150);
        }, 3000);
    }
})();
</script>
@endpush
@endsection