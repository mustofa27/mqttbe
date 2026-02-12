<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaypoolService
{
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->apiUrl = config('paypool.api_url');
        $this->apiToken = config('paypool.api_token');
    }

    /**
     * Create a payment via Paypool API (Midtrans)
     * Supports all documented fields: external_id, amount, currency, customer_name, customer_email, customer_phone, description, metadata, success_redirect_url, failure_redirect_url
     */
    public function createPayment(array $data)
    {
        // Only allow documented fields
        $allowed = [
            'external_id', 'amount', 'currency', 'customer_name', 'customer_email', 'customer_phone',
            'description', 'metadata', 'success_redirect_url', 'failure_redirect_url'
        ];
        $payload = array_intersect_key($data, array_flip($allowed));
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/api/v1/payments/create', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            Log::error('Paypool payment creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Payment creation failed',
                'errors' => $response->json('errors'),
            ];
        } catch (\Exception $e) {
            Log::error('Paypool API error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to payment gateway',
            ];
        }
    }

    /**
     * Continue a pending payment (get Snap redirect URL)
     * Returns the Snap payment page URL for a pending payment
     */
    public function continuePayment(string $externalId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get($this->apiUrl . '/api/v1/payments/' . $externalId . '/continue');

            if ($response->successful() && $response->json('success')) {
                return [
                    'success' => true,
                    'redirect_url' => $response->json('redirect_url'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Cannot continue payment',
            ];
        } catch (\Exception $e) {
            Log::error('Paypool API error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to continue payment',
            ];
        }
    }

    /**
     * Get payment details by external_id
     */
    public function getPayment(string $externalId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get($this->apiUrl . '/api/v1/payments/' . $externalId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Payment not found',
            ];
        } catch (\Exception $e) {
            Log::error('Paypool API error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Unable to fetch payment details',
            ];
        }
    }

    /**
     * Cancel a payment
     */
    public function cancelPayment(string $externalId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->post($this->apiUrl . '/api/v1/payments/' . $externalId . '/cancel');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Payment cancellation failed',
            ];
        } catch (\Exception $e) {
            Log::error('Paypool API error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Unable to cancel payment',
            ];
        }
    }
}
