<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'product_id',
        'order_reference',
        'quantity',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'shipping_address',
        'city',
        'shipping_method_name',
        'shipping_cost',
        'pickup_agent_details',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'variation_label',
        'mpesa_transaction_id',
        'payment_phone_number'
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
