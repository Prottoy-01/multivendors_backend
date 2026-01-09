@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Failed Card --}}
            <div class="card border-danger">
                <div class="card-header bg-danger text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <br>
                        Payment Failed
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5 class="text-danger">Oops! Something went wrong</h5>
                        <p class="text-muted">We were unable to process your payment. Please try again.</p>
                    </div>
                    
                    {{-- Error Message --}}
                    @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> {{ session('error') }}
                    </div>
                    @endif
                    
                    <hr>
                    
                    {{-- Common Reasons --}}
                    <h6 class="text-muted mb-3"><i class="fas fa-info-circle"></i> Common Reasons for Payment Failure:</h6>
                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <i class="fas fa-credit-card text-danger"></i>
                            <strong>Insufficient Funds:</strong> Your card doesn't have enough balance
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-ban text-danger"></i>
                            <strong>Card Declined:</strong> Your bank declined the transaction
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-clock text-danger"></i>
                            <strong>Expired Card:</strong> Your card has expired
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-keyboard text-danger"></i>
                            <strong>Incorrect Details:</strong> Card number, expiry, or CVC is wrong
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-globe text-danger"></i>
                            <strong>International Restrictions:</strong> Card not enabled for online/international payments
                        </li>
                    </ul>
                    
                    {{-- What to Do --}}
                    <h6 class="text-muted mb-3"><i class="fas fa-lightbulb"></i> What You Can Do:</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-redo fa-2x text-primary mb-3"></i>
                                    <h6>Try Again</h6>
                                    <p class="small text-muted mb-3">Double-check your card details and try the payment again</p>
                                    <a href="{{ route('customer.checkout') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back to Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-credit-card fa-2x text-success mb-3"></i>
                                    <h6>Use Different Card</h6>
                                    <p class="small text-muted mb-3">Try using a different payment method or card</p>
                                    <a href="{{ route('customer.checkout') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-sync"></i> Change Payment Method
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-phone fa-2x text-warning mb-3"></i>
                                    <h6>Contact Your Bank</h6>
                                    <p class="small text-muted mb-3">Your bank can explain why the transaction was declined</p>
                                    <button class="btn btn-warning btn-sm" disabled>
                                        <i class="fas fa-building"></i> Call Your Bank
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-2x text-info mb-3"></i>
                                    <h6>Cash on Delivery</h6>
                                    <p class="small text-muted mb-3">Pay when you receive your order at your doorstep</p>
                                    <a href="{{ route('customer.checkout') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-hand-holding-usd"></i> Choose COD
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    {{-- Test Card Info (For Demo) --}}
                    <div class="alert alert-info">
                        <i class="fas fa-flask"></i>
                        <strong>Testing? Use These Test Cards:</strong>
                        <div class="mt-2">
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead>
                                    <tr>
                                        <th>Card Number</th>
                                        <th>Expiry</th>
                                        <th>CVC</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-success">
                                        <td><code>4242 4242 4242 4242</code></td>
                                        <td>Any future</td>
                                        <td>Any</td>
                                        <td><span class="badge bg-success">✓ Success</span></td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td><code>4000 0000 0000 0002</code></td>
                                        <td>Any future</td>
                                        <td>Any</td>
                                        <td><span class="badge bg-danger">✗ Declined</span></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><code>4000 0000 0000 9995</code></td>
                                        <td>Any future</td>
                                        <td>Any</td>
                                        <td><span class="badge bg-warning">⚠ Insufficient Funds</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- Help Section --}}
                    <div class="alert alert-secondary">
                        <i class="fas fa-question-circle"></i>
                        <strong>Need Help?</strong><br>
                        <small>
                            If you continue to experience issues, please contact our support team:<br>
                            <i class="fas fa-envelope"></i> support@yourstore.com | 
                            <i class="fas fa-phone"></i> +1 (555) 123-4567
                        </small>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="text-center mt-4">
                        <a href="{{ route('customer.checkout') }}" class="btn btn-danger btn-lg me-2">
                            <i class="fas fa-redo"></i> Try Payment Again
                        </a>
                        <a href="{{ route('customer.cart') }}" class="btn btn-outline-secondary btn-lg me-2">
                            <i class="fas fa-shopping-cart"></i> View Cart
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                    
                    {{-- Cart Saved Message --}}
                    <div class="alert alert-success mt-4 mb-0">
                        <i class="fas fa-check-circle"></i>
                        <strong>Don't worry!</strong> Your cart items are safe and saved. 
                        You can try the payment again whenever you're ready.
                    </div>
                </div>
            </div>
            
            {{-- Security Note --}}
            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="fas fa-lock"></i> All transactions are secured with 256-bit SSL encryption<br>
                    <i class="fas fa-shield-alt"></i> We never store your full card details
                </p>
            </div>
        </div>
    </div>
</div>
@endsection