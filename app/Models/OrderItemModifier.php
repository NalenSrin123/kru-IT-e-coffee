<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'modifier_item_id',
        'modifier_name',
        'extra_price',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function modifierItem(): BelongsTo
    {
        return $this->belongsTo(ModifierItem::class);
    }
}
