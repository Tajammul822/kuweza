<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LoanPaymentRule;
use App\Models\PaymentLog;
use App\Models\RepaymentInstallment;
use App\Models\Transaction;
use App\Services\MpesaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RepaymentController extends Controller
{
    /**
     * List all repayment installments for the authenticated vendor.
     * Includes grace period warnings and total_due so the frontend
     * can clearly show what the vendor owes and any upcoming penalties.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $today = Carbon::today();

        $installments = RepaymentInstallment::whereHas('transaction', function ($q) use ($user) {
            $q->where('vendor_id', $user->vendorProfile->id);
        })
        ->with('transaction:id,transaction_code,total_amount,currency,status,rule_id')
        ->orderBy('due_date')
        ->get()
        ->map(function ($installment) use ($today) {

            $rule = $installment->transaction->rule_id
                ? LoanPaymentRule::find($installment->transaction->rule_id)
                : null;

            $dueDate        = Carbon::parse($installment->due_date);
            $gracePeriodEnd = $rule
                ? $dueDate->copy()->addDays($rule->grace_period_days)
                : $dueDate->copy();

            $totalDue = round(
                ($installment->base_amount + $installment->penalty_amount) - $installment->amount_paid,
                2
            );

            // Estimated penalty if vendor doesn't pay before grace period ends
            $estimatedPenalty = 0;
            if ($rule && $installment->status === 'PENDING' && $installment->penalty_amount == 0) {
                if ($rule->penalty_type === 'PERCENTAGE') {
                    $estimatedPenalty = round($installment->base_amount * ($rule->penalty_value / 100), 2);
                } else { // FIXED
                    $estimatedPenalty = $rule->penalty_value;
                }
            }

            // Grace period flags
            $isInGracePeriod = $installment->status === 'PENDING'
                && $today->gt($dueDate)
                && $today->lte($gracePeriodEnd);

            $daysUntilGraceEnds = $isInGracePeriod
                ? $today->diffInDays($gracePeriodEnd)
                : null;

            // Build warning message for the frontend
            $warning = null;
            if ($installment->status === 'OVERDUE') {
                $warning = "This installment is overdue. A penalty of {$installment->penalty_amount} {$installment->transaction->currency} has been applied.";
            } elseif ($isInGracePeriod) {
                $warning = "You are in the grace period. Pay within {$daysUntilGraceEnds} day(s) (by "
                    . $gracePeriodEnd->format('d M Y')
                    . ") to avoid a penalty of {$estimatedPenalty} {$installment->transaction->currency}.";
            } elseif ($installment->status === 'PENDING' && $rule && $rule->grace_period_days > 0) {
                $warning = "If not paid by " . $dueDate->format('d M Y')
                    . ", a {$rule->grace_period_days}-day grace period applies. "
                    . "After that, a penalty of {$estimatedPenalty} {$installment->transaction->currency} will be added.";
            }

            return [
                'id'                  => $installment->id,
                'transaction_code'    => $installment->transaction->transaction_code,
                'installment_number'  => $installment->installment_number,
                'due_date'            => $installment->due_date,
                'grace_period_ends'   => $rule ? $gracePeriodEnd->toDateString() : null,
                'base_amount'         => $installment->base_amount,
                'penalty_amount'      => $installment->penalty_amount,
                'estimated_penalty'   => $estimatedPenalty,
                'amount_paid'         => $installment->amount_paid,
                'total_due'           => $totalDue,
                'currency'            => $installment->transaction->currency,
                'status'              => $installment->status,
                'is_in_grace_period'  => $isInGracePeriod,
                'days_until_grace_ends' => $daysUntilGraceEnds,
                'warning'             => $warning,
            ];
        });

        return response()->json(['installments' => $installments]);
    }

    /**
     * Payment history for the authenticated vendor.
     * Returns all PaymentLogs tied to the vendor's transactions.
     */
    public function paymentLogs()
    {
        $user = Auth::user();

        if (!$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $logs = PaymentLog::whereHas('transaction', function ($q) use ($user) {
            $q->where('vendor_id', $user->vendorProfile->id);
        })
        ->where('status', 'CONFIRMED')
        ->with([
            'transaction:id,transaction_code,currency',
            'installment:id,installment_number',
        ])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(fn ($log) => [
            'id'                 => $log->id,
            'type'               => $log->payment_type,
            'type_label'         => $log->payment_type === 'DISBURSEMENT_TO_FARMER'
                                        ? 'Disbursed to Farmer'
                                        : 'Repayment',
            'amount'             => $log->amount,
            'currency'           => $log->transaction->currency,
            'transaction_code'   => $log->transaction->transaction_code,
            'installment_number' => $log->installment?->installment_number,
            'gateway'            => $log->gateway_name,
            'reference'          => $log->gateway_reference,
            'date'               => $log->created_at->toDateTimeString(),
        ]);

        return response()->json(['payment_logs' => $logs]);
    }

    /**
     * Vendor initiates payment for a specific installment via M-Pesa C2B.
     */
    public function pay(RepaymentInstallment $installment)
    {
        Log::debug('[Pay] ── START ──────────────────────────────', [
            'installment_id' => $installment->id,
        ]);

        $user = Auth::user();

        Log::debug('[Pay] Authenticated user', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'phone'   => $user->phone,
        ]);

        if (!$user->vendorProfile) {
            Log::warning('[Pay] No vendor profile', ['user_id' => $user->id]);
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        Log::debug('[Pay] Vendor profile', [
            'vendor_id' => $user->vendorProfile->id,
        ]);

        $transaction = $installment->transaction;

        Log::debug('[Pay] Installment & transaction', [
            'installment_number'  => $installment->installment_number,
            'installment_status'  => $installment->status,
            'due_date'            => $installment->due_date,
            'base_amount'         => $installment->base_amount,
            'penalty_amount'      => $installment->penalty_amount,
            'amount_paid'         => $installment->amount_paid,
            'transaction_id'      => $transaction->id,
            'transaction_code'    => $transaction->transaction_code,
            'transaction_vendor'  => $transaction->vendor_id,
        ]);

        if ($transaction->vendor_id !== $user->vendorProfile->id) {
            Log::warning('[Pay] Forbidden — transaction belongs to different vendor', [
                'tx_vendor_id'   => $transaction->vendor_id,
                'user_vendor_id' => $user->vendorProfile->id,
            ]);
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!in_array($installment->status, ['PENDING', 'OVERDUE'])) {
            Log::info('[Pay] Rejected — installment not payable', [
                'status' => $installment->status,
            ]);
            return response()->json([
                'message' => "Installment is already {$installment->status}.",
            ], 422);
        }

        $totalDue = round(
            ($installment->base_amount + $installment->penalty_amount) - $installment->amount_paid,
            2
        );

        Log::debug('[Pay] Total due calculated', ['total_due' => $totalDue]);

        if ($totalDue <= 0) {
            Log::info('[Pay] Rejected — no outstanding amount', ['total_due' => $totalDue]);
            return response()->json(['message' => 'No outstanding amount on this installment.'], 422);
        }

        $txRef       = $transaction->transaction_code . 'I' . $installment->installment_number;
        $description = "Repayment installment {$installment->installment_number} for {$transaction->transaction_code}";

        Log::debug('[Pay] Calling M-Pesa C2B', [
            'msisdn'    => $user->phone,
            'amount'    => $totalDue,
            'reference' => $txRef,
            'desc'      => $description,
        ]);

        try {
            $mpesa  = new MpesaService();
            $result = $mpesa->initiateC2B(
                $user->phone,
                $totalDue,
                $txRef,
                $description
            );

            Log::debug('[Pay] M-Pesa C2B raw result', ['result' => $result]);

            $responseCode = $result['output_ResponseCode'] ?? '';

            if (!in_array($responseCode, ['INS-0', 'INS-I'])) {
                Log::error('[Pay] M-Pesa C2B rejected', [
                    'response_code' => $responseCode,
                    'response_desc' => $result['output_ResponseDesc'] ?? null,
                    'full_response' => $result,
                ]);
                return response()->json([
                    'message'        => 'M-Pesa payment request failed: ' . ($result['output_ResponseDesc'] ?? 'Unknown error'),
                    'mpesa_response' => $result,
                ], 422);
            }

            $gatewayRef = $result['output_ThirdPartyConversationID']
                       ?? $result['output_ConversationID']
                       ?? null;

            Log::debug('[Pay] Creating PaymentLog', [
                'transaction_id'    => $transaction->id,
                'installment_id'    => $installment->id,
                'amount'            => $totalDue,
                'gateway_reference' => $gatewayRef,
            ]);

            $paymentLog = PaymentLog::create([
                'transaction_id'    => $transaction->id,
                'installment_id'    => $installment->id,
                'user_id'           => $user->id,
                'payment_type'      => 'REPAYMENT_FROM_VENDOR',
                'amount'            => $totalDue,
                'gateway_reference' => $gatewayRef,
                'gateway_name'      => 'M-PESA',
                'status'            => 'PENDING',
            ]);

            Log::info('[Pay] SUCCESS — C2B initiated', [
                'installment_id'    => $installment->id,
                'response_code'     => $responseCode,
                'gateway_reference' => $gatewayRef,
            ]);

            // In simulation mode, immediately mark the installment PAID
            // (bypasses the async callback — for dev/testing only)
            if (config('mpesa.simulate_c2b')) {
                $paymentLog->update(['status' => 'CONFIRMED']);
                $installment->update([
                    'amount_paid' => $installment->base_amount + $installment->penalty_amount,
                    'status'      => 'PAID',
                ]);

                $unpaid = RepaymentInstallment::where('transaction_id', $transaction->id)
                    ->whereIn('status', ['PENDING', 'OVERDUE'])
                    ->count();

                if ($unpaid === 0) {
                    Transaction::where('id', $transaction->id)->update(['status' => 'REPAID']);
                    Log::info('[Pay] Transaction fully repaid (simulated)', ['transaction_id' => $transaction->id]);
                }

                Log::info('[Pay] Installment marked PAID (simulated)', ['installment_id' => $installment->id]);

                return response()->json([
                    'message'            => 'Payment successful (simulated).',
                    'status'             => 'PAID',
                    'total_due'          => $totalDue,
                    'base_amount'        => $installment->base_amount,
                    'penalty_amount'     => $installment->penalty_amount,
                    'currency'           => $transaction->currency,
                    'installment_number' => $installment->installment_number,
                    'due_date'           => $installment->due_date,
                ]);
            }

            return response()->json([
                'message'            => 'Payment request sent. Please confirm on your phone via USSD.',
                'total_due'          => $totalDue,
                'base_amount'        => $installment->base_amount,
                'penalty_amount'     => $installment->penalty_amount,
                'currency'           => $transaction->currency,
                'installment_number' => $installment->installment_number,
                'due_date'           => $installment->due_date,
            ]);

        } catch (\Exception $e) {
            Log::error('[Pay] Exception thrown', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'M-Pesa error: ' . $e->getMessage()], 500);
        }
    }
}
