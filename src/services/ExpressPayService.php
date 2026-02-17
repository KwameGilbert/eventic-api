<?php

namespace App\Services;

use Exception;

/**
 * ExpressPay Merchant API Direct Service
 * 
 * Implements the Merchant Direct API for processing payments on-site
 * without redirecting to the ExpressPay checkout page.
 * 
 * Flow:
 *   Step 1: POST to /api/direct/submit.php → get token
 *   Step 2: POST to /api/checkout.php with token + mobile money details → initiate charge
 *   Step 3: ExpressPay POSTs to post-url when payment completes (for pending MoMo)
 *   Step 4: POST to /api/query.php to verify transaction status
 * 
 * @see https://expresspaygh.com/developers/docs/accept-payments/merchant-direct-api
 */
class ExpressPayService
{
    private string $merchantId;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = $_ENV['EXPRESSPAY_MERCHANT_ID'] ?? '';
        $this->apiKey = $_ENV['EXPRESSPAY_API_KEY'] ?? '';
        // Base URL should be like https://sandbox.expresspaygh.com/api or https://expresspaygh.com/api
        $this->baseUrl = $_ENV['EXPRESSPAY_BASE_URL'] ?? 'https://sandbox.expresspaygh.com/api';
    }

    /**
     * STEP 1: Submit invoice to get a token
     * 
     * POST to /api/submit.php
     * 
     * The submit endpoint is the same for both standard and Merchant Direct API.
     * The "Direct" part refers to Step 2 (charging directly via checkout.php
     * instead of redirecting the user to the ExpressPay page).
     * 
     * @param array $data Order details
     * @return array Contains 'token' and 'order_id' on success
     * @throws Exception
     */
    public function submit(array $data): array
    {
        $payload = [
            'merchant-id' => $this->merchantId,
            'api-key' => $this->apiKey,
            'currency' => $data['currency'] ?? 'GHS',
            'amount' => number_format((float) $data['amount'], 2, '.', ''),
            'order-id' => $data['order_id'],
            'order-desc' => $data['description'] ?? 'Payment',
            'redirect-url' => $data['redirect_url'] ?? ($_ENV['FRONTEND_URL'] ?? 'http://localhost:5173'),
            'post-url' => $data['post_url'] ?? $data['ipn_url'] ?? '',
            'firstname' => $data['firstname'] ?? 'Guest',
            'lastname' => $data['lastname'] ?? 'User',
            'email' => $data['email'] ?? 'guest@eventic.com',
            'phonenumber' => $data['phonenumber'] ?? $data['momo_number'] ?? '0000000000',
        ];

        // Remove empty values
        $payload = array_filter($payload, function($v) { return $v !== '' && !is_null($v); });

        // Use /direct/submit.php for Merchant Direct API
        // This creates a "direct" token that checkout.php accepts for API charging
        $response = $this->makeRequest('/direct/submit.php', $payload);

        // Response: status (1=Success, 2=Invalid Credentials, 3=Invalid Request, 4=Invalid IP)
        $status = (int) ($response['status'] ?? 0);
        
        if ($status === 1 && !empty($response['token'])) {
            return [
                'success' => true,
                'token' => $response['token'],
                'order_id' => $response['order-id'] ?? $data['order_id'],
            ];
        }

        $errorMap = [
            2 => 'Invalid credentials',
            3 => 'Invalid request',
            4 => 'Invalid IP',
        ];

        $label = $errorMap[$status] ?? 'Unknown error';
        $apiMsg = $response['message'] ?? 'No message';
        throw new Exception("ExpressPay Submit Error (status={$status}, {$label}): {$apiMsg} | Full response: " . json_encode($response));
    }

    /**
     * STEP 2: Checkout - Initiate MoMo charge directly
     * 
     * POST to /api/checkout.php with token + mobile money details
     * 
     * For MoMo: token, mobile-number, mobile-network, mobile-auth-token (if Vodafone)
     * 
     * Response result codes:
     *   1 = Approved
     *   2 = Declined
     *   3 = Error in transaction data or system error
     *   4 = Pending (Final status via post-url)
     * 
     * @param string $token Token from Step 1
     * @param string $mobileNumber Phone number (format: 233XXXXXXXXX or 0XXXXXXXXX)
     * @param string $mobileNetwork Network code: MTN_MM, VODAFONE_CASH, TIGO_CASH, AIRTEL_MM
     * @param string|null $mobileAuthToken Auth/voucher code (required for VODAFONE_CASH)
     * @return array Checkout response
     * @throws Exception
     */
    public function checkoutMoMo(string $token, string $mobileNumber, string $mobileNetwork, ?string $mobileAuthToken = null): array
    {
        $payload = [
            'token' => $token,
            'mobile-number' => $mobileNumber,
            'mobile-network' => $mobileNetwork,
        ];

        // Vodafone/Telecel requires a pre-auth voucher token
        if (!empty($mobileAuthToken)) {
            $payload['mobile-auth-token'] = $mobileAuthToken;
        }

        // POST to /checkout.php with direct token + mobile details
        // The token from /direct/submit.php should make this work as API (not redirect)
        $response = $this->makeRequest('/checkout.php', $payload);

        $result = (int) ($response['result'] ?? 0);

        // result 1 = Approved, 4 = Pending (MoMo user needs to confirm on phone)
        if ($result === 1 || $result === 4) {
            return [
                'success' => true,
                'result' => $result,
                'result_text' => $response['result-text'] ?? ($result === 1 ? 'Approved' : 'Pending'),
                'token' => $response['token'] ?? $token,
                'order_id' => $response['order-id'] ?? null,
                'transaction_id' => $response['transaction-id'] ?? null,
                'currency' => $response['currency'] ?? 'GHS',
                'amount' => $response['amount'] ?? null,
                'date_processed' => $response['date-processed'] ?? null,
            ];
        }

        // result 2 = Declined, 3 = Error
        $resultText = $response['result-text'] ?? 'Payment failed';
        throw new Exception("ExpressPay Checkout Error (result={$result}): {$resultText}");
    }


    /**
     * Full Direct MoMo Payment Flow (Step 1 + Step 2 combined)
     * 
     * 1. Submits invoice to get token
     * 2. Immediately initiates MoMo charge via checkout
     * 
     * @param array $data Payment data including amount, order_id, momo_number, momo_network, etc.
     * @return array Combined response with token, result, etc.
     * @throws Exception
     */
    public function initiateMoMoDirect(array $data): array
    {
        // Step 1: Get token
        $submitResponse = $this->submit($data);
        $token = $submitResponse['token'];

        // Prepare mobile money details
        $mobileNumber = $data['momo_number'] ?? $data['phonenumber'] ?? '';
        $mobileNetwork = $this->mapNetworkCode($data['momo_network'] ?? 'MTN');
        $mobileAuthToken = $data['momo_auth_token'] ?? null;

        // Step 2: Checkout with MoMo
        $checkoutResponse = $this->checkoutMoMo($token, $mobileNumber, $mobileNetwork, $mobileAuthToken);

        return array_merge($checkoutResponse, [
            'token' => $token, // Always use the original token for queries
        ]);
    }


    /**
     * STEP 4: Query transaction status
     * 
     * POST to /api/query.php
     * 
     * Response result codes:
     *   1 = Approved/Success
     *   2 = Declined
     *   3 = Error / No transaction data
     *   4 = Pending
     * 
     * @param string $token Transaction token from Step 1
     * @return array Parsed transaction status
     * @throws Exception
     */
    public function queryTransaction(string $token): array
    {
        $payload = [
            'merchant-id' => $this->merchantId,
            'api-key' => $this->apiKey,
            'token' => $token,
        ];

        $response = $this->makeRequest('/query.php', $payload);

        $result = (int) ($response['result'] ?? 0);
        
        if ($result === 1) {
            return [
                'status' => 'paid',
                'result' => $result,
                'result_text' => $response['result-text'] ?? 'Success',
                'order_id' => $response['order-id'] ?? null,
                'token' => $response['token'] ?? $token,
                'currency' => $response['currency'] ?? 'GHS',
                'amount' => $response['amount'] ?? 0,
                'auth_code' => $response['auth-code'] ?? null,
                'transaction_id' => $response['transaction-id'] ?? null,
                'date_processed' => $response['date-processed'] ?? null,
            ];
        }

        if ($result === 4) {
            return [
                'status' => 'pending',
                'result' => $result,
                'result_text' => $response['result-text'] ?? 'Pending',
                'order_id' => $response['order-id'] ?? null,
                'token' => $response['token'] ?? $token,
            ];
        }

        // result 2 = Declined, 3 = Error/No data
        return [
            'status' => 'failed',
            'result' => $result,
            'result_text' => $response['result-text'] ?? 'Failed',
            'order_id' => $response['order-id'] ?? null,
            'raw' => $response,
        ];
    }


    /**
     * Legacy: Submit invoice for redirect-based checkout (non-direct)
     * Uses the standard /submit.php endpoint (not /direct/submit.php)
     * 
     * @param array $data Order details
     * @return array Response with token and checkout redirect URL
     * @throws Exception
     */
    public function submitInvoice(array $data): array
    {
        $payload = [
            'merchant-id' => $this->merchantId,
            'api-key' => $this->apiKey,
            'firstname' => $data['firstname'] ?? 'Guest',
            'lastname' => $data['lastname'] ?? 'Voter',
            'email' => $data['email'] ?? 'guest@eventic.com',
            'phonenumber' => $data['phonenumber'] ?? '0000000000',
            'currency' => $data['currency'] ?? 'GHS',
            'amount' => number_format((float) $data['amount'], 2, '.', ''),
            'order-id' => $data['order_id'],
            'redirect-url' => $data['redirect_url'] ?? '',
            'post-url' => $data['post_url'] ?? $data['ipn_url'] ?? '',
            'order-desc' => $data['description'] ?? 'Vote Payment',
        ];

        // Remove empty values
        $payload = array_filter($payload, function($v) { return $v !== '' && !is_null($v); });

        // Standard submit uses /submit.php (not /direct/submit.php)
        $response = $this->makeRequest('/submit.php', $payload);

        if (isset($response['status']) && (int)$response['status'] === 1 && !empty($response['token'])) {
            // Checkout URL: {baseUrl}/checkout.php?token={token}
            // SDK uses: sprintf("%scheckout.php?token=%s", base_url, token)
            $checkoutUrl = rtrim($this->baseUrl, '/') . '/checkout.php?token=' . $response['token'];
            return [
                'success' => true,
                'token' => $response['token'],
                'order_id' => $response['order-id'] ?? $data['order_id'],
                'redirect_url' => $checkoutUrl,
            ];
        }

        throw new Exception($response['message'] ?? 'Failed to initiate payment');
    }


    /**
     * Map frontend network names to ExpressPay Merchant Direct API codes
     * 
     * Supported values per docs: "MTN_MM", "AIRTEL_MM", "TIGO_CASH", "VODAFONE_CASH"
     */
    public function mapNetworkCode(string $network): string
    {
        $network = strtolower(trim($network));

        if (str_contains($network, 'mtn')) return 'MTN_MM';
        if (str_contains($network, 'vodafone') || str_contains($network, 'voda')) return 'VODAFONE_CASH';
        if (str_contains($network, 'telecel')) return 'VODAFONE_CASH'; // Telecel is the rebranded Vodafone
        if (str_contains($network, 'tigo')) return 'TIGO_CASH';
        if (str_contains($network, 'airtel')) return 'AIRTEL_MM';
        if (str_contains($network, 'at') || str_contains($network, 'airteltigo')) return 'AIRTEL_MM';

        return 'MTN_MM'; // Default fallback
    }

    
    /**
     * Make an HTTP POST request to ExpressPay API
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;
        
        error_log("=== ExpressPay API Request ===");
        error_log("URL: {$url}");
        error_log("Payload: " . json_encode($data, JSON_PRETTY_PRINT));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        error_log("=== ExpressPay API Response ===");
        error_log("HTTP Code: {$httpCode}");
        error_log("Effective URL: {$effectiveUrl}");
        error_log("cURL Error: {$curlError} (errno: {$curlErrno})");
        error_log("Response Body: " . substr($result ?: '(empty)', 0, 1000));

        if ($curlError) {
            throw new Exception(
                "ExpressPay cURL Error [{$curlErrno}]: {$curlError}" .
                " | URL: {$url}" .
                " | HTTP: {$httpCode}"
            );
        }

        if (empty($result)) {
            throw new Exception(
                "ExpressPay returned empty response" .
                " | URL: {$url}" .
                " | HTTP: {$httpCode}" .
                " | Effective URL: {$effectiveUrl}"
            );
        }

        $decoded = json_decode($result, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(
                "ExpressPay invalid JSON (HTTP {$httpCode}): " . substr($result, 0, 500) .
                " | URL: {$url}" .
                " | JSON Error: " . json_last_error_msg()
            );
        }

        error_log("Decoded Response: " . json_encode($decoded, JSON_PRETTY_PRINT));

        return $decoded;
    }
}
