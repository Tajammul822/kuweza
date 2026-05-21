<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\RepaymentInstallment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    /**
     * B2C callback — M-Pesa confirms the disbursement reached the farmer.
     * Called after admin approves a transaction.
     */
    public function disbursement(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();
        Log::info('[M-Pesa] B2C disbursement callback', $payload);

        $responseCode   = $payload['output_ResponseCode']             ?? null;
        $conversationId = $payload['output_ConversationID']           ?? null;
        $transactionId  = $payload['output_TransactionID']            ?? null;

        if ($responseCode !== 'INS-0') {
            Log::warning('[M-Pesa] B2C disbursement failed', [
                'code'            => $responseCode,
                'desc'            => $payload['output_ResponseDesc'] ?? 'N/A',
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['status' => 'received']);
        }

        // Update the PaymentLog with the confirmed M-Pesa transaction ID
        $log = PaymentLog::where('payment_type', 'DISBURSEMENT_TO_FARMER')
            ->where('gateway_reference', $conversationId)
            ->first();

        if ($log && $transactionId) {
            $log->update(['gateway_reference' => $transactionId]);
            Log::info('[M-Pesa] Disbursement confirmed', [
                'transaction_id' => $log->transaction_id,
                'mpesa_tx_id'    => $transactionId,
            ]);
        }

        return response()->json(['status' => 'received']);
    }

    /**
     * C2B callback — M-Pesa confirms the vendor completed a repayment installment.
     */
    public function repayment(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();
        Log::info('[M-Pesa] C2B repayment callback', $payload);

        $responseCode   = $payload['output_ResponseCode']             ?? null;
        $conversationId = $payload['output_ConversationID']           ?? null;
        $transactionId  = $payload['output_TransactionID']            ?? null;
        $thirdPartyId   = $payload['output_ThirdPartyConversationID'] ?? null;

        if ($responseCode !== 'INS-0') {
            Log::warning('[M-Pesa] C2B repayment failed in callback', [
                'code' => $responseCode,
                'desc' => $payload['output_ResponseDesc'] ?? 'N/A',
            ]);
            return response()->json(['status' => 'received']);
        }

        // Find the pending repayment log by ThirdPartyConversationID
        $log = PaymentLog::where('payment_type', 'REPAYMENT_FROM_VENDOR')
            ->where('gateway_reference', $thirdPartyId)
            ->first();

        if (!$log) {
            Log::warning('[M-Pesa] No matching repayment log found', ['third_party_id' => $thirdPartyId]);
            return response()->json(['status' => 'received']);
        }

        // Update log with final M-Pesa transaction ID
        $log->update(['gateway_reference' => $transactionId]);

        // Mark the installment as PAID
        if ($log->installment_id) {
            $installment = RepaymentInstallment::find($log->installment_id);
            if ($installment) {
                $installment->update([
                    'amount_paid' => $installment->base_amount + $installment->penalty_amount,
                    'status'      => 'PAID',
                ]);

                Log::info('[M-Pesa] Installment marked PAID', [
                    'installment_id' => $installment->id,
                    'transaction_id' => $log->transaction_id,
                    'mpesa_tx_id'    => $transactionId,
                ]);

                // Check if all installments for this transaction are now paid
                $this->checkAndMarkRepaid($log->transaction_id);
            }
        }

        return response()->json(['status' => 'received']);
    }

    private function checkAndMarkRepaid(int $transactionId): void
    {
        $unpaid = RepaymentInstallment::where('transaction_id', $transactionId)
            ->whereIn('status', ['PENDING', 'OVERDUE', 'PARTIALLY_PAID'])
            ->count();

        if ($unpaid === 0) {
            Transaction::where('id', $transactionId)->update(['status' => 'REPAID']);
            Log::info('[M-Pesa] Transaction fully repaid', ['transaction_id' => $transactionId]);
        }
    }
}
