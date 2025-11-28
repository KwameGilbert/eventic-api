<?php

declare(strict_types=1);

require_once MODEL . 'BaseModel.php';

/**
 * Product Model
 * Represents a product in the system
 */
class Product extends BaseModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'products';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'sku',
        'category',
        'status',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get products by category
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)->get();
    }

    /**
     * Get products in stock
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getInStock()
    {
        return static::where('quantity', '>', 0)
                     ->where('status', 'active')
                     ->get();
    }

    /**
     * Search products
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function search(string $search)
    {
        return static::where('name', 'LIKE', "%{$search}%")
                     ->orWhere('description', 'LIKE', "%{$search}%")
                     ->orWhere('sku', 'LIKE', "%{$search}%")
                     ->get();
    }

    /**
     * Get low stock products
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLowStock(int $threshold = 10)
    {
        return static::where('quantity', '<=', $threshold)
                     ->where('quantity', '>', 0)
                     ->get();
    }
}
