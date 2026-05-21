<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\RepaymentInstallment;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepaymentController extends Controller
{
    /**
     * List all repayment installments for the authenticated vendor's transactions.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $installments = RepaymentInstallment::whereHas('transaction', function ($q) use ($user) {
            $q->where('vendor_id', $user->vendorProfile->id);
        })
        ->with('transaction:id,transaction_code,total_amount,currency,status')
        ->orderBy('due_date')
        ->get();

        return response()->json(['installments' => $installments]);
    }

    /**
     * Vendor initiates payment for a specific installment.
     * Sends a USSD push (C2B) to the vendor's phone.
     */
    public function pay(RepaymentInstallment $installment)
    {
        $user = Auth::user();

        if (!$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Make sure this installment belongs to the authenticated vendor
        $transaction = $installment->transaction;
        if ($transaction->vendor_id !== $user->vendorProfile->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!in_array($installment->status, ['PENDING', 'OVERDUE'])) {
            return response()->json([
                'message' => "Installment is already {$installment->status}.",
            ], 422);
        }

        $totalDue = $installment->base_amount + $installment->penalty_amount - $installment->amount_paid;

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
                    'message' => 'M-Pesa payment request failed: ' . ($result['output_ResponseDesc'] ?? 'Unknown error'),
                    'mpesa_response' => $result,
                ], 422);
            }

            // Log the pending repayment — marked PAID by callback when vendor confirms USSD
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
                'message'          => 'Payment request sent. Please confirm on your phone via USSD.',
                'amount_due'       => $totalDue,
                'currency'         => $transaction->currency,
                'installment_number' => $installment->installment_number,
                'due_date'         => $installment->due_date,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'M-Pesa error: ' . $e->getMessage()], 500);
        }
    }
}
