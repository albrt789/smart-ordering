<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'daily_stock' => 'integer',
        'track_stock' => 'boolean',
        'badges' => 'array',
        'is_signature' => 'boolean',
        'is_available' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class)->with('choices');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if product is currently out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->track_stock && $this->stock <= 0;
    }

    /**
     * Check if product is available for customer ordering
     */
    public function isAvailableToOrder(): bool
    {
        if (!$this->is_available) {
            return false;
        }

        if ($this->track_stock && $this->stock <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if requested quantity is available
     */
    public function hasEnoughStock(int $quantity): bool
    {
        if (!$this->track_stock) {
            return true;
        }

        return $this->stock >= $quantity;
    }
}
