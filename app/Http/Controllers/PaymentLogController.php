<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use Illuminate\Http\Request;

class PaymentLogController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentLog::with([
            'transaction.vendorProfile.user',
            'transaction.farmerProfile.user',
        ])->latest();

        // Filter by payment type
        if ($request->filled('type') && in_array($request->type, ['DISBURSEMENT_TO_FARMER', 'REPAYMENT_FROM_VENDOR'])) {
            $query->where('payment_type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Summary totals (always calculated on all records, not just the filtered page)
        $totalDisbursed = PaymentLog::where('payment_type', 'DISBURSEMENT_TO_FARMER')->sum('amount');
        $totalRepaid    = PaymentLog::where('payment_type', 'REPAYMENT_FROM_VENDOR')->sum('amount');
        $outstanding    = max($totalDisbursed - $totalRepaid, 0);

        return view('admin.payment_logs.index', compact(
            'logs', 'totalDisbursed', 'totalRepaid', 'outstanding'
        ));
    }
}
