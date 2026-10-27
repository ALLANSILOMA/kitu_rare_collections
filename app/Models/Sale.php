<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'order_reference',
        'customer_name',
        'variation_label',
        'quantity',
        'selling_price',
        'buying_price',
        'shipping_cost',
        'total_revenue',
        'total_cost',
        'profit',
        'payment_method',
        'shipping_method',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'buying_price'  => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'total_cost'    => 'decimal:2',
        'profit'        => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Scopes for date filtering ─────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
