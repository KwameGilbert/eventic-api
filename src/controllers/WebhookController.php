<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CallbackService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * WebhookController
 * 
 * Handles incoming webhooks from external providers (e.g. Kowri).
 * These routes are grouped and managed centrally.
 */
class WebhookController
{
    private CallbackService $callbackService;

    public function __construct(CallbackService $callbackService)
    {
        $this->callbackService = $callbackService;
    }

    /**
     * Handle Kowri Order Webhook
     * POST /v1/webhooks/kowri/order
     */
    public function handleOrder(Request $request, Response $response): Response
    {
        try {
            $payload = $this->getPayload($request);
            error_log("Kowri Order Webhook received: " . json_encode($payload));
            
            $this->callbackService->handleOrderWebhook($payload);
            
            return ResponseHelper::success($response, 'Webhook processed successfully');
        } catch (Exception $e) {
            error_log("Order Webhook Error: " . $e->getMessage());
            // Most providers expect a 200/OK even if processing fails internally 
            // once the payload is correctly received to prevent infinite retries.
            return ResponseHelper::success($response, 'Handled with error: ' . $e->getMessage());
        }
    }

    /**
     * Handle Kowri Vote Webhook (IPN)
     * POST /v1/webhooks/kowri/vote
     */
    public function handleVote(Request $request, Response $response): Response
    {
        try {
            $payload = $this->getPayload($request);
            error_log("Kowri Vote Webhook received: " . json_encode($payload));
            
            $this->callbackService->handleVoteWebhook($payload);
            
            return ResponseHelper::success($response, 'Webhook processed successfully');
        } catch (Exception $e) {
            error_log("Vote Webhook Error: " . $e->getMessage());
            return ResponseHelper::success($response, 'Handled with error: ' . $e->getMessage());
        }
    }

    /**
     * Extracts payload from request, accounting for different content types
     */
    private function getPayload(Request $request): array
    {
        $payload = $request->getParsedBody();
        
        // Fallback for raw JSON if PHP's parsed body is empty
        if (empty($payload)) {
            $rawBody = file_get_contents('php://input');
            $payload = json_decode($rawBody, true);
        }
        
        return $payload ?? [];
    }
}
