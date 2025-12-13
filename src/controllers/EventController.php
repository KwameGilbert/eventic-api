<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\EventImage;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * EventController
 * Handles event-related operations using Eloquent ORM
 */
class EventController
{
    /**
     * Get all events (with optional filtering)
     * GET /v1/events
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = Event::with(['ticketTypes', 'eventType', 'organizer.user', 'images']);

            // Filter by status (default to published for public list)
            if (isset($queryParams['status'])) {
                $query->where('status', $queryParams['status']);
            } else {
                // Default to published events for public endpoint
                $query->where('status', Event::STATUS_PUBLISHED);
            }

            // Filter by event type
            if (isset($queryParams['event_type_id'])) {
                $query->where('event_type_id', $queryParams['event_type_id']);
            }

            // Filter by organizer
            if (isset($queryParams['organizer_id'])) {
                $query->where('organizer_id', $queryParams['organizer_id']);
            }

            // Filter by category slug
            if (isset($queryParams['category'])) {
                $query->whereHas('eventType', function ($q) use ($queryParams) {
                    $q->where('slug', $queryParams['category']);
                });
            }

            // Filter upcoming only
            if (isset($queryParams['upcoming']) && $queryParams['upcoming'] === 'true') {
                $query->where('start_time', '>', \Illuminate\Support\Carbon::now());
            }

            // Search by title, description, or venue
            if (isset($queryParams['search']) && !empty($queryParams['search'])) {
                $search = $queryParams['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('venue_name', 'LIKE', "%{$search}%");
                });
            }

            // Location filter - search across address, city, region, and country
            if (isset($queryParams['location']) && !empty($queryParams['location'])) {
                $location = $queryParams['location'];
                $query->where(function ($q) use ($location) {
                    $q->where('address', 'LIKE', "%{$location}%")
                        ->orWhere('city', 'LIKE', "%{$location}%")
                        ->orWhere('region', 'LIKE', "%{$location}%")
                        ->orWhere('country', 'LIKE', "%{$location}%");
                });
            }

            // Pagination
            $page = (int) ($queryParams['page'] ?? 1);
            $perPage = (int) ($queryParams['per_page'] ?? 20);
            $offset = ($page - 1) * $perPage;

            $totalCount = $query->count();
            $events = $query->orderBy('start_time', 'asc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            // Format events for frontend compatibility
            $formattedEvents = $events->map(function ($event) {
                return $event->getFullDetails();
            });

            return ResponseHelper::success($response, 'Events fetched successfully', [
                'events' => $formattedEvents->toArray(),
                'count' => $events->count(),
                'total' => $totalCount,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalCount / $perPage),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch events', 500, $e->getMessage());
        }
    }

    /**
     * Get featured events for homepage carousel
     * GET /v1/events/featured
     */
    public function featured(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $limit = (int) ($queryParams['limit'] ?? 5);

            // First try to get featured events
            $events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
                ->where('status', Event::STATUS_PUBLISHED)
                ->where('is_featured', true)
                ->where('start_time', '>', \Illuminate\Support\Carbon::now())
                ->orderBy('start_time', 'asc')
                ->limit($limit)
                ->get();

            // If no featured events, fallback to upcoming events
            if ($events->isEmpty()) {
                $events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
                    ->where('status', Event::STATUS_PUBLISHED)
                    ->where('start_time', '>', \Illuminate\Support\Carbon::now())
                    ->orderBy('start_time', 'asc')
                    ->limit($limit)
                    ->get();
            }

            // Format for frontend carousel
            $formattedEvents = $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'eventSlug' => $event->slug,
                    'venue' => $event->venue_name . ($event->address ? ', ' . $event->address : ''),
                    'date' => $event->start_time ? $event->start_time->format('D d M Y, g:i A') : null,
                    'image' => $event->banner_image,
                    'category' => $event->eventType ? $event->eventType->name : null,
                ];
            });

            return ResponseHelper::success($response, 'Featured events fetched successfully', $formattedEvents->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch featured events', 500, $e->getMessage());
        }
    }

    /**
     * Get single event by ID or slug
     * GET /v1/events/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $identifier = $args['id'];

            // Try to find by ID first, then by slug
            if (is_numeric($identifier)) {
                $event = Event::with(['organizer.user', 'ticketTypes', 'images', 'eventType'])->find($identifier);
            } else {
                $event = Event::with(['organizer.user', 'ticketTypes', 'images', 'eventType'])
                    ->where('slug', $identifier)
                    ->first();
            }

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            return ResponseHelper::success($response, 'Event fetched successfully', $event->getFullDetails());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event', 500, $e->getMessage());
        }
    }

    /**
     * Increment event views
     * POST /v1/events/{id}/view
     */
    public function incrementViews(Request $request, Response $response, array $args): Response
    {
        try {
            $identifier = $args['id'];

            // Try to find by ID first, then by slug
            if (is_numeric($identifier)) {
                $event = Event::find($identifier);
            } else {
                $event = Event::where('slug', $identifier)->first();
            }

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Increment views
            $event->increment('views');

            return ResponseHelper::success($response, 'View recorded successfully', [
                'views' => $event->views
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to record view', 500, $e->getMessage());
        }
    }

    /**
     * Create new event
     * POST /v1/events
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
                return ResponseHelper::error($response, 'Only organizers can create events', 403);
            }

            // Set organizer_id from authenticated user's organizer profile
            if ($organizer) {
                $data['organizer_id'] = $organizer->id;
            }

            // Validate required fields
            $requiredFields = ['title', 'start_time', 'end_time'];
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
                $data['status'] = Event::STATUS_DRAFT;
            }

            // Validate status value
            $validStatuses = [Event::STATUS_DRAFT, Event::STATUS_PENDING, Event::STATUS_PUBLISHED, Event::STATUS_CANCELLED, Event::STATUS_COMPLETED];
            if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
                return ResponseHelper::error($response, "Invalid status value. Allowed values: draft, pending, published, cancelled, completed", 400);
            }

            // Set default event_format if not provided
            if (!isset($data['event_format'])) {
                $data['event_format'] = 'ticketing';
            }

            // Validate event_format value
            $validFormats = ['ticketing', 'awards'];
            if (isset($data['event_format']) && !in_array($data['event_format'], $validFormats)) {
                return ResponseHelper::error($response, "Invalid event_format value. Allowed values: ticketing, awards", 400);
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

            // Validate tags - handle JSON string or array
            if (isset($data['tags'])) {
                if (is_string($data['tags'])) {
                    $data['tags'] = json_decode($data['tags'], true) ?? [];
                }
                if (!is_array($data['tags'])) {
                    return ResponseHelper::error($response, 'Tags must be an array', 400);
                }
            }

            // Handle banner image upload
            if (isset($uploadedFiles['banner_image'])) {
                $bannerImage = $uploadedFiles['banner_image'];

                if ($bannerImage->getError() === UPLOAD_ERR_OK) {
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $mimeType = $bannerImage->getClientMediaType();

                    if (!in_array($mimeType, $allowedTypes)) {
                        return ResponseHelper::error($response, 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP', 400);
                    }

                    // Validate file size (max 5MB)
                    if ($bannerImage->getSize() > 5 * 1024 * 1024) {
                        return ResponseHelper::error($response, 'Image size must be less than 5MB', 400);
                    }

                    // Create upload directory if it doesn't exist
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/events';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Generate unique filename
                    $extension = pathinfo($bannerImage->getClientFilename(), PATHINFO_EXTENSION);
                    $filename = 'event_' . uniqid() . '_' . time() . '.' . $extension;
                    $filepath = $uploadDir . '/' . $filename;

                    // Move uploaded file
                    $bannerImage->moveTo($filepath);

                    // Store relative path in database
                    $data['banner_image'] = '/uploads/events/' . $filename;
                }
            }

            $event = Event::create($data);

            // Handle event photos upload (multiple)
            if (isset($uploadedFiles['event_photos']) && is_array($uploadedFiles['event_photos'])) {
                $uploadDir = dirname(__DIR__, 2) . '/public/uploads/events';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                foreach ($uploadedFiles['event_photos'] as $photo) {
                    if ($photo->getError() === UPLOAD_ERR_OK) {
                        $mimeType = $photo->getClientMediaType();

                        // Validate file type
                        if (!in_array($mimeType, $allowedTypes)) {
                            continue; // Skip invalid files
                        }

                        // Validate file size (max 5MB)
                        if ($photo->getSize() > 5 * 1024 * 1024) {
                            continue; // Skip files that are too large
                        }

                        // Generate unique filename
                        $extension = pathinfo($photo->getClientFilename(), PATHINFO_EXTENSION);
                        $filename = 'event_photo_' . uniqid() . '_' . time() . '.' . $extension;
                        $filepath = $uploadDir . '/' . $filename;

                        // Move uploaded file
                        $photo->moveTo($filepath);

                        // Create EventImage record
                        EventImage::create([
                            'event_id' => $event->id,
                            'image_path' => '/uploads/events/' . $filename,
                        ]);
                    }
                }
            }

            // Handle tickets creation
            if (isset($data['tickets'])) {
                $tickets = is_string($data['tickets']) ? json_decode($data['tickets'], true) : $data['tickets'];
                if (is_array($tickets)) {
                    foreach ($tickets as $index => $ticketData) {
                        if (!empty($ticketData['name']) && isset($ticketData['quantity'])) {
                            $ticketImagePath = null;

                            // Handle ticket image upload for this specific ticket
                            // Check if image is uploaded as ticket_image_{index}
                            if (isset($uploadedFiles["ticket_image_{$index}"])) {
                                $ticketImage = $uploadedFiles["ticket_image_{$index}"];

                                if ($ticketImage->getError() === UPLOAD_ERR_OK) {
                                    // Validate file type
                                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    $mimeType = $ticketImage->getClientMediaType();

                                    if (in_array($mimeType, $allowedTypes)) {
                                        // Validate file size (max 2MB for ticket images)
                                        if ($ticketImage->getSize() <= 2 * 1024 * 1024) {
                                            // Create upload directory for tickets if it doesn't exist
                                            $ticketUploadDir = dirname(__DIR__, 2) . '/public/uploads/tickets';
                                            if (!is_dir($ticketUploadDir)) {
                                                mkdir($ticketUploadDir, 0755, true);
                                            }

                                            // Generate unique filename
                                            $extension = pathinfo($ticketImage->getClientFilename(), PATHINFO_EXTENSION);
                                            $filename = 'ticket_' . uniqid() . '_' . time() . '.' . $extension;
                                            $filepath = $ticketUploadDir . '/' . $filename;

                                            // Move uploaded file
                                            $ticketImage->moveTo($filepath);

                                            // Store relative path
                                            $ticketImagePath = rtrim($_ENV['APP_URL'] ?? 'http://app.eventic.com', '/') . '/uploads/tickets/' . $filename;
                                        }
                                    }
                                }
                            }

                            TicketType::create([
                                'event_id' => $event->id,
                                'organizer_id' => $event->organizer_id,
                                'name' => $ticketData['name'],
                                'price' => $ticketData['price'] ?? 0,
                                'sale_price' => $ticketData['promoPrice'] ?? 0,
                                'quantity' => $ticketData['quantity'],
                                'remaining' => $ticketData['quantity'],
                                'description' => $ticketData['description'] ?? null,
                                'max_per_user' => $ticketData['maxPerOrder'] ?? 10,
                                'sale_start' => !empty($ticketData['saleStartDate']) ? $ticketData['saleStartDate'] : null,
                                'sale_end' => !empty($ticketData['saleEndDate']) ? $ticketData['saleEndDate'] : null,
                                'ticket_image' => $ticketImagePath,
                                'status' => 'active'
                            ]);
                        }
                    }
                }
            }

            return ResponseHelper::success($response, 'Event created successfully', $event->getFullDetails(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create event', 500, $e->getMessage());
        }
    }

    /**
     * Update event
     * PUT /v1/events/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $event = Event::find($id);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Authorization: Check if user is admin or the event organizer
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || $organizer->id !== $event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this event', 403);
                }
            }

            // Update slug if title changes and slug isn't manually provided
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            }

            // Validate status value if provided
            if (isset($data['status'])) {
                $validStatuses = [Event::STATUS_DRAFT, Event::STATUS_PENDING, Event::STATUS_PUBLISHED, Event::STATUS_CANCELLED, Event::STATUS_COMPLETED];
                if (!in_array($data['status'], $validStatuses)) {
                    return ResponseHelper::error($response, "Invalid status value. Allowed values: draft, pending, published, cancelled, completed", 400);
                }
            }

            // Validate event_format value if provided
            if (isset($data['event_format'])) {
                $validFormats = ['ticketing', 'awards'];
                if (!in_array($data['event_format'], $validFormats)) {
                    return ResponseHelper::error($response, "Invalid event_format value. Allowed values: ticketing, awards", 400);
                }
            }

            // Validate tags - handle JSON string or array
            if (isset($data['tags'])) {
                if (is_string($data['tags'])) {
                    $data['tags'] = json_decode($data['tags'], true) ?? [];
                }
                if (!is_array($data['tags'])) {
                    return ResponseHelper::error($response, 'Tags must be an array', 400);
                }
            }

            // Handle banner image upload
            if (isset($uploadedFiles['banner_image'])) {
                $bannerImage = $uploadedFiles['banner_image'];

                if ($bannerImage->getError() === UPLOAD_ERR_OK) {
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $mimeType = $bannerImage->getClientMediaType();

                    if (!in_array($mimeType, $allowedTypes)) {
                        return ResponseHelper::error($response, 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP', 400);
                    }

                    // Validate file size (max 5MB)
                    if ($bannerImage->getSize() > 5 * 1024 * 1024) {
                        return ResponseHelper::error($response, 'Image size must be less than 5MB', 400);
                    }

                    // Create upload directory if it doesn't exist
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/events';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Delete old image if exists
                    if ($event->banner_image && file_exists(dirname(__DIR__, 2) . '/public' . $event->banner_image)) {
                        unlink(dirname(__DIR__, 2) . '/public' . $event->banner_image);
                    }

                    // Generate unique filename
                    $extension = pathinfo($bannerImage->getClientFilename(), PATHINFO_EXTENSION);
                    $filename = 'event_' . uniqid() . '_' . time() . '.' . $extension;
                    $filepath = $uploadDir . '/' . $filename;

                    // Move uploaded file
                    $bannerImage->moveTo($filepath);

                    // Store relative path in database
                    $data['banner_image'] = '/uploads/events/' . $filename;
                }
            }

            $event->update($data);

            // Handle event photos upload (multiple) - these are added to existing photos
            if (isset($uploadedFiles['event_photos']) && is_array($uploadedFiles['event_photos'])) {
                $uploadDir = dirname(__DIR__, 2) . '/public/uploads/events';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                foreach ($uploadedFiles['event_photos'] as $photo) {
                    if ($photo->getError() === UPLOAD_ERR_OK) {
                        $mimeType = $photo->getClientMediaType();

                        // Validate file type
                        if (!in_array($mimeType, $allowedTypes)) {
                            continue; // Skip invalid files
                        }

                        // Validate file size (max 5MB)
                        if ($photo->getSize() > 5 * 1024 * 1024) {
                            continue; // Skip files that are too large
                        }

                        // Generate unique filename
                        $extension = pathinfo($photo->getClientFilename(), PATHINFO_EXTENSION);
                        $filename = 'event_photo_' . uniqid() . '_' . time() . '.' . $extension;
                        $filepath = $uploadDir . '/' . $filename;

                        // Move uploaded file
                        $photo->moveTo($filepath);

                        // Create EventImage record
                        EventImage::create([
                            'event_id' => $event->id,
                            'image_path' => '/uploads/events/' . $filename,
                        ]);
                    }
                }
            }

            // Handle deleted tickets
            if (isset($data['deleted_tickets'])) {
                $deletedTickets = is_string($data['deleted_tickets']) ? json_decode($data['deleted_tickets'], true) : $data['deleted_tickets'];
                if (is_array($deletedTickets) && !empty($deletedTickets)) {
                    TicketType::whereIn('id', $deletedTickets)
                        ->where('event_id', $event->id)
                        ->delete();
                }
            }

            // Handle tickets (create/update)
            if (isset($data['tickets'])) {
                $tickets = is_string($data['tickets']) ? json_decode($data['tickets'], true) : $data['tickets'];
                if (is_array($tickets)) {
                    foreach ($tickets as $index => $ticketData) {
                        if (!empty($ticketData['name']) && isset($ticketData['quantity'])) {
                            if (isset($ticketData['id'])) {
                                // Update existing ticket
                                $ticket = TicketType::where('id', $ticketData['id'])
                                    ->where('event_id', $event->id)
                                    ->first();
                                if ($ticket) {
                                    $oldQuantity = $ticket->quantity;
                                    $sold = $oldQuantity - $ticket->remaining;
                                    $newRemaining = $ticketData['quantity'] - $sold;

                                    // Prevent negative remaining if sold > new quantity (edge case)
                                    if ($newRemaining < 0)
                                        $newRemaining = 0;

                                    // Handle ticket image upload for update
                                    $ticketImagePath = $ticket->ticket_image; // Keep existing image by default
                                    
                                    if (isset($uploadedFiles["ticket_image_{$index}"])) {
                                        $ticketImage = $uploadedFiles["ticket_image_{$index}"];

                                        if ($ticketImage->getError() === UPLOAD_ERR_OK) {
                                            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                            $mimeType = $ticketImage->getClientMediaType();

                                            if (in_array($mimeType, $allowedTypes)) {
                                                if ($ticketImage->getSize() <= 2 * 1024 * 1024) {
                                                    $ticketUploadDir = dirname(__DIR__, 2) . '/public/uploads/tickets';
                                                    if (!is_dir($ticketUploadDir)) {
                                                        mkdir($ticketUploadDir, 0755, true);
                                                    }

                                                    // Delete old ticket image if exists
                                                    if ($ticket->ticket_image && file_exists(dirname(__DIR__, 2) . '/public' . $ticket->ticket_image)) {
                                                        unlink(dirname(__DIR__, 2) . '/public' . $ticket->ticket_image);
                                                    }

                                                    $extension = pathinfo($ticketImage->getClientFilename(), PATHINFO_EXTENSION);
                                                    $filename = 'ticket_' . uniqid() . '_' . time() . '.' . $extension;
                                                    $filepath = $ticketUploadDir . '/' . $filename;

                                                    $ticketImage->moveTo($filepath);
                                                    $ticketImagePath = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/uploads/tickets/' . $filename;
                                                }
                                            }
                                        }
                                    }

                                    $ticket->update([
                                        'name' => $ticketData['name'],
                                        'price' => $ticketData['price'] ?? 0,
                                        'sale_price' => $ticketData['promoPrice'] ?? 0,
                                        'quantity' => $ticketData['quantity'],
                                        'remaining' => $newRemaining,
                                        'description' => $ticketData['description'] ?? null,
                                        'max_per_user' => $ticketData['maxPerOrder'] ?? 10,
                                        'sale_start' => !empty($ticketData['saleStartDate']) ? $ticketData['saleStartDate'] : null,
                                        'sale_end' => !empty($ticketData['saleEndDate']) ? $ticketData['saleEndDate'] : null,
                                        'ticket_image' => $ticketImagePath,
                                    ]);
                                }
                            } else {
                                // Create new ticket
                                $ticketImagePath = null;

                                if (isset($uploadedFiles["ticket_image_{$index}"])) {
                                    $ticketImage = $uploadedFiles["ticket_image_{$index}"];

                                    if ($ticketImage->getError() === UPLOAD_ERR_OK) {
                                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                        $mimeType = $ticketImage->getClientMediaType();

                                        if (in_array($mimeType, $allowedTypes)) {
                                            if ($ticketImage->getSize() <= 2 * 1024 * 1024) {
                                                $ticketUploadDir = dirname(__DIR__, 2) . '/public/uploads/tickets';
                                                if (!is_dir($ticketUploadDir)) {
                                                    mkdir($ticketUploadDir, 0755, true);
                                                }

                                                $extension = pathinfo($ticketImage->getClientFilename(), PATHINFO_EXTENSION);
                                                $filename = 'ticket_' . uniqid() . '_' . time() . '.' . $extension;
                                                $filepath = $ticketUploadDir . '/' . $filename;

                                                $ticketImage->moveTo($filepath);
                                                $ticketImagePath = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/uploads/tickets/' . $filename;
                                            }
                                        }
                                    }
                                }

                                TicketType::create([
                                    'event_id' => $event->id,
                                    'organizer_id' => $event->organizer_id,
                                    'name' => $ticketData['name'],
                                    'price' => $ticketData['price'] ?? 0,
                                    'sale_price' => $ticketData['promoPrice'] ?? 0,
                                    'quantity' => $ticketData['quantity'],
                                    'remaining' => $ticketData['quantity'],
                                    'description' => $ticketData['description'] ?? null,
                                    'max_per_user' => $ticketData['maxPerOrder'] ?? 10,
                                    'sale_start' => !empty($ticketData['saleStartDate']) ? $ticketData['saleStartDate'] : null,
                                    'sale_end' => !empty($ticketData['saleEndDate']) ? $ticketData['saleEndDate'] : null,
                                    'ticket_image' => $ticketImagePath,
                                    'status' => 'active'
                                ]);
                            }
                        }
                    }
                }
            }

            return ResponseHelper::success($response, 'Event updated successfully', $event->getFullDetails());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update event', 500, $e->getMessage());
        }
    }

    /**
     * Delete event
     * DELETE /v1/events/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $event = Event::find($id);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Authorization: Check if user is admin or the event organizer
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || $organizer->id !== $event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this event', 403);
                }
            }

            // Validation: Check if event has tickets sold
            if (Ticket::where('event_id', $id)->exists()) {
                return ResponseHelper::error($response, 'Cannot delete event with existing tickets', 400);
            }

            // Validation: Check if event has any order items (even if no tickets generated yet)
            if (OrderItem::where('event_id', $id)->exists()) {
                return ResponseHelper::error($response, 'Cannot delete event with associated orders', 400);
            }

            $event->delete();

            return ResponseHelper::success($response, 'Event deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete event', 500, $e->getMessage());
        }
    }

    /**
     * Search events
     * GET /v1/events/search
     */
    public function search(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['query'] ?? '';

            if (empty($query)) {
                return ResponseHelper::error($response, 'Search query is required', 400);
            }

            $events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
                ->where('status', Event::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%")
                        ->orWhere('venue_name', 'LIKE', "%{$query}%");
                })
                ->get();

            $formattedEvents = $events->map(function ($event) {
                return $event->getFullDetails();
            });

            return ResponseHelper::success($response, 'Events found', [
                'events' => $formattedEvents->toArray(),
                'count' => $events->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to search events', 500, $e->getMessage());
        }
    }

    /**
     * Get all event types (categories)
     * GET /v1/events/types
     */
    public function getEventTypes(Request $request, Response $response, array $args): Response
    {
        try {
            $eventTypes = EventType::all();

            return ResponseHelper::success($response, 'Event types fetched successfully', [
                'event_types' => $eventTypes->toArray(),
                'count' => $eventTypes->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event types', 500, $e->getMessage());
        }
    }
}
