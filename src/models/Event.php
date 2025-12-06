<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Event Model
 * 
 * Represents an event created by an organizer.
 *
 * @property int $id
 * @property int $organizer_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int|null $event_type_id
 * @property string|null $venue_name
 * @property string|null $address
 * @property string|null $map_url
 * @property string|null $banner_image
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property string $status
 * @property string|null $audience
 * @property string|null $language
 * @property array|null $tags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Event extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'events';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Event Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'organizer_id',
        'title',
        'slug',
        'description',
        'event_type_id',
        'venue_name',
        'address',
        'map_url',
        'banner_image',
        'start_time',
        'end_time',
        'status',
        'is_featured',
        'audience',
        'language',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'organizer_id' => 'integer',
        'event_type_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the organizer that owns the event.
     */
    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    /**
     * Get the event type.
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /**
     * Get the event images.
     */
    public function images()
    {
        return $this->hasMany(EventImage::class, 'event_id');
    }

    /**
     * Get the ticket types for this event.
     */
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class, 'event_id');
    }

    /**
     * Get the reviews for this event.
     */
    public function reviews()
    {
        return $this->hasMany(EventReview::class, 'event_id');
    }

    /**
     * Get the tickets for this event.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_id');
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Scope to get published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to get featured events.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', \Illuminate\Support\Carbon::now());
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if event is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Get formatted price from lowest ticket type
     */
    public function getLowestPrice(): ?float
    {
        $lowestTicket = $this->ticketTypes()
            ->where('status', TicketType::STATUS_ACTIVE)
            ->orderBy('price', 'asc')
            ->first();
        
        return $lowestTicket ? (float)$lowestTicket->price : null;
    }

    /**
     * Get event details formatted for frontend (matching mock data structure)
     */
    public function getFullDetails(): array
    {
        // Load relationships
        $this->load(['organizer.user', 'ticketTypes', 'images', 'eventType']);
        
        $details = [
            'id' => $this->id,
            'title' => $this->title,
            'eventSlug' => $this->slug,
            'description' => $this->description,
            'venue' => $this->venue_name,
            'location' => $this->address,
            'country' => '', // Could be extracted from address if stored
            'date' => $this->start_time ? $this->start_time->format('Y-m-d') : null,
            'time' => $this->start_time ? $this->start_time->format('g:i A') : null,
            'price' => $this->getLowestPrice() ? 'GH₵' . number_format($this->getLowestPrice(), 2) : 'Free',
            'numericPrice' => $this->getLowestPrice() ?? 0,
            'category' => $this->eventType ? $this->eventType->name : null,
            'slug' => $this->eventType ? $this->eventType->slug : null,
            'audience' => $this->audience,
            'isOnline' => false,
            'image' => $this->banner_image,
            'mapUrl' => $this->map_url,
            'tags' => $this->tags ?? [],
            'ticketTypes' => $this->ticketTypes->map(function ($tt) {
                return [
                    'id' => $tt->id,
                    'name' => $tt->name,
                    'price' => (float)$tt->price,
                    'originalPrice' => null, // Could add a original_price field if needed
                    'available' => $tt->isAvailable(),
                    'availableQuantity' => $tt->remaining,
                    'maxPerAttendee' => $tt->max_per_user,
                    'description' => null, // Could add description field to ticket_types
                ];
            })->toArray(),
            'organizer' => null,
            'contact' => null,
            'socialMedia' => null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
        ];

        // Add organizer info if available
        if ($this->organizer) {
            $details['organizer'] = [
                'id' => $this->organizer->id,
                'name' => $this->organizer->organization_name ?? ($this->organizer->user->name ?? 'Organizer'),
                'avatar' => $this->organizer->profile_image ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->organizer->organization_name ?? 'Org'),
                'bio' => $this->organizer->bio,
                'verified' => true,
                'followers' => 0, // Could add followers count
                'eventsOrganized' => $this->organizer->events()->count(),
                'rating' => 4.5, // Could calculate from reviews
            ];
        }

        // Add images if available
        if ($this->images->count() > 0) {
            $details['images'] = $this->images->pluck('image_path')->toArray();
        }

        return $details;
    }

    /**
     * Get summary for list views (less data than full details)
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'eventSlug' => $this->slug,
            'venue' => $this->venue_name,
            'location' => $this->address,
            'date' => $this->start_time ? $this->start_time->format('Y-m-d') : null,
            'time' => $this->start_time ? $this->start_time->format('g:i A') : null,
            'price' => $this->getLowestPrice() ? 'GH₵' . number_format($this->getLowestPrice(), 2) : 'Free',
            'numericPrice' => $this->getLowestPrice() ?? 0,
            'category' => $this->eventType ? $this->eventType->name : null,
            'image' => $this->banner_image,
            'status' => $this->status,
        ];
    }
}
