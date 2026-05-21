<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;

class FarmerPaymentReceived extends Notification
{
    public function __construct(private Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'PAYMENT_RECEIVED',
            'transaction_code' => $this->transaction->transaction_code,
            'amount'           => $this->transaction->total_amount,
            'currency'         => $this->transaction->currency,
            'message'          => "You have received a payment of "
                                . number_format($this->transaction->total_amount, 2)
                                . " {$this->transaction->currency} for transaction "
                                . "{$this->transaction->transaction_code}.",
        ];
    }
}
