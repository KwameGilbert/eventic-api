<?php

namespace App\Services;

use Exception;

class ExpressPayService
{
    private string $merchantId;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = $_ENV['EXPRESSPAY_MERCHANT_ID'] ?? '281147842201';
        $this->apiKey = $_ENV['EXPRESSPAY_API_KEY'] ?? 'vUCB6DlImp2O8BNeyv2ek-b9KpTBSOlvHhOWiC2reD-aH7dbDtqznC3g4aHg7WR-fK1rMf2DFSfp712GIvo';
        // Use sandbox URL by default for now, can be switched via ENV
        $this->baseUrl = $_ENV['EXPRESSPAY_BASE_URL'] ?? 'https://sandbox.expresspaygh.com/api';
    }

    /**
     * Submit an invoice/order to ExpressPay
     * 
     * @param array $data Order details
     * @return array Response from ExpressPay
     * @throws Exception
     */
    public function submitInvoice(array $data): array
    {
        $payload = [
            'merchant-id' => $this->merchantId,
            'api-key' => $this->apiKey,
            'firstname' => $data['firstname'] ?? 'Guest',
            'lastname' => $data['lastname'] ?? 'Voter',
            'email' => $data['email'],
            'phonenumber' => $data['phonenumber'] ?? '0000000000',
            'currency' => $data['currency'] ?? 'GHS',
            'amount' => $data['amount'],
            'order-id' => $data['order_id'],
            'redirect-url' => $data['redirect_url'],
            'ipn-url' => $data['ipn_url'] ?? null,
            'order-desc' => $data['description'] ?? 'Vote Payment',
        ];

        // Remove null values
        $payload = array_filter($payload, function($v) { return !is_null($v); });

        $response = $this->makeRequest('/submit.php', $payload);

        if (isset($response['status']) && $response['status'] === 1) {
            return [
                'success' => true,
                'token' => $response['token'],
                'redirect_url' => "https://sandbox.expresspaygh.com/api/checkout.php?token=" . $response['token']
            ];
        }

        throw new Exception($response['message'] ?? 'Failed to initiate payment');
    }

    /**
     * Query transaction status
     * 
     * @param string $token Transaction token
     * @return array Transaction status details
     * @throws Exception
     */
    public function queryTransaction(string $token): array
    {
        $payload = [
            'merchant-id' => $this->merchantId,
            'api-key' => $this->apiKey,
            'token' => $token
        ];

        $response = $this->makeRequest('/query.php', $payload);

        if (isset($response['result']) && $response['result'] === 1) {
            return [
                'status' => 'success', // Mapped to our system's status
                'amount' => $response['amount'] ?? 0,
                'currency' => $response['currency'] ?? 'GHS',
                'order_id' => $response['order-id'] ?? null,
                'raw' => $response
            ];
        }

        return [
            'status' => 'failed',
            'raw' => $response
        ];
    }

    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Build generic headers if needed, ExpressPay usually accepts form-urlencoded
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        // SSL verification (disable for dev/sandbox if needed, generic advice)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }

        $decoded = json_decode($result, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Sometimes they return non-JSON on error or plain text, simplistic handling
            throw new Exception("Invalid JSON response: " . $result);
        }

        return $decoded;
    }
}
