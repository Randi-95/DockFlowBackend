<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sku_code',
        'name',
        'image_url',
        'stock_qty',
        'unit',
        'price',
        'rack_location',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
