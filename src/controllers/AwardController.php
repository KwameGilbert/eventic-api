<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Award;
use App\Models\AwardImage;
use App\Models\Organizer;
use App\Services\UploadService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * AwardController
 * Handles awards show operations (completely separate from Events/Ticketing)
 */
class AwardController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    /**
     * Get all awards (with optional filtering)
     * GET /v1/awards
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = Award::with(['categories.nominees', 'organizer.user', 'images']);

            // Filter by status (default to published for public list)
            if (isset($queryParams['status'])) {
                $query->where('status', $queryParams['status']);
            } else {
                // Default to published awards for public endpoint
                $query->where('status', Award::STATUS_PUBLISHED);
            }

            // Filter by organizer
            if (isset($queryParams['organizer_id'])) {
                $query->where('organizer_id', $queryParams['organizer_id']);
            }

            // Filter upcoming only
            if (isset($queryParams['upcoming']) && $queryParams['upcoming'] === 'true') {
                $query->where('ceremony_date', '>', \Illuminate\Support\Carbon::now());
            }

            // Filter voting open
            if (isset($queryParams['voting_open']) && $queryParams['voting_open'] === 'true') {
                $query->votingOpen();
            }

            // Search by title or description
            if (isset($queryParams['search']) && !empty($queryParams['search'])) {
                $search = $queryParams['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Pagination
            $page = (int) ($queryParams['page'] ?? 1);
            $perPage = (int) ($queryParams['per_page'] ?? 20);
            $offset = ($page - 1) * $perPage;

            $totalCount = $query->count();
            $awards = $query->orderBy('ceremony_date', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            // Format awards for frontend compatibility
            $formattedAwards = $awards->map(function ($award) {
                return $award->getFullDetails();
            });

            return ResponseHelper::success($response, 'Awards fetched successfully', [
                'awards' => $formattedAwards->toArray(),
                'count' => $awards->count(),
                'total' => $totalCount,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalCount / $perPage),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch awards', 500, $e->getMessage());
        }
    }

    /**
     * Get featured awards
     * GET /v1/awards/featured
     */
    public function featured(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            
            // Pagination parameters
            $page = (int) ($queryParams['page'] ?? 1);
            $perPage = (int) ($queryParams['per_page'] ?? 20);
            $offset = ($page - 1) * $perPage;

            // Build query for featured awards
            $query = Award::with(['categories.nominees', 'organizer.user', 'images'])
                ->where('status', Award::STATUS_PUBLISHED)
                ->where('is_featured', true);

            // Filter upcoming only (optional)
            if (isset($queryParams['upcoming']) && $queryParams['upcoming'] === 'true') {
                $query->where('ceremony_date', '>', \Illuminate\Support\Carbon::now());
            }

            // Filter voting open (optional)
            if (isset($queryParams['voting_open']) && $queryParams['voting_open'] === 'true') {
                $query->votingOpen();
            }

            // Get total count before pagination
            $totalCount = $query->count();

            // Get paginated results
            $awards = $query->orderBy('ceremony_date', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            // Format awards for frontend compatibility (same as index)
            $formattedAwards = $awards->map(function ($award) {
                return $award->getFullDetails();
            });

            return ResponseHelper::success($response, 'Featured awards fetched successfully', [
                'awards' => $formattedAwards->toArray(),
                'count' => $awards->count(),
                'total' => $totalCount,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalCount / $perPage),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch featured awards', 500, $e->getMessage());
        }
    }

    /**
     * Get single award by ID or slug
     * GET /v1/awards/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $identifier = $args['id'];
            
            // Get user info if authenticated (optional for public endpoint)
            $userRole = $request->getAttribute('user_role');
            $userId = $request->getAttribute('user_id');

            // Try to find by ID first, then by slug
            if (is_numeric($identifier)) {
                $award = Award::with(['organizer.user', 'categories.nominees', 'images'])->find($identifier);
            } else {
                $award = Award::with(['organizer.user', 'categories.nominees', 'images'])
                    ->where('slug', $identifier)
                    ->first();
            }

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Increment views
            $award->increment('views');

            return ResponseHelper::success($response, 'Award fetched successfully', $award->getFullDetails($userRole, $userId));
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch award', 500, $e->getMessage());
        }
    }

    /**
     * Create new award
     * POST /v1/awards
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');
            $uploadedFiles = $request->getUploadedFiles();

            // Get organizer for the user
            $organizer = Organizer::where('user_id', $user->id)->first();
            if (!$organizer && $user->role !== 'admin') {
                return ResponseHelper::error($response, 'Only organizers can create awards', 403);
            }

            // Set organizer_id from authenticated user's organizer profile
            if ($organizer) {
                $data['organizer_id'] = $organizer->id;
            }

            // Validate required fields
            $requiredFields = ['title', 'ceremony_date', 'voting_start', 'voting_end'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ResponseHelper::error($response, "Field '$field' is required", 400);
                }
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = Award::STATUS_DRAFT;
            }

            // Validate status value
            $validStatuses = [Award::STATUS_DRAFT, Award::STATUS_PUBLISHED, Award::STATUS_CLOSED, Award::STATUS_COMPLETED];
            if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
                return ResponseHelper::error($response, "Invalid status value. Allowed values: draft, published, closed, completed", 400);
            }

            // Set default location values if not provided
            if (!isset($data['country'])) {
                $data['country'] = 'Ghana';
            }
            if (!isset($data['region'])) {
                $data['region'] = 'Greater Accra';
            }
            if (!isset($data['city'])) {
                $data['city'] = 'Accra';
            }

            // Handle banner image upload using UploadService
            if (isset($uploadedFiles['banner_image'])) {
                $bannerImage = $uploadedFiles['banner_image'];
                if ($bannerImage->getError() === UPLOAD_ERR_OK) {
                    try {
                        $data['banner_image'] = $this->uploadService->uploadFile($bannerImage, 'banner', 'awards');
                    } catch (Exception $e) {
                        return ResponseHelper::error($response, $e->getMessage(), 400);
                    }
                }
            }

            $award = Award::create($data);

            // Handle award photos upload (multiple) using UploadService
            if (isset($uploadedFiles['award_photos']) && is_array($uploadedFiles['award_photos'])) {
                foreach ($uploadedFiles['award_photos'] as $photo) {
                    if ($photo->getError() === UPLOAD_ERR_OK) {
                        try {
                            $imagePath = $this->uploadService->uploadFile($photo, 'image', 'awards');
                            AwardImage::create([
                                'award_id' => $award->id,
                                'image_path' => $imagePath,
                            ]);
                        } catch (Exception $e) {
                            // Log error but continue with other files
                            error_log("Failed to upload award photo: " . $e->getMessage());
                        }
                    }
                }
            }

            return ResponseHelper::success($response, 'Award created successfully', $award->getFullDetails(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create award', 500, $e->getMessage());
        }
    }

    /**
     * Update award
     * PUT /v1/awards/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $award = Award::find($id);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Authorization: Check if user is admin or the award organizer
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || $organizer->id !== $award->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this award', 403);
                }
            }

            // Update slug if title changes and slug isn't manually provided
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            }

            // Validate status value if provided
            if (isset($data['status'])) {
                $validStatuses = [Award::STATUS_DRAFT, Award::STATUS_PUBLISHED, Award::STATUS_CLOSED, Award::STATUS_COMPLETED];
                if (!in_array($data['status'], $validStatuses)) {
                    return ResponseHelper::error($response, "Invalid status value. Allowed values: draft, published, closed, completed", 400);
                }
            }

            // Handle banner image upload using UploadService
            if (isset($uploadedFiles['banner_image'])) {
                $bannerImage = $uploadedFiles['banner_image'];
                if ($bannerImage->getError() === UPLOAD_ERR_OK) {
                    try {
                        $data['banner_image'] = $this->uploadService->replaceFile(
                            $bannerImage,
                            $award->banner_image,
                            'banner',
                            'awards'
                        );
                    } catch (Exception $e) {
                        return ResponseHelper::error($response, $e->getMessage(), 400);
                    }
                }
            }

            $award->update($data);

            // Handle award photos upload (multiple) using UploadService - these are added to existing photos
            if (isset($uploadedFiles['award_photos']) && is_array($uploadedFiles['award_photos'])) {
                foreach ($uploadedFiles['award_photos'] as $photo) {
                    if ($photo->getError() === UPLOAD_ERR_OK) {
                        try {
                            $imagePath = $this->uploadService->uploadFile($photo, 'image', 'awards');
                            AwardImage::create([
                                'award_id' => $award->id,
                                'image_path' => $imagePath,
                            ]);
                        } catch (Exception $e) {
                            // Log error but continue with other files
                            error_log("Failed to upload award photo: " . $e->getMessage());
                        }
                    }
                }
            }

            return ResponseHelper::success($response, 'Award updated successfully', $award->getFullDetails());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update award', 500, $e->getMessage());
        }
    }

    /**
     * Delete award
     * DELETE /v1/awards/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $award = Award::find($id);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Authorization: Check if user is admin or the award organizer
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || $organizer->id !== $award->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this award', 403);
                }
            }

            // Validation: Check if award has any votes
            if ($award->votes()->where('status', 'paid')->exists()) {
                return ResponseHelper::error($response, 'Cannot delete award with existing votes', 400);
            }

            $award->delete();

            return ResponseHelper::success($response, 'Award deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete award', 500, $e->getMessage());
        }
    }

    /**
     * Search awards
     * GET /v1/awards/search
     */
    public function search(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['query'] ?? '';

            if (empty($query)) {
                return ResponseHelper::error($response, 'Search query is required', 400);
            }

            $awards = Award::with(['categories.nominees', 'organizer.user'])
                ->where('status', Award::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->get();

            $formattedAwards = $awards->map(function ($award) {
                return $award->getFullDetails();
            });

            return ResponseHelper::success($response, 'Awards found', [
                'awards' => $formattedAwards->toArray(),
                'count' => $awards->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to search awards', 500, $e->getMessage());
        }
    }

    /**
     * Get overall leaderboard for an award
     * GET /v1/awards/{id}/leaderboard
     */
    public function leaderboard(Request $request, Response $response, array $args): Response
    {
        try {
            $awardId = $args['id'];
            $award = Award::with(['categories.nominees'])->find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            $leaderboard = [];

            foreach ($award->categories as $category) {
                $categoryLeaderboard = $category->nominees->map(function ($nominee) {
                    return [
                        'nominee_id' => $nominee->id,
                        'nominee_name' => $nominee->name,
                        'nominee_image' => $nominee->image,
                        'total_votes' => $nominee->getTotalVotes(),
                    ];
                })->sortByDesc('total_votes')->values()->toArray();

                $leaderboard[] = [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'nominees' => $categoryLeaderboard,
                ];
            }

            return ResponseHelper::success($response, 'Leaderboard fetched successfully', [
                'award' => [
                    'id' => $award->id,
                    'title' => $award->title,
                    'total_votes' => $award->getTotalVotes(),
                ],
                'leaderboard' => $leaderboard,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch leaderboard', 500, $e->getMessage());
        }
    }

    /**
     * Toggle show_results flag for an award
     * PUT /v1/awards/{id}/toggle-results
     */
    public function toggleShowResults(Request $request, Response $response, array $args): Response
    {
        try {
            $awardId = (int) $args['id'];
            $userId = $request->getAttribute('user_id');
            $userRole = $request->getAttribute('user_role');

            // Find the award
            $award = Award::find($awardId);
            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Verify organizer ownership
            if ($userRole !== 'organizer') {
                return ResponseHelper::error($response, 'Only organizers can modify award settings', 403);
            }

            $organizer = Organizer::where('user_id', $userId)->first();
            if (!$organizer || $award->organizer_id !== $organizer->id) {
                return ResponseHelper::error($response, 'You do not have permission to modify this award', 403);
            }

            // Toggle the show_results flag
            $newValue = $award->toggleShowResults();

            return ResponseHelper::success($response, 'Results visibility updated successfully', [
                'show_results' => $newValue,
                'message' => $newValue ? 'Voting results are now visible to the public' : 'Voting results are now hidden from the public'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to toggle results visibility', 500, $e->getMessage());
        }
    }
}
