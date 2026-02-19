<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Models\Award;
use App\Models\AwardCategory;
use App\Models\AwardNominee;
use App\Models\Organizer;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * SearchController
 * Handles global search across different models
 */
class SearchController
{
    /**
     * Global search for events, awards, nominees, categories, and organizers
     * GET /v1/search/global
     */
    public function globalSearch(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['query'] ?? '';

            if (empty($query)) {
                return ResponseHelper::success($response, 'Global search results', [
                    'events' => [],
                    'awards' => [],
                    'contestants' => [],
                    'categories' => [],
                    'organizers' => [],
                    'count' => 0
                ]);
            }

            // 1. Search Events
            $events = Event::whereIn('status', [Event::STATUS_PUBLISHED, Event::STATUS_COMPLETED])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('venue_name', 'LIKE', "%{$query}%");
                })
                ->limit(5)
                ->get();

            // 2. Search Awards
            $awards = Award::whereIn('status', [Award::STATUS_PUBLISHED, Award::STATUS_COMPLETED])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('award_code', 'LIKE', "%{$query}%");
                })
                ->limit(5)
                ->get();

            // 3. Search Nominees (Contestants)
            $nominees = AwardNominee::whereHas('award', function ($q) {
                    $q->whereIn('status', [Award::STATUS_PUBLISHED, Award::STATUS_COMPLETED]);
                })
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('nominee_code', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->with(['award', 'category'])
                ->limit(10)
                ->get();

            // 4. Search Award Categories
            $categories = AwardCategory::whereHas('award', function ($q) {
                    $q->whereIn('status', [Award::STATUS_PUBLISHED, Award::STATUS_COMPLETED]);
                })
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->with(['award'])
                ->limit(5)
                ->get();

            // 5. Search Organizers
            $organizers = Organizer::where(function ($q) use ($query) {
                    $q->where('organization_name', 'LIKE', "%{$query}%")
                      ->orWhere('bio', 'LIKE', "%{$query}%");
                })
                ->limit(5)
                ->get();

            // Format results
            $data = [
                'events' => $events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'name' => $event->title,
                        'slug' => $event->slug,
                        'image' => $event->banner_image,
                        'type' => 'event',
                        'venue' => $event->venue_name,
                        'date' => $event->start_time ? $event->start_time->format('M d, Y') : null,
                    ];
                }),
                'awards' => $awards->map(function ($award) {
                    return [
                        'id' => $award->id,
                        'name' => $award->title,
                        'slug' => $award->slug,
                        'image' => $award->banner_image,
                        'type' => 'award',
                        'code' => $award->award_code,
                        'date' => $award->ceremony_date ? $award->ceremony_date->format('M d, Y') : null,
                    ];
                }),
                'contestants' => $nominees->map(function ($nominee) {
                    return [
                        'id' => $nominee->id,
                        'name' => $nominee->name,
                        'code' => $nominee->nominee_code,
                        'image' => $nominee->image,
                        'type' => 'contestant',
                        'award' => $nominee->award ? $nominee->award->title : null,
                        'award_slug' => $nominee->award ? $nominee->award->slug : null,
                        'category' => $nominee->category ? $nominee->category->name : null,
                    ];
                }),
                'categories' => $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'type' => 'category',
                        'award' => $category->award ? $category->award->title : null,
                        'award_slug' => $category->award ? $category->award->slug : null,
                    ];
                }),
                'organizers' => $organizers->map(function ($organizer) {
                    return [
                        'id' => $organizer->id,
                        'name' => $organizer->organization_name,
                        'image' => $organizer->profile_image,
                        'type' => 'organizer',
                        'bio' => $organizer->bio,
                    ];
                }),
                'count' => $events->count() + $awards->count() + $nominees->count() + $categories->count() + $organizers->count()
            ];

            return ResponseHelper::success($response, 'Global search results', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to perform global search', 500, $e->getMessage());
        }
    }
}
