<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helper\ResponseHelper;
use App\Models\AwardNominee;
use App\Models\AwardCategory;
use App\Models\Event;
use App\Models\Organizer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class AwardNomineeController
{
    /**
     * Get all nominees for a category
     * GET /v1/award-categories/{categoryId}/nominees
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $categoryId = $args['categoryId'];
            $queryParams = $request->getQueryParams();
            $includeStats = isset($queryParams['include_stats']) && $queryParams['include_stats'] === 'true';

            // Verify category exists
            $category = AwardCategory::find($categoryId);
            if (!$category) {
                return ResponseHelper::error($response, 'Award category not found', 404);
            }

            // Get nominees ordered by display_order
            $nominees = AwardNominee::where('category_id', $categoryId)
                ->ordered()
                ->get();

            if ($includeStats) {
                $nomineesData = $nominees->map(function ($nominee) {
                    return $nominee->getDetailsWithStats();
                });
            } else {
                $nomineesData = $nominees->map(function ($nominee) {
                    return [
                        'id' => $nominee->id,
                        'category_id' => $nominee->category_id,
                        'event_id' => $nominee->event_id,
                        'name' => $nominee->name,
                        'description' => $nominee->description,
                        'image' => $nominee->image,
                        'display_order' => $nominee->display_order,
                        'created_at' => $nominee->created_at?->toIso8601String(),
                        'updated_at' => $nominee->updated_at?->toIso8601String(),
                    ];
                });
            }

            return ResponseHelper::success($response, 'Nominees fetched successfully', $nomineesData->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch nominees', 500, $e->getMessage());
        }
    }

    /**
     * Get all nominees for an event
     * GET /v1/events/{eventId}/nominees
     */
    public function getByEvent(Request $request, Response $response, array $args): Response
    {
        try {
            $eventId = $args['eventId'];
            $queryParams = $request->getQueryParams();
            $includeStats = isset($queryParams['include_stats']) && $queryParams['include_stats'] === 'true';

            // Verify event exists
            $event = Event::find($eventId);
            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Get nominees with category info
            $nominees = AwardNominee::with('category')
                ->where('event_id', $eventId)
                ->ordered()
                ->get();

            if ($includeStats) {
                $nomineesData = $nominees->map(function ($nominee) {
                    $stats = $nominee->getDetailsWithStats();
                    $stats['category_name'] = $nominee->category ? $nominee->category->name : null;
                    return $stats;
                });
            } else {
                $nomineesData = $nominees->map(function ($nominee) {
                    return [
                        'id' => $nominee->id,
                        'category_id' => $nominee->category_id,
                        'category_name' => $nominee->category ? $nominee->category->name : null,
                        'event_id' => $nominee->event_id,
                        'name' => $nominee->name,
                        'description' => $nominee->description,
                        'image' => $nominee->image,
                        'display_order' => $nominee->display_order,
                    ];
                });
            }

            return ResponseHelper::success($response, 'Event nominees fetched successfully', $nomineesData->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event nominees', 500, $e->getMessage());
        }
    }

    /**
     * Get single nominee details
     * GET /v1/nominees/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $queryParams = $request->getQueryParams();
            $includeStats = isset($queryParams['include_stats']) && $queryParams['include_stats'] === 'true';

            $nominee = AwardNominee::with(['category', 'event'])->find($id);

            if (!$nominee) {
                return ResponseHelper::error($response, 'Nominee not found', 404);
            }

            $nomineeData = $includeStats 
                ? $nominee->getDetailsWithStats()
                : [
                    'id' => $nominee->id,
                    'category_id' => $nominee->category_id,
                    'category_name' => $nominee->category ? $nominee->category->name : null,
                    'event_id' => $nominee->event_id,
                    'event_name' => $nominee->event ? $nominee->event->title : null,
                    'name' => $nominee->name,
                    'description' => $nominee->description,
                    'image' => $nominee->image,
                    'display_order' => $nominee->display_order,
                    'created_at' => $nominee->created_at?->toIso8601String(),
                    'updated_at' => $nominee->updated_at?->toIso8601String(),
                ];

            return ResponseHelper::success($response, 'Nominee fetched successfully', $nomineeData);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch nominee', 500, $e->getMessage());
        }
    }

    /**
     * Create new nominee
     * POST /v1/award-categories/{categoryId}/nominees
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $categoryId = $args['categoryId'];
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');
            $uploadedFiles = $request->getUploadedFiles();

            // Verify category exists
            $category = AwardCategory::with('event')->find($categoryId);
            if (!$category) {
                return ResponseHelper::error($response, 'Award category not found', 404);
            }

            // Authorization: Check if user owns the event
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || !$category->event || $organizer->id !== $category->event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this event', 403);
                }
            }

            // Validate required fields
            if (empty($data['name'])) {
                return ResponseHelper::error($response, 'Nominee name is required', 400);
            }

            // Set category_id and event_id
            $data['category_id'] = $categoryId;
            $data['event_id'] = $category->event_id;

            // Set default display_order
            if (!isset($data['display_order'])) {
                $maxOrder = AwardNominee::where('category_id', $categoryId)->max('display_order') ?? 0;
                $data['display_order'] = $maxOrder + 1;
            }

            // Handle image upload
            if (isset($uploadedFiles['image'])) {
                $image = $uploadedFiles['image'];

                if ($image->getError() === UPLOAD_ERR_OK) {
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $mimeType = $image->getClientMediaType();

                    if (!in_array($mimeType, $allowedTypes)) {
                        return ResponseHelper::error($response, 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP', 400);
                    }

                    // Validate file size (max 5MB)
                    if ($image->getSize() > 5 * 1024 * 1024) {
                        return ResponseHelper::error($response, 'Image size must be less than 5MB', 400);
                    }

                    // Create upload directory
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/nominees';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Generate unique filename
                    $extension = pathinfo($image->getClientFilename(), PATHINFO_EXTENSION);
                    $filename = 'nominee_' . uniqid() . '_' . time() . '.' . $extension;
                    $filepath = $uploadDir . '/' . $filename;

                    // Move uploaded file
                    $image->moveTo($filepath);

                    // Store relative path
                    $data['image'] = '/uploads/nominees/' . $filename;
                }
            }

            $nominee = AwardNominee::create($data);

            return ResponseHelper::success($response, 'Nominee created successfully', $nominee->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create nominee', 500, $e->getMessage());
        }
    }

    /**
     * Update nominee
     * PUT /v1/nominees/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');
            $uploadedFiles = $request->getUploadedFiles();

            $nominee = AwardNominee::with(['category.event'])->find($id);

            if (!$nominee) {
                return ResponseHelper::error($response, 'Nominee not found', 404);
            }

            // Authorization: Check if user owns the event
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                $event = $nominee->category ? $nominee->category->event : null;
                if (!$organizer || !$event || $organizer->id !== $event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this nominee', 403);
                }
            }

            // Don't allow changing category_id or event_id
            unset($data['category_id'], $data['event_id']);

            // Handle image upload
            if (isset($uploadedFiles['image'])) {
                $image = $uploadedFiles['image'];

                if ($image->getError() === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $mimeType = $image->getClientMediaType();

                    if (!in_array($mimeType, $allowedTypes)) {
                        return ResponseHelper::error($response, 'Invalid image type', 400);
                    }

                    if ($image->getSize() > 5 * 1024 * 1024) {
                        return ResponseHelper::error($response, 'Image size must be less than 5MB', 400);
                    }

                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/nominees';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Delete old image if exists
                    if ($nominee->image && file_exists(dirname(__DIR__, 2) . '/public' . $nominee->image)) {
                        unlink(dirname(__DIR__, 2) . '/public' . $nominee->image);
                    }

                    $extension = pathinfo($image->getClientFilename(), PATHINFO_EXTENSION);
                    $filename = 'nominee_' . uniqid() . '_' . time() . '.' . $extension;
                    $filepath = $uploadDir . '/' . $filename;

                    $image->moveTo($filepath);
                    $data['image'] = '/uploads/nominees/' . $filename;
                }
            }

            $nominee->update($data);

            return ResponseHelper::success($response, 'Nominee updated successfully', $nominee->fresh()->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update nominee', 500, $e->getMessage());
        }
    }

    /**
     * Delete nominee
     * DELETE /v1/nominees/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $user = $request->getAttribute('user');

            $nominee = AwardNominee::with(['category.event'])->find($id);

            if (!$nominee) {
                return ResponseHelper::error($response, 'Nominee not found', 404);
            }

            // Authorization
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                $event = $nominee->category ? $nominee->category->event : null;
                if (!$organizer || !$event || $organizer->id !== $event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized', 403);
                }
            }

            // Check if nominee has votes
            $voteCount = $nominee->votes()->where('status', 'paid')->count();
            if ($voteCount > 0) {
                return ResponseHelper::error($response, 'Cannot delete nominee with paid votes', 400);
            }

            // Delete image if exists
            if ($nominee->image && file_exists(dirname(__DIR__, 2) . '/public' . $nominee->image)) {
                unlink(dirname(__DIR__, 2) . '/public' . $nominee->image);
            }

            $nominee->delete();

            return ResponseHelper::success($response, 'Nominee deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete nominee', 500, $e->getMessage());
        }
    }

    /**
     * Get nominee vote statistics
     * GET /v1/nominees/{id}/stats
     */
    public function getStats(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];

            $nominee = AwardNominee::with('category')->find($id);

            if (!$nominee) {
                return ResponseHelper::error($response, 'Nominee not found', 404);
            }

            $stats = [
                'total_votes' => $nominee->getTotalVotes(),
                'total_revenue' => $nominee->getTotalRevenue(),
                'paid_votes_count' => $nominee->votes()->where('status', 'paid')->count(),
                'pending_votes_count' => $nominee->votes()->where('status', 'pending')->count(),
            ];

            return ResponseHelper::success($response, 'Nominee statistics fetched successfully', $stats);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch nominee statistics', 500, $e->getMessage());
        }
    }

    /**
     * Reorder nominees in a category
     * POST /v1/award-categories/{categoryId}/nominees/reorder
     */
    public function reorder(Request $request, Response $response, array $args): Response
    {
        try {
            $categoryId = $args['categoryId'];
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Verify category exists
            $category = AwardCategory::with('event')->find($categoryId);
            if (!$category) {
                return ResponseHelper::error($response, 'Category not found', 404);
            }

            // Authorization
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || !$category->event || $organizer->id !== $category->event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized', 403);
                }
            }

            // Validate request
            if (!isset($data['order']) || !is_array($data['order'])) {
                return ResponseHelper::error($response, 'Order array is required', 400);
            }

            // Update display_order for each nominee
            foreach ($data['order'] as $index => $nomineeId) {
                AwardNominee::where('id', $nomineeId)
                    ->where('category_id', $categoryId)
                    ->update(['display_order' => $index]);
            }

            $nominees = AwardNominee::where('category_id', $categoryId)
                ->ordered()
                ->get();

            return ResponseHelper::success($response, 'Nominees reordered successfully', $nominees->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reorder nominees', 500, $e->getMessage());
        }
    }
}
