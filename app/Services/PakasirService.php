<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PakasirService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $project;

    public function __construct()
    {
        $this->baseUrl = config('pakasir.base_url', 'https://app.pakasir.com/api');
        $this->apiKey = config('pakasir.api_key');
        $this->project = config('pakasir.project');
    }

    /**
     * Create a QRIS transaction
     */
    public function createTransaction(string $orderId, int $amount): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/transactioncreate/qris", [
            'project' => $this->project,
            'order_id' => $orderId,
            'amount' => $amount,
            'api_key' => $this->apiKey,
        ]);

        return $response->json();
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId, int $amount): array
    {
        $response = Http::get("{$this->baseUrl}/transactiondetail", [
            'project' => $this->project,
            'amount' => $amount,
            'order_id' => $orderId,
            'api_key' => $this->apiKey,
        ]);

        return $response->json();
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction(string $orderId, int $amount): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/transactioncancel", [
            'project' => $this->project,
            'order_id' => $orderId,
            'amount' => $amount,
            'api_key' => $this->apiKey,
        ]);

        return $response->json();
    }

    /**
     * Check if payment is completed
     */
    public function isPaymentCompleted(string $orderId, int $amount): bool
    {
        $result = $this->getTransactionStatus($orderId, $amount);
        return $result['transaction']['status'] ?? '' === 'completed';
    }
}
