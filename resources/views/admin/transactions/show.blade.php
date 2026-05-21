@extends('admin.layout')
@section('admin-transaction-detail-content')
    <div class="container-xxl">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">

            {{-- Header --}}
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Transaction: <span class="text-primary">{{ $transaction->transaction_code }}</span></h4>
                        <span class="text-muted small">Created {{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            {{-- Vendor & Farmer Cards --}}
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-store me-2 text-primary"></i>Vendor (Buyer)</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $transaction->vendorProfile->user->name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $transaction->vendorProfile->user->phone ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Shop:</strong> {{ $transaction->vendorProfile->shop_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-tractor me-2 text-success"></i>Farmer (Seller)</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $transaction->farmerProfile->user->name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $transaction->farmerProfile->user->phone ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Farm:</strong> {{ $transaction->farmerProfile->farm_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Purchased Items --}}
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-shopping-basket me-2"></i>Purchased Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->items as $item)
                                        <tr>
                                            <td>{{ $item->product->title ?? 'Unknown Product' }}</td>
                                            <td class="text-end">{{ number_format($item->product->unit_price ?? 0, 2) }} {{ $transaction->currency }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format(($item->product->unit_price ?? 0) * $item->quantity, 2) }} {{ $transaction->currency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end text-success fs-6">
                                            {{ number_format($transaction->total_amount, 2) }} {{ $transaction->currency }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Repayment Schedule (only when approved/repaid) --}}
            @if ($installments->count() > 0)
                <div class="col-12 mb-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-calendar-alt me-2"></i>Repayment Schedule
                            </h5>
                            <span class="badge bg-white text-success fs-6">
                                {{ $installments->where('status', 'PAID')->count() }} / {{ $installments->count() }} Paid
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Due Date</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Penalty</th>
                                            <th class="text-end">Paid</th>
                                            <th class="text-end">Outstanding</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalOutstanding = 0; @endphp
                                        @foreach ($installments as $installment)
                                            @php
                                                $outstanding = ($installment->base_amount + $installment->penalty_amount) - $installment->amount_paid;
                                                $totalOutstanding += max($outstanding, 0);
                                                $isOverdue = $installment->status === 'PENDING' && \Carbon\Carbon::parse($installment->due_date)->isPast();
                                            @endphp
                                            <tr class="{{ $installment->status === 'PAID' ? 'table-success' : ($isOverdue ? 'table-danger' : '') }}">
                                                <td class="text-center fw-bold">{{ $installment->installment_number }}</td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($installment->due_date)->format('d M Y') }}
                                                    @if ($isOverdue)
                                                        <span class="badge bg-danger ms-1">Overdue</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ number_format($installment->base_amount, 2) }} {{ $transaction->currency }}</td>
                                                <td class="text-end">
                                                    @if ($installment->penalty_amount > 0)
                                                        <span class="text-danger">{{ number_format($installment->penalty_amount, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ number_format($installment->amount_paid, 2) }}</td>
                                                <td class="text-end fw-bold">
                                                    @if ($outstanding > 0)
                                                        <span class="text-danger">{{ number_format($outstanding, 2) }}</span>
                                                    @else
                                                        <span class="text-success">0.00</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($installment->status === 'PAID')
                                                        <span class="badge bg-success">PAID</span>
                                                    @elseif ($installment->status === 'PARTIALLY_PAID')
                                                        <span class="badge bg-warning text-dark">PARTIAL</span>
                                                    @elseif ($isOverdue)
                                                        <span class="badge bg-danger">OVERDUE</span>
                                                    @else
                                                        <span class="badge bg-secondary">PENDING</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if ($totalOutstanding > 0)
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="5" class="text-end">Total Outstanding:</th>
                                                <th class="text-end text-danger">{{ number_format($totalOutstanding, 2) }} {{ $transaction->currency }}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payment Log --}}
            @if ($paymentLogs->count() > 0)
                <div class="col-12 mb-4">
                    <div class="card border-secondary">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment Log</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                            <th>M-Pesa Reference</th>
                                            <th>Gateway</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paymentLogs as $log)
                                            <tr>
                                                <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                                <td>
                                                    @if ($log->payment_type === 'DISBURSEMENT_TO_FARMER')
                                                        <span class="badge bg-success">Disbursed → Farmer</span>
                                                    @else
                                                        <span class="badge bg-primary">Repayment ← Vendor</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold">{{ number_format($log->amount, 2) }} {{ $transaction->currency }}</td>
                                                <td><code>{{ $log->gateway_reference ?? '—' }}</code></td>
                                                <td>{{ $log->gateway_name ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Applicable Rule --}}
            @if ($applicableRule)
                <div class="col-12 mb-4">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0 text-white"><i class="fas fa-file-invoice-dollar me-2"></i>Applied Loan Rule</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-info">{{ $applicableRule->duration_days }}</div>
                                    <div class="text-muted small">Days Duration</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-info">{{ $applicableRule->installment_type }}</div>
                                    <div class="text-muted small">Installment Type</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-danger">
                                        {{ $applicableRule->penalty_value }}{{ $applicableRule->penalty_type === 'PERCENTAGE' ? '%' : ' '.$transaction->currency }}
                                    </div>
                                    <div class="text-muted small">Penalty ({{ $applicableRule->penalty_type }})</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-warning">{{ $applicableRule->grace_period_days }}</div>
                                    <div class="text-muted small">Grace Period (days)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Admin Action --}}
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white"><i class="fas fa-tasks me-2"></i>Admin Action</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6>Current Status:
                                    @if ($transaction->status === 'PENDING')
                                        <span class="badge bg-warning text-dark fs-6">PENDING</span>
                                    @elseif ($transaction->status === 'APPROVED')
                                        <span class="badge bg-success fs-6">APPROVED</span>
                                    @elseif ($transaction->status === 'REJECTED')
                                        <span class="badge bg-danger fs-6">REJECTED</span>
                                    @elseif ($transaction->status === 'REPAID')
                                        <span class="badge bg-primary fs-6">REPAID</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">{{ $transaction->status }}</span>
                                    @endif
                                </h6>

                                @if ($transaction->status === 'PENDING')
                                    @if ($applicableRule)
                                        <div class="alert alert-info mt-3 mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Approving will send <strong>{{ number_format($transaction->total_amount, 2) }} {{ $transaction->currency }}</strong>
                                            via M-Pesa to farmer <strong>{{ $transaction->farmerProfile->user->phone ?? 'N/A' }}</strong>.
                                            A <strong>{{ $applicableRule->installment_type }}</strong> repayment schedule
                                            over <strong>{{ $applicableRule->duration_days }} days</strong> will be generated for the vendor.
                                        </div>
                                    @else
                                        <div class="alert alert-danger mt-3 mb-0">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <strong>No payment rule found</strong> for amount
                                            {{ number_format($transaction->total_amount, 2) }} {{ $transaction->currency }}.
                                            Please <a href="{{ route('admin.rules.create') }}">create a rule</a> before approving.
                                        </div>
                                    @endif
                                @endif

                                @if ($transaction->admin_notes)
                                    <p class="mt-3 mb-0"><strong>Admin Notes:</strong> {{ $transaction->admin_notes }}</p>
                                @endif
                            </div>

                            <div class="col-md-4 text-end">
                                @if ($transaction->status === 'PENDING' && $applicableRule)
                                    <form action="{{ route('admin.transactions.approve', $transaction->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3 text-start">
                                            <label class="form-label fw-bold">Admin Notes (optional)</label>
                                            <textarea name="admin_notes" class="form-control" rows="2" placeholder="Reason for approval..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-lg w-100"
                                            onclick="return confirm('Approve this transaction and send M-Pesa payment to the farmer?')">
                                            <i class="fas fa-check-circle me-1"></i> Approve & Pay Farmer
                                        </button>
                                    </form>
                                @elseif ($transaction->status !== 'PENDING')
                                    <button class="btn btn-secondary btn-lg" disabled>
                                        <i class="fas fa-lock me-1"></i> Already Processed
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
