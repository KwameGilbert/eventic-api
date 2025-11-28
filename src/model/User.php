<?php

declare(strict_types=1);

require_once MODEL . 'BaseModel.php';

/**
 * User Model
 * Represents a user in the system
 */
class User extends BaseModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'users';

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
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get user by email
     * @param string $email
     * @return User|null
     */
    public static function findByEmail(string $email): ?User
    {
        return static::where('email', $email)->first();
    }

    /**
     * Get active users
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveUsers()
    {
        return static::where('status', 'active')->get();
    }

    /**
     * Check if email exists
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = static::where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}
