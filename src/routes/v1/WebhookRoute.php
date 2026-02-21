<?php

/**
 * Webhook Routes (v1 API)
 */

use App\Controllers\WebhookController;
use Slim\App;

return function (App $app): void {
    $webhookController = $app->getContainer()->get(WebhookController::class);
    
    // Centralized Webhook Endpoints (Public - secured by provider signature/verification if implemented)
    $app->group('/v1/webhooks', function ($group) use ($webhookController) {
        
        // Kowri Webhooks
        $group->group('/kowri', function ($group) use ($webhookController) {
            $group->post('/order', [$webhookController, 'handleOrder']);
            $group->post('/vote', [$webhookController, 'handleVote']);
        });
        
    });
};
