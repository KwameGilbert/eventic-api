<?php

namespace App\Services;

use Exception;

/**
 * Kowri Payment Service
 * 
 * Handles integration with Kowri WebPOS Merchant APIs for payment initiation,
 * status verification, and webhook processing.
 */
class KowriService
{
    private string $appId;
    private string $appReference;
    private string $secret;
    private string $baseUrl;

    public function __construct()
    {
        $this->appId = $_ENV['KOWRI_APP_ID'] ?? '';
        $this->appReference = $_ENV['KOWRI_APP_REFERENCE'] ?? '';
        $this->secret = $_ENV['KOWRI_SECRET'] ?? '';
        // Base URL should be the domain only
        $this->baseUrl = rtrim($_ENV['KOWRI_BASE_URL'] ?? 'https://kbposapi.mykowri.com', '/');
    }

    /**
     * Create an invoice/order on Kowri
     * 
     * @param array $data Order details
     * @return array Contains 'pay_token' on success
     * @throws Exception
     */
    public function createInvoice(array $data): array
    {
        $payload = [
            'requestId' => uniqid('req_', true),
            'appReference' => $this->appReference,
            'secret' => $this->secret,
            'amount' => number_format((float) $data['amount'], 2, '.', ''),
            'currency' => $data['currency'] ?? 'GHS',
            'merchantOrderId' => $data['order_id'],
            'reference' => $data['order_id'],
            'description' => $data['description'] ?? 'Payment',
            'callbackUrl' => $data['redirect_url'] ?? null,
            'metadata' => [
                [
                    'key' => 'webhookUrl',
                    'value' => $data['webhook_url'] ?? $_ENV['PAYMENT_CALLBACK_URL'] ?? ''
                ]
            ]
        ];

        // Optional customer info
        if (!empty($data['email'])) $payload['customerEmail'] = $data['email'];
        if (!empty($data['name'])) $payload['customerName'] = $data['name'];
        if (!empty($data['phone'])) $payload['customerPhone'] = $data['phone'];

        $response = $this->makeRequest('/webpos/createInvoice', $payload);

        $result = $response['result'] ?? [];
        if (!empty($result['invoiceNum'])) {
            return [
                'success' => true,
                'pay_token' => $result['invoiceNum'], // Map to internal 'pay_token' name
                'invoiceNum' => $result['invoiceNum'],
                'order_id' => $data['order_id'],
                'raw' => $response
            ];
        }

        throw new Exception("Kowri Create Invoice Error: " . ($response['message'] ?? json_encode($response)));
    }

    /**
     * Query transaction status using the pay_token or merchantOrderId
     * 
     * @param string $payToken
     * @return array
     * @throws Exception
     */
    public function queryTransaction(string $payToken): array
    {
        $payload = [
            'requestId' => uniqid('req_', true),
            'appReference' => $this->appReference,
            'secret' => $this->secret,
            'pay_token' => $payToken
        ];

        $response = $this->makeRequest('/webpos/getInvoiceSummary', $payload);

        $status = $response['status'] ?? 'UNKNOWN';

        return [
            'status' => $this->mapStatus($status),
            'raw_status' => $status,
            'amount' => $response['amount'] ?? 0,
            'order_id' => $response['orderId'] ?? null,
            'transaction_id' => $response['transactionId'] ?? null,
            'success' => $response['success'] ?? false,
            'raw' => $response
        ];
    }

    /**
     * PayNow - Combined Create Invoice and Process Payment (MoMo Only)
     * For MoMo, this triggers an instant prompt on the customer's phone.
     * For Card, it returns a redirectUrl.
     * 
     * @param array $data
     * @return array
     */
    public function payNow(array $data): array
    {
        // Format phone numbers according to strict Kowri requirements
        // walletRef (233XXXXXXXXX) vs customerMobile (0XXXXXXXXX)
        $rawPhone = preg_replace('/[^0-9]/', '', $data['phone']);
        $last9Digits = substr($rawPhone, -9);
        
        $walletRef = '233' . $last9Digits;
        $customerMobile = '0' . $last9Digits;

        $payload = [
            'requestId' => uniqid('req_', true),
            'appReference' => $this->appReference,
            'secret' => $this->secret,
            'amount' => number_format((float) $data['amount'], 2, '.', ''),
            'currency' => $data['currency'] ?? 'GHS',
            'customerName' => $data['name'] ?? 'Customer',
            'customerMobile' => $customerMobile,
            'walletRef' => $walletRef,
            'provider' => $this->mapProvider($data['network'] ?? ''),
            'reference' => $data['order_id'],
            'transactionId' => '',
            'metadata' => [
                [
                    'key' => 'webhookUrl',
                    'value' => $data['webhook_url'] ?? $_ENV['PAYMENT_CALLBACK_URL'] ?? ''
                ]
            ]
        ];

        $response = $this->makeRequest('/webpos/payNow', $payload);

        if (!empty($response['success']) && $response['success'] === true) {
            $result = $response['result'];
            return [
                'success' => true,
                'status' => $this->mapStatus($result['status'] ?? 'PENDING'),
                // MoMo returns callerTransId, Card returns token
                'token' => $result['token'] ?? $result['callerTransId'] ?? null,
                'reference' => $result['reference'] ?? $data['order_id'],
                'redirect_url' => $result['redirectUrl'] ?? null,
                'raw' => $response
            ];
        }

        throw new Exception("Kowri PayNow Error: " . ($response['statusMessage'] ?? json_encode($response)));
    }

    /**
     * Legacy Direct Charge - Wraps payNow for compatibility
     */
    public function directCharge(array $data): array
    {
        return $this->payNow($data);
    }

    /**
     * Query status by Merchant Order ID
     */
    public function queryByOrderId(string $orderId): array
    {
        $payload = [
            'requestId' => uniqid('req_', true),
            'appReference' => $this->appReference,
            'secret' => $this->secret,
            'merchantOrderId' => $orderId
        ];

        $response = $this->makeRequest('/webpos/getInvoiceSummary', $payload);

        $status = $response['status'] ?? 'UNKNOWN';

        return [
            'status' => $this->mapStatus($status),
            'raw_status' => $status,
            'amount' => $response['amount'] ?? 0,
            'order_id' => $response['orderId'] ?? null,
            'transaction_id' => $response['transactionId'] ?? null,
            'success' => $response['success'] ?? false,
            'raw' => $response
        ];
    }

    /**
     * Map simple network names to Kowri provider codes
     */
    private function mapProvider(string $network): string
    {
        return match (strtoupper($network)) {
            'CARD' => 'CARD',
            'MTN', 'MTN_MONEY' => 'MTN_MONEY',
            'VODAFONE', 'VODAFONE_CASH', 'TELECEL' => 'VODAFONE_CASH',
            'AIRTELTIGO', 'AIRTELTIGO_MONEY', 'AT' => 'AIRTELTIGO_MONEY',
            default => 'MTN_MONEY',
        };
    }

    /**
     * Map Kowri status values to internal application status
     */
    private function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'FULFILLED' => 'paid',
            'UNFULFILLED_ERROR' => 'failed',
            'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Make an HTTP POST request to Kowri API
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'App-Reference: ' . $this->appReference,
            'Secret: ' . $this->secret,
            'appId: ' . $this->appId,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Kowri API Connection Error: {$curlError}");
        }

        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Kowri Invalid JSON Response (HTTP {$httpCode}): " . substr($result, 0, 500));
        }

        return $decoded;
    }
}
