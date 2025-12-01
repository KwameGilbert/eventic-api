<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order Model
 * 
 * Represents a financial transaction for ticket purchases.
 *
 * @property int $id
 * @property int $user_id
 * @property float $total_amount
 * @property string $status
 * @property string|null $payment_reference
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'pos_user_id',
        'total_amount',
        'status',
        'payment_reference',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'pos_user_id' => 'integer',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posUser()
    {
        return $this->belongsTo(User::class, 'pos_user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'order_id');
    }
}
