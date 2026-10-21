<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrderRequest extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_city',
        'request_description',
        'preferred_fabric',
        'measurements',
        'notes',
        'status',
        'assigned_to',
        'converted_at',
        'converted_order_id',
    ];

    protected function casts(): array
    {
        return [
            'measurements' => 'array',
            'converted_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'new' => 'New',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'converted' => 'Converted to Order',
        'closed' => 'Closed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function markConverted(Order $order): void
    {
        $this->forceFill([
            'status' => 'converted',
            'converted_at' => now(),
            'converted_order_id' => $order->id,
        ])->save();
    }
}
