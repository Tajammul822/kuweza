<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MpesaService;

class TestMpesaConnection extends Command
{
    protected $signature = 'mpesa:test
                            {--phone=000000000001 : Customer MSISDN to test C2B with}
                            {--amount=1 : Amount to test with}
                            {--spc= : Override Service Provider Code}
                            {--currency= : Override currency (e.g. CDF)}
                            {--session-only : Only test getSession, skip C2B}';

    protected $description = 'Test the M-Pesa sandbox connection (getSession + optional C2B)';

    public function handle(): int
    {
        $this->info('M-Pesa Sandbox Connection Test');
        $this->line(str_repeat('-', 50));
        $this->line('Environment : ' . config('mpesa.environment'));
        $this->line('Market      : ' . config('mpesa.market'));
        $this->line('Country     : ' . config('mpesa.country'));
        $this->line('Currency    : ' . config('mpesa.currency'));
        $this->line('SPC         : ' . config('mpesa.service_provider_code'));
        $this->newLine();

        $mpesa = new MpesaService();

        // ── Step 1: getSession ────────────────────────────────────────
        $this->line('Step 1: Fetching session key...');

        try {
            $sessionKey = $mpesa->getSessionKey();
            $this->info('✔ Session key obtained: ' . substr($sessionKey, 0, 10) . '...');
        } catch (\Exception $e) {
            $this->error('✘ getSession failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('session-only')) {
            $this->newLine();
            $this->info('Session-only test passed.');
            return self::SUCCESS;
        }

        // ── Step 2: C2B ───────────────────────────────────────────────
        $phone    = $this->option('phone');
        $amount   = $this->option('amount');
        $spc      = $this->option('spc')      ?: null;
        $currency = $this->option('currency') ?: null;

        $effectiveSpc      = $spc      ?? config('mpesa.service_provider_code');
        $effectiveCurrency = $currency ?? config('mpesa.currency');

        $this->newLine();
        $this->line("Step 2: Initiating C2B to {$phone} for {$amount} {$effectiveCurrency} (SPC: {$effectiveSpc})...");

        try {
            $result = $mpesa->initiateC2B(
                $phone,
                (float) $amount,
                'TEST' . strtoupper(substr(md5((string) time()), 0, 8)),
                'Sandbox test payment',
                $spc,
                $currency
            );
        } catch (\Exception $e) {
            $this->error('✘ C2B request failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Raw M-Pesa Response:');
        foreach ($result as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        $this->newLine();
        $code = $result['output_ResponseCode'] ?? 'N/A';
        $desc = $result['output_ResponseDesc'] ?? 'N/A';

        if ($code === 'INS-0') {
            $txId = $result['output_TransactionID'] ?? 'N/A';
            $this->info("✔ C2B SUCCESS  Code: {$code} | TxID: {$txId}");
            return self::SUCCESS;
        }

        $this->error("✘ C2B FAILED  Code: {$code} | {$desc}");
        return self::FAILURE;
    }
}
