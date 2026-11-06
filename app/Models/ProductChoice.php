<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductChoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'extra_price' => 'integer',
        'is_default' => 'boolean',
    ];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }
}

