@extends('layouts.app')

@section('title', 'Manage Vendors')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-store"></i> Vendors Management</h2>
            <p class="text-muted">Approve and manage vendor accounts</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Vendors</h5>
            <span class="badge bg-light text-dark">Total: {{ count($vendors) }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vendor Name</th>
                            <th>Shop Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                        <tr>
                            <td>#{{ $vendor['id'] }}</td>
                            <td>
                                <i class="fas fa-user-tie text-success"></i> 
                                {{ $vendor['user']['name'] ?? 'N/A' }}
                            </td>
                            <td>
                                <strong>{{ $vendor['shop_name'] }}</strong>
                                @if(!empty($vendor['shop_description']))
                                    <br><small class="text-muted">{{ Str::limit($vendor['shop_description'], 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $vendor['user']['email'] ?? 'N/A' }}</td>
                            <td>{{ $vendor['phone'] ?? 'N/A' }}</td>
                            <td>
                               @if($vendor['status'] === 'approved')
    <span class="badge bg-success">
        <i class="fas fa-check-circle"></i> Approved
    </span>
@elseif($vendor['status'] === 'rejected')
    <span class="badge bg-danger">
        <i class="fas fa-times-circle"></i> Rejected
    </span>
@else
    <span class="badge bg-warning text-dark">
        <i class="fas fa-clock"></i> Pending
    </span>
@endif
                            </td>
                            <td>{{ date('M d, Y', strtotime($vendor['created_at'])) }}</td>
                            <td>
                                @if($vendor['status'] !== 'approved')
                                    <form action="{{ route('admin.vendors.approve', $vendor['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this vendor?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Already approved</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No vendors found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Vendor Statistics -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                     <h3>{{ count(array_filter($vendors, fn($v) => $v['status'] === 'approved')) }}</h3>
                    <p class="text-muted">Approved Vendors</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                     <h3>{{ count(array_filter($vendors, fn($v) => $v['status'] === 'pending')) }}</h3>
                    <p class="text-muted">Pending Approval</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection