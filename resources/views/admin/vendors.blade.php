@extends('layouts.app')

@section('title', 'Manage Vendors')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-store"></i> Manage Vendors</h2>
            <p class="text-muted">View and manage all vendor accounts</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Vendors ({{ count($vendors) }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Shop Name</th>
                            <th>Owner</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor['id'] }}</td>
                            <td>
                                <strong>{{ $vendor['shop_name'] }}</strong>
                            </td>
                            <td>{{ $vendor['user']['name'] ?? 'N/A' }}</td>
                            <td>{{ $vendor['user']['email'] ?? 'N/A' }}</td>
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
                                @if($vendor['status'] === 'pending')
                                    {{-- Approve Button --}}
                                    <form action="{{ route('admin.vendors.approve', $vendor['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Are you sure you want to approve this vendor?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    
                                    {{-- Reject Button --}}
                                    <form action="{{ route('admin.vendors.reject', $vendor['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to reject this vendor?')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                @elseif($vendor['status'] === 'approved')
                                    {{-- Reject Button (to revoke approval) --}}
                                    <form action="{{ route('admin.vendors.reject', $vendor['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm" 
                                                onclick="return confirm('Are you sure you want to reject this approved vendor?')">
                                            <i class="fas fa-ban"></i> Revoke
                                        </button>
                                    </form>
                                @elseif($vendor['status'] === 'rejected')
                                    {{-- Approve Button (to re-approve rejected vendor) --}}
                                    <form action="{{ route('admin.vendors.approve', $vendor['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Are you sure you want to approve this rejected vendor?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                @endif
                                
                                {{-- ✅ View Details Button - NOW WORKING! --}}
                                <a href="{{ route('admin.vendors.details', $vendor['id']) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No vendors found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                    <h3>{{ collect($vendors)->where('status', 'pending')->count() }}</h3>
                    <p class="text-muted">Pending Approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h3>{{ collect($vendors)->where('status', 'approved')->count() }}</h3>
                    <p class="text-muted">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h3>{{ collect($vendors)->where('status', 'rejected')->count() }}</h3>
                    <p class="text-muted">Rejected</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection