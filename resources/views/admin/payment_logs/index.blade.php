@extends('admin.layout')
@section('admin-payment-logs-content')
<div class="container-xxl">

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment Logs</h4>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:54px;height:54px;flex-shrink:0">
                        <i class="fas fa-arrow-up text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Disbursed to Farmers</div>
                        <div class="fs-5 fw-bold text-success">
                            {{ number_format($totalDisbursed, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:54px;height:54px;flex-shrink:0">
                        <i class="fas fa-arrow-down text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Repaid by Vendors</div>
                        <div class="fs-5 fw-bold text-primary">
                            {{ number_format($totalRepaid, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:54px;height:54px;flex-shrink:0">
                        <i class="fas fa-hourglass-half text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Outstanding Balance</div>
                        <div class="fs-5 fw-bold text-danger">
                            {{ number_format($outstanding, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payment-logs.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payment Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="DISBURSEMENT_TO_FARMER"
                            {{ request('type') === 'DISBURSEMENT_TO_FARMER' ? 'selected' : '' }}>
                            Disbursed → Farmer
                        </option>
                        <option value="REPAYMENT_FROM_VENDOR"
                            {{ request('type') === 'REPAYMENT_FROM_VENDOR' ? 'selected' : '' }}>
                            Repayment ← Vendor
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="from" class="form-control form-control-sm"
                           value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="to" class="form-control form-control-sm"
                           value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.payment-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                {{ $logs->total() }} record(s)
                @if(request('type') || request('from') || request('to'))
                    <span class="badge bg-warning text-dark ms-1">Filtered</span>
                @endif
            </span>
        </div>
        <div class="card-body p-0">
            @if ($logs->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-receipt fs-1 d-block mb-2"></i>
                    No payment records found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Transaction</th>
                                <th>Type</th>
                                <th>Vendor</th>
                                <th>Farmer</th>
                                <th class="text-end">Amount</th>
                                <th>M-Pesa Ref</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-nowrap">
                                        <div>{{ $log->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.transactions.show', $log->transaction_id) }}"
                                           class="fw-semibold text-decoration-none">
                                            {{ $log->transaction->transaction_code ?? '—' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($log->payment_type === 'DISBURSEMENT_TO_FARMER')
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-up me-1"></i>Disbursed → Farmer
                                            </span>
                                        @else
                                            <span class="badge bg-primary">
                                                <i class="fas fa-arrow-down me-1"></i>Repayment ← Vendor
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $log->transaction->vendorProfile->user->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $log->transaction->vendorProfile->user->phone ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $log->transaction->farmerProfile->user->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $log->transaction->farmerProfile->user->phone ?? '' }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-nowrap">
                                        {{ number_format($log->amount, 2) }}
                                    </td>
                                    <td>
                                        @if ($log->gateway_reference)
                                            <code class="small">{{ $log->gateway_reference }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.transactions.show', $log->transaction_id) }}"
                                           class="btn btn-sm btn-outline-info" style="width:30px;height:30px;border-radius:50%;padding:0;line-height:28px"
                                           title="View Transaction">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())
                    <div class="p-3">{{ $logs->links() }}</div>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection
