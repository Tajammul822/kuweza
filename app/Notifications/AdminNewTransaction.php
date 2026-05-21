<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;

class AdminNewTransaction extends Notification
{
    public function __construct(private Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'NEW_TRANSACTION',
            'transaction_id'   => $this->transaction->id,
            'transaction_code' => $this->transaction->transaction_code,
            'amount'           => $this->transaction->total_amount,
            'currency'         => $this->transaction->currency,
            'message'          => "New transaction {$this->transaction->transaction_code} submitted by vendor "
                                . ($this->transaction->vendorProfile->user->name ?? 'Unknown')
                                . " for " . number_format($this->transaction->total_amount, 2)
                                . " {$this->transaction->currency}. Awaiting approval.",
        ];
    }
}
