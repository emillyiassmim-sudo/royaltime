<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'status',
        'total',
        'shipping',
        'items',
        'payment_method',
        'customer_snapshot',
        'checkout_token',
    ];

    protected $casts = [
        'items' => 'array',
        'customer_snapshot' => 'encrypted:array',
        'total' => 'decimal:2',
        'shipping' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
