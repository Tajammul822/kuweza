@extends('admin.layout')
@section('admin-transaction-index-content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <div class="container-xxl">
        @if (Session::get('success'))
            <div class="alert alert-success">
                {{ Session::get('success') }}
            </div>
        @endif
        
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col d-flex justify-content-between align-items-center">
                                <h4 class="card-title">All Transactions</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0 mt-3">
                        <div class="table-responsive">
                            <table class="table datatable" id="transactions-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Vendor (Buyer)</th>
                                        <th>Farmer (Seller)</th>
                                        <th>Total Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td><strong>{{ $transaction->transaction_code }}</strong></td>
                                            
                                            {{-- Vendor aur Farmer ka naam nikalne ka tariqa --}}
                                            <td>{{ $transaction->vendorProfile->user->name ?? 'N/A' }}</td>
                                            <td>{{ $transaction->farmerProfile->user->name ?? 'N/A' }}</td>
                                            
                                            <td>{{ $transaction->total_amount }} {{ $transaction->currency }}</td>
                                            <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                                            
                                            <td>
                                                @if ($transaction->status == 'PENDING')
                                                    <span class="badge bg-warning text-dark">PENDING</span>
                                                @elseif($transaction->status == 'APPROVED')
                                                    <span class="badge bg-primary">APPROVED</span>
                                                @elseif($transaction->status == 'REPAID')
                                                    <span class="badge bg-success">REPAID</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transaction->status }}</span>
                                                @endif
                                            </td>
                                            
                                            <td>
                                                {{-- View Details / Approve Button --}}
                                                {{-- Iska route hum agle step mein banayenge --}}
                                                <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-info btn-sm" style="height:30px;width:30px;border-radius:50%" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#transactions-table').DataTable({
                pageLength: 50,
                order: [[4, 'desc']] // Date ke hisaab se descending sort
            });
        });
    </script>
@endsection