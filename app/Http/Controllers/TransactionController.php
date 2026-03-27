<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\LoanPaymentRule;

class TransactionController extends Controller
{
    public function index()
    {

        $transactions = Transaction::with(['vendorProfile.user', 'farmerProfile.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show($id)
    {

        $transaction = Transaction::with(['vendorProfile.user', 'farmerProfile.user', 'items.product'])
            ->findOrFail($id);

        $applicableRule = LoanPaymentRule::where('min_amount', '<=', $transaction->total_amount)
            ->where('max_amount', '>=', $transaction->total_amount)
            ->first();

        return view('admin.transactions.show', compact('transaction', 'applicableRule'));
    }
}
