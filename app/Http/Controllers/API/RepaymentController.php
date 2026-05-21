<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LoanPaymentRule;
use App\Models\PaymentLog;
use App\Models\RepaymentInstallment;
use App\Services\MpesaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
     * Vendor initiates payment for a specific installment via M-Pesa C2B.
     */
    public function pay(RepaymentInstallment $installment)
    {
        $user = Auth::user();

        if (!$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $transaction = $installment->transaction;
        if ($transaction->vendor_id !== $user->vendorProfile->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!in_array($installment->status, ['PENDING', 'OVERDUE'])) {
            return response()->json([
                'message' => "Installment is already {$installment->status}.",
            ], 422);
        }

        $totalDue = round(
            ($installment->base_amount + $installment->penalty_amount) - $installment->amount_paid,
            2
        );

        if ($totalDue <= 0) {
            return response()->json(['message' => 'No outstanding amount on this installment.'], 422);
        }

        try {
            $mpesa  = new MpesaService();
            $result = $mpesa->initiateC2B(
                $user->phone,
                $totalDue,
                $transaction->transaction_code . 'I' . $installment->installment_number,
                "Repayment installment {$installment->installment_number} for {$transaction->transaction_code}"
            );

            $responseCode = $result['output_ResponseCode'] ?? '';

            if (!in_array($responseCode, ['INS-0', 'INS-I'])) {
                return response()->json([
                    'message'        => 'M-Pesa payment request failed: ' . ($result['output_ResponseDesc'] ?? 'Unknown error'),
                    'mpesa_response' => $result,
                ], 422);
            }

            PaymentLog::create([
                'transaction_id'    => $transaction->id,
                'installment_id'    => $installment->id,
                'user_id'           => $user->id,
                'payment_type'      => 'REPAYMENT_FROM_VENDOR',
                'amount'            => $totalDue,
                'gateway_reference' => $result['output_ThirdPartyConversationID']
                                    ?? $result['output_ConversationID']
                                    ?? null,
                'gateway_name'      => 'M-PESA',
            ]);

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
            return response()->json(['message' => 'M-Pesa error: ' . $e->getMessage()], 500);
        }
    }
}
