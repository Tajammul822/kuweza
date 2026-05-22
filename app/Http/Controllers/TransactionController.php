<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\LoanPaymentRule;
use App\Models\PaymentLog;
use App\Models\RepaymentInstallment;
use App\Notifications\FarmerPaymentReceived;
use App\Notifications\VendorTransactionApproved;
use App\Services\MpesaService;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['vendorProfile.user', 'farmerProfile.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(int $id)
    {
        $transaction = Transaction::with([
            'vendorProfile.user',
            'farmerProfile.user',
            'items.product',
        ])->findOrFail($id);

        $applicableRule = LoanPaymentRule::where('min_amount', '<=', $transaction->total_amount)
            ->where('max_amount', '>=', $transaction->total_amount)
            ->first();

        $installments = RepaymentInstallment::where('transaction_id', $transaction->id)
            ->orderBy('installment_number')
            ->get();

        $paymentLogs = \App\Models\PaymentLog::where('transaction_id', $transaction->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.transactions.show', compact(
            'transaction', 'applicableRule', 'installments', 'paymentLogs'
        ));
    }

    public function approve(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'PENDING') {
            return back()->with('error', 'Only pending transactions can be approved.');
        }

        $farmerPhone = $transaction->farmerProfile->user->phone;
        $vendorUser  = $transaction->vendorProfile->user;

        $rule = LoanPaymentRule::where('min_amount', '<=', $transaction->total_amount)
            ->where('max_amount', '>=', $transaction->total_amount)
            ->first();

        try {
            // ── B2C: Admin disburses payment to farmer ────────────────
            $mpesa  = new MpesaService();
            $result = $mpesa->initiateB2C(
                $farmerPhone,
                (float) $transaction->total_amount,
                $transaction->transaction_code,
                'Farm sale payment - ' . $transaction->transaction_code
            );

            $accepted = in_array($result['output_ResponseCode'] ?? '', ['INS-0', 'INS-I']);

            if (!$accepted) {
                return back()->with(
                    'error',
                    'M-Pesa B2C failed: ' . ($result['output_ResponseDesc'] ?? 'Unknown error')
                );
            }

            // ── Update transaction ────────────────────────────────────
            $transaction->update([
                'status'      => 'APPROVED',
                'rule_id'     => $rule ? $rule->id : null,
                'admin_notes' => $request->input('admin_notes'),
            ]);

            // ── Log disbursement to farmer ────────────────────────────
            PaymentLog::create([
                'transaction_id'    => $transaction->id,
                'user_id'           => auth()->id(),
                'payment_type'      => 'DISBURSEMENT_TO_FARMER',
                'amount'            => $transaction->total_amount,
                'gateway_reference' => $result['output_TransactionID']
                                    ?? $result['output_ConversationID']
                                    ?? null,
                'gateway_name'      => 'M-PESA',
                'status'            => config('mpesa.simulate_b2c') ? 'CONFIRMED' : 'PENDING',
            ]);

            // ── Generate repayment schedule ───────────────────────────
            $installments = [];
            if ($rule) {
                $installments = $this->generateInstallments($transaction, $rule);
            }

            // ── Notify farmer (payment sent) ──────────────────────────
            $transaction->farmerProfile->user->notify(
                new FarmerPaymentReceived($transaction)
            );

            // ── Notify vendor (approved + repayment schedule) ─────────
            $vendorUser->notify(
                new VendorTransactionApproved($transaction, $installments)
            );

            return back()->with('success',
                "Transaction approved. M-Pesa payment of {$transaction->total_amount} {$transaction->currency} "
                . "sent to farmer ({$farmerPhone}). Vendor notified with repayment schedule."
            );

        } catch (\Exception $e) {
            return back()->with('error', 'M-Pesa error: ' . $e->getMessage());
        }
    }

    /**
     * Generates repayment installments and returns the created records.
     */
    private function generateInstallments(Transaction $transaction, LoanPaymentRule $rule): array
    {
        $today = Carbon::today();
        $created = [];

        if ($rule->installment_type === 'SINGLE') {
            $created[] = RepaymentInstallment::create([
                'transaction_id'     => $transaction->id,
                'installment_number' => 1,
                'due_date'           => $today->copy()->addDays($rule->duration_days),
                'base_amount'        => $transaction->total_amount,
                'penalty_amount'     => 0,
                'status'             => 'PENDING',
            ]);

        } elseif ($rule->installment_type === 'WEEKLY') {
            $weeks             = max((int) ($rule->duration_days / 7), 1);
            $installmentAmount = round($transaction->total_amount / $weeks, 2);

            for ($i = 1; $i <= $weeks; $i++) {
                $created[] = RepaymentInstallment::create([
                    'transaction_id'     => $transaction->id,
                    'installment_number' => $i,
                    'due_date'           => $today->copy()->addWeeks($i),
                    'base_amount'        => $installmentAmount,
                    'penalty_amount'     => 0,
                    'status'             => 'PENDING',
                ]);
            }

        } elseif ($rule->installment_type === 'MONTHLY') {
            $months            = max((int) ($rule->duration_days / 30), 1);
            $installmentAmount = round($transaction->total_amount / $months, 2);

            for ($i = 1; $i <= $months; $i++) {
                $created[] = RepaymentInstallment::create([
                    'transaction_id'     => $transaction->id,
                    'installment_number' => $i,
                    'due_date'           => $today->copy()->addMonths($i),
                    'base_amount'        => $installmentAmount,
                    'penalty_amount'     => 0,
                    'status'             => 'PENDING',
                ]);
            }
        }

        return $created;
    }
}
