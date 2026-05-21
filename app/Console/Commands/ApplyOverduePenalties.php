<?php

namespace App\Console\Commands;

use App\Models\LoanPaymentRule;
use App\Models\RepaymentInstallment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyOverduePenalties extends Command
{
    protected $signature   = 'kuweza:apply-overdue-penalties';
    protected $description = 'Mark overdue installments and apply penalties based on loan rules';

    public function handle(): int
    {
        $today = Carbon::today();
        $count = 0;

        // Load all PENDING installments whose transaction has an assigned rule
        RepaymentInstallment::where('status', 'PENDING')
            ->whereHas('transaction', fn ($q) => $q->whereNotNull('rule_id'))
            ->with('transaction.rule')
            ->chunkById(100, function ($installments) use ($today, &$count) {
                foreach ($installments as $installment) {
                    $rule = $installment->transaction->rule;
                    if (!$rule) continue;

                    $gracePeriodEnd = Carbon::parse($installment->due_date)
                        ->addDays($rule->grace_period_days);

                    if ($today->lte($gracePeriodEnd)) {
                        continue; // still within grace period
                    }

                    // Calculate penalty based on rule type
                    if ($rule->penalty_type === 'PERCENTAGE') {
                        $penalty = round($installment->base_amount * ($rule->penalty_value / 100), 2);
                    } else { // FIXED
                        $penalty = $rule->penalty_value;
                    }

                    $installment->update([
                        'status'         => 'OVERDUE',
                        'penalty_amount' => $penalty,
                    ]);

                    Log::info('[Penalties] Installment marked overdue', [
                        'installment_id' => $installment->id,
                        'transaction_id' => $installment->transaction_id,
                        'due_date'       => $installment->due_date,
                        'penalty'        => $penalty,
                        'rule_type'      => $rule->penalty_type,
                    ]);

                    $count++;
                }
            });

        $this->info("Done. {$count} installment(s) marked overdue with penalties applied.");
        return self::SUCCESS;
    }
}
