<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'has_allergen_warning' => 'boolean',
        'show_cart_ready_card' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(MenuItem::class, 'product_id');
    }
}

