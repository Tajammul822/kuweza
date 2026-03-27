@extends('admin.layout')
@section('admin-transaction-detail-content')
    <div class="container-xxl">
        <div class="row">
            {{-- Header Section --}}
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Transaction Details: <span class="text-primary">{{ $transaction->transaction_code }}</span></h4>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>

            {{-- Vendor & Farmer Info Cards --}}
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Vendor (Buyer) Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $transaction->vendorProfile->user->name ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $transaction->vendorProfile->user->email ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $transaction->vendorProfile->user->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Farmer (Seller) Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $transaction->farmerProfile->user->name ?? 'N/A' }}</p>
                        <p><strong>Farm Name:</strong> {{ $transaction->farmerProfile->farm_name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $transaction->farmerProfile->user->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Purchased Items Table --}}
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Purchased Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Unit Price (At Purchase)</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->items as $item)
                                        <tr>
                                            <td>{{ $item->product->title ?? 'Unknown Product' }}</td>
                                            <td>{{ $item->unit_price }} {{ $transaction->currency }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->price * $item->quantity }} {{ $transaction->currency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total Amount:</th>
                                        <th><h5 class="text-success mb-0">{{ $transaction->total_amount }} {{ $transaction->currency }}</h5></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approval / Action Section --}}
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white">Admin Action</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6>Current Status: 
                                    @if ($transaction->status == 'PENDING')
                                        <span class="badge bg-warning text-dark fs-6">PENDING</span>
                                    @else
                                        <span class="badge bg-info fs-6">{{ $transaction->status }}</span>
                                    @endif
                                </h6>
                                
                                @if($applicableRule)
                                    <div class="alert alert-info mt-3">
                                        <strong>Applicable Rule Found!</strong><br>
                                        System will automatically apply a <strong>{{ $applicableRule->duration_days }} Days</strong> repayment timeline 
                                        with <strong>{{ $applicableRule->installment_type }}</strong> installments. 
                                        Penalty will be <strong>{{ $applicableRule->penalty_value }} ({{ $applicableRule->penalty_type }})</strong>.
                                    </div>
                                @else
                                    <div class="alert alert-danger mt-3">
                                        <strong>No Rule Found!</strong> Please create a payment rule in the settings for the amount 
                                        ({{ $transaction->total_amount }}) before approving.
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4 text-end">
                                @if ($transaction->status == 'PENDING' && $applicableRule)
                                    <form action="{{ route('admin.transactions.approve', $transaction->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Are you sure you want to approve this transaction and pay the farmer?')">
                                            <i class="fas fa-check-circle"></i> Approve & Disburse Funds
                                        </button>
                                    </form>
                                @elseif($transaction->status != 'PENDING')
                                    <button class="btn btn-secondary btn-lg" disabled>Already Processed</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection