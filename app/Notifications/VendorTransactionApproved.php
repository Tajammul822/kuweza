<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;

class VendorTransactionApproved extends Notification
{
    public function __construct(
        private Transaction $transaction,
        private array $installments
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'TRANSACTION_APPROVED',
            'transaction_code' => $this->transaction->transaction_code,
            'total_amount'     => $this->transaction->total_amount,
            'currency'         => $this->transaction->currency,
            'message'          => "Your transaction {$this->transaction->transaction_code} has been approved. "
                                . "Repay " . number_format($this->transaction->total_amount, 2)
                                . " {$this->transaction->currency} in " . count($this->installments) . " installment(s).",
            'repayment_schedule' => collect($this->installments)->map(fn ($i) => [
                'installment_number' => $i->installment_number,
                'due_date'           => $i->due_date,
                'amount'             => $i->base_amount,
                'status'             => $i->status,
            ])->toArray(),
        ];
    }
}
