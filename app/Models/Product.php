<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'category_label',
        'description',
        'price',
        'promo_price',
        'stock',
        'reference',
        'color',
        'strap',
        'image',
        'images',
        'badge',
        'installments',
        'technical',
        'sections',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'stock' => 'integer',
            'images' => 'array',
            'technical' => 'array',
            'sections' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
