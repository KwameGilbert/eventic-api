<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Award Model
 * 
 * Represents an awards show/ceremony (e.g., Ghana Music Awards 2025)
 * Completely separate from Events (which handle ticketing)
 *
 * @property int $id
 * @property int $organizer_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $banner_image
 * @property string|null $venue_name
 * @property string|null $address
 * @property string|null $map_url
 * @property \Illuminate\Support\Carbon $ceremony_date
 * @property \Illuminate\Support\Carbon $voting_start
 * @property \Illuminate\Support\Carbon $voting_end
 * @property string $status
 * @property bool $is_featured
 * @property string $country
 * @property string $region
 * @property string $city
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $facebook
 * @property string|null $twitter
 * @property string|null $instagram
 * @property string|null $video_url
 * @property int $views
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Award extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'awards';

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

    // Award Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CLOSED = 'closed';
    const STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'organizer_id',
        'title',
        'slug',
        'description',
        'banner_image',
        'venue_name',
        'address',
        'map_url',
        'ceremony_date',
        'voting_start',
        'voting_end',
        'status',
        'is_featured',
        'country',
        'region',
        'city',
        'phone',
        'website',
        'facebook',
        'twitter',
        'instagram',
        'video_url',
        'views',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'ceremony_date' => 'datetime',
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the organizer that owns the award.
     */
    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    /**
     * Get the award categories for this award show.
     */
    public function categories()
    {
        return $this->hasMany(AwardCategory::class, 'award_id')
                    ->orderBy('display_order');
    }

    /**
     * Get all nominees for this award show.
     */
    public function nominees()
    {
        return $this->hasMany(AwardNominee::class, 'award_id');
    }

    /**
     * Get all votes for this award show.
     */
    public function votes()
    {
        return $this->hasMany(AwardVote::class, 'award_id');
    }

    /**
     * Get the images for this award show.
     */
    public function images()
    {
        return $this->hasMany(AwardImage::class, 'award_id');
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Scope to get published awards.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to get featured awards.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get upcoming awards (ceremony not yet happened).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('ceremony_date', '>', \Illuminate\Support\Carbon::now());
    }

    /**
     * Scope to get awards currently open for voting.
     */
    public function scopeVotingOpen($query)
    {
        $now = \Illuminate\Support\Carbon::now();
        return $query->where('voting_start', '<=', $now)
                     ->where('voting_end', '>=', $now)
                     ->where('status', self::STATUS_PUBLISHED);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if award is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if voting is currently open.
     */
    public function isVotingOpen(): bool
    {
        $now = \Illuminate\Support\Carbon::now();
        return $this->voting_start <= $now && 
               $this->voting_end >= $now &&
               $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if voting has ended.
     */
    public function isVotingClosed(): bool
    {
        return \Illuminate\Support\Carbon::now() > $this->voting_end;
    }

    /**
     * Check if ceremony has passed.
     */
    public function isCeremonyComplete(): bool
    {
        return \Illuminate\Support\Carbon::now() > $this->ceremony_date;
    }

    /**
     * Get total number of votes across all categories.
     */
    public function getTotalVotes(): int
    {
        return $this->votes()
                    ->where('status', 'paid')
                    ->sum('number_of_votes');
    }

    /**
     * Get total revenue from all votes.
     */
    public function getTotalRevenue(): float
    {
        $total = 0;
        
        foreach ($this->categories as $category) {
            $categoryVotes = $category->votes()
                                      ->where('status', 'paid')
                                      ->sum('number_of_votes');
            $total += $categoryVotes * $category->cost_per_vote;
        }
        
        return $total;
    }

    /**
     * Get award details formatted for frontend.
     */
    public function getFullDetails(): array
    {
        // Load relationships
        $this->load(['organizer.user', 'categories.nominees', 'images']);

        $details = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'venue' => $this->venue_name,
            'location' => $this->address,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'ceremony_date' => $this->ceremony_date ? $this->ceremony_date->format('Y-m-d') : null,
            'ceremony_time' => $this->ceremony_date ? $this->ceremony_date->format('g:i A') : null,
            'voting_start' => $this->voting_start ? $this->voting_start->toIso8601String() : null,
            'voting_end' => $this->voting_end ? $this->voting_end->toIso8601String() : null,
            'is_voting_open' => $this->isVotingOpen(),
            'is_voting_closed' => $this->isVotingClosed(),
            'image' => $this->banner_image,
            'mapUrl' => $this->map_url,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'total_votes' => $this->getTotalVotes(),
            'total_revenue' => $this->getTotalRevenue(),
            'views' => $this->views,
            'categories' => $this->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => $category->image,
                    'cost_per_vote' => (float) $category->cost_per_vote,
                    'voting_start' => $category->voting_start,
                    'voting_end' => $category->voting_end,
                    'status' => $category->status,
                    'display_order' => $category->display_order,
                    'is_voting_open' => $category->isVotingOpen(),
                    'nominees' => $category->nominees->map(function ($nominee) {
                        return [
                            'id' => $nominee->id,
                            'name' => $nominee->name,
                            'description' => $nominee->description,
                            'image' => $nominee->image,
                            'display_order' => $nominee->display_order,
                            'total_votes' => $nominee->getTotalVotes(),
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'organizer' => null,
            'contact' => [
                'phone' => $this->phone,
                'website' => $this->website,
            ],
            'socialMedia' => [
                'facebook' => $this->facebook,
                'twitter' => $this->twitter,
                'instagram' => $this->instagram,
            ],
            'videoUrl' => $this->video_url,
        ];

        // Add organizer info if available
        if ($this->organizer) {
            $details['organizer'] = [
                'id' => $this->organizer->id,
                'name' => $this->organizer->organization_name ?? ($this->organizer->user->name ?? 'Organizer'),
                'avatar' => $this->organizer->profile_image ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->organizer->organization_name ?? 'Org'),
                'bio' => $this->organizer->bio,
                'verified' => true,
            ];
        }

        // Add images if available
        if ($this->images->count() > 0) {
            $details['images'] = $this->images->pluck('image_path')->toArray();
        }

        return $details;
    }

    /**
     * Get summary for list views.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'venue' => $this->venue_name,
            'location' => $this->address,
            'ceremony_date' => $this->ceremony_date ? $this->ceremony_date->format('Y-m-d') : null,
            'ceremony_time' => $this->ceremony_date ? $this->ceremony_date->format('g:i A') : null,
            'is_voting_open' => $this->isVotingOpen(),
            'image' => $this->banner_image,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'categories_count' => $this->categories()->count(),
            'total_votes' => $this->getTotalVotes(),
        ];
    }
}
