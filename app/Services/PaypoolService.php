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
     * Create a payment via Paypool API
     */
    public function createPayment(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/api/v1/payments/create', $data);

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
