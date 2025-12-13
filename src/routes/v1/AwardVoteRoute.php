<?php

/**
 * Award Vote Routes (v1 API)
 */

use App\Controllers\AwardVoteController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app): void {
    $voteController = $app->getContainer()->get(AwardVoteController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes (no auth required - voting is open to everyone)
    $app->group('/v1', function ($group) use ($voteController) {
        // Initiate a vote (create pending vote)
        $group->post('/nominees/{nomineeId}/vote', [$voteController, 'initiate']);

        // Confirm vote payment (callback from payment gateway)
        $group->post('/votes/confirm', [$voteController, 'confirmPayment']);

        // Get vote details by reference
        $group->get('/votes/reference/{reference}', [$voteController, 'getByReference']);

        // Get votes for a nominee
        // Query Params: ?status=pending|paid
        $group->get('/nominees/{nomineeId}/votes', [$voteController, 'getByNominee']);

        // Get votes for a category
        // Query Params: ?status=pending|paid
        $group->get('/award-categories/{categoryId}/votes', [$voteController, 'getByCategory']);

        // Get category leaderboard (public - for display)
        $group->get('/award-categories/{categoryId}/leaderboard', [$voteController, 'getLeaderboard']);
    });

    // Protected routes (auth required - organizer/admin only)
    $app->group('/v1/events', function ($group) use ($voteController) {
        // Get all votes for an event (organizer only)
        // Query Params: ?status=pending|paid
        $group->get('/{eventId}/votes', [$voteController, 'getByEvent']);

        // Get comprehensive event vote statistics (organizer only)
        $group->get('/{eventId}/vote-stats', [$voteController, 'getEventStats']);
    })->add($authMiddleware);
};
