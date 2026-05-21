<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaService
{
    private string $apiKey;
    private string $publicKey;
    private string $serviceProviderCode;
    private string $country;
    private string $currency;
    private string $baseUrl;

    public function __construct()
    {
        $env    = config('mpesa.environment', 'sandbox');
        $market = config('mpesa.market', 'vodacomTZN');

        $this->apiKey              = config('mpesa.api_key');
        $this->publicKey           = config('mpesa.public_key');
        $this->serviceProviderCode = config('mpesa.service_provider_code');
        $this->country             = config('mpesa.country');
        $this->currency            = config('mpesa.currency');
        $this->baseUrl             = "https://openapi.m-pesa.com/{$env}/ipg/v2/{$market}";
    }

    private function encrypt(string $key): string
    {
        $pem = "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split($this->publicKey, 64, "\n")
            . "-----END PUBLIC KEY-----";

        openssl_public_encrypt($key, $encrypted, $pem, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    public function getSessionKey(): string
    {
        $url = "{$this->baseUrl}/getSession/";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->encrypt($this->apiKey),
            'Origin'        => '*',
            'Content-Type'  => 'application/json',
        ])->get($url);

        Log::debug('[M-Pesa] getSession', [
            'url'         => $url,
            'http_status' => $response->status(),
            'response'    => $response->body(),
        ]);

        $data = $response->json();

        if (($data['output_ResponseCode'] ?? '') !== 'INS-0') {
            throw new \Exception(
                'M-Pesa getSession failed: ' . ($data['output_ResponseDesc'] ?? $response->body())
            );
        }

        return $data['output_SessionID'];
    }

    /**
     * B2C — Business to Customer.
     * Admin disburses money TO the farmer's phone after approving a transaction.
     */
    public function initiateB2C(
        string $recipientMSISDN,
        float  $amount,
        string $transactionReference,
        string $description
    ): array {
        // B2C sandbox is often unavailable; simulate locally for development
        if (config('mpesa.simulate_b2c')) {
            $fakeId = strtoupper(substr(md5(uniqid()), 0, 16));
            Log::debug('[M-Pesa] B2C SIMULATED', [
                'recipient' => $recipientMSISDN,
                'amount'    => $amount,
                'reference' => $transactionReference,
                'fake_tx_id' => $fakeId,
            ]);
            return [
                'output_ResponseCode'             => 'INS-0',
                'output_ResponseDesc'             => 'Simulated B2C success (sandbox bypass)',
                'output_TransactionID'            => $fakeId,
                'output_ConversationID'           => $fakeId,
                'output_ThirdPartyConversationID' => $fakeId,
            ];
        }

        $sessionKey          = $this->getSessionKey();
        $encryptedSessionKey = $this->encrypt($sessionKey);
        $conversationId      = preg_replace('/[^a-zA-Z0-9]/', '', (string) Str::uuid());

        $url     = "{$this->baseUrl}/b2cPayment/singleStage/";
        $payload = [
            'input_Amount'                   => number_format($amount, 2, '.', ''),
            'input_Country'                  => $this->country,
            'input_Currency'                 => $this->currency,
            'input_CustomerMSISDN'           => $recipientMSISDN,
            'input_ServiceProviderCode'      => $this->serviceProviderCode,
            'input_ThirdPartyConversationID' => $conversationId,
            'input_TransactionReference'     => preg_replace('/[^a-zA-Z0-9]/', '', $transactionReference),
            'input_PaymentItemsDesc'         => substr($description, 0, 70),
        ];

        Log::debug('[M-Pesa] B2C request', ['url' => $url, 'payload' => $payload]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $encryptedSessionKey,
            'Origin'        => '*',
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        Log::debug('[M-Pesa] B2C response', [
            'http_status' => $response->status(),
            'body'        => $response->body(),
        ]);

        $data = $response->json();

        if ($data === null) {
            throw new \Exception(
                "M-Pesa B2C returned HTTP {$response->status()} (non-JSON). "
                . "Check that B2C Single Payment is enabled on your portal application."
            );
        }

        return $data;
    }

    /**
     * C2B — Customer to Business.
     * Sends a USSD push to the vendor's phone to collect a repayment installment.
     */
    public function initiateC2B(
        string $customerMSISDN,
        float  $amount,
        string $transactionReference,
        string $description
    ): array {
        $sessionKey          = $this->getSessionKey();
        $encryptedSessionKey = $this->encrypt($sessionKey);
        $conversationId      = preg_replace('/[^a-zA-Z0-9]/', '', (string) Str::uuid());

        $url     = "{$this->baseUrl}/c2bPayment/singleStage/";
        $payload = [
            'input_Amount'                   => number_format($amount, 2, '.', ''),
            'input_Country'                  => $this->country,
            'input_Currency'                 => $this->currency,
            'input_CustomerMSISDN'           => $customerMSISDN,
            'input_ServiceProviderCode'      => $this->serviceProviderCode,
            'input_ThirdPartyConversationID' => $conversationId,
            'input_TransactionReference'     => preg_replace('/[^a-zA-Z0-9]/', '', $transactionReference),
            'input_PurchasedItemsDesc'       => substr($description, 0, 70),
        ];

        Log::debug('[M-Pesa] C2B request', ['url' => $url, 'payload' => $payload]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $encryptedSessionKey,
            'Origin'        => '*',
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        Log::debug('[M-Pesa] C2B response', [
            'http_status' => $response->status(),
            'body'        => $response->body(),
        ]);

        $data = $response->json();

        if ($data === null) {
            throw new \Exception(
                "M-Pesa C2B returned HTTP {$response->status()} (non-JSON). "
                . "Check that C2B Single Payment is enabled on your portal application."
            );
        }

        return $data;
    }
}
