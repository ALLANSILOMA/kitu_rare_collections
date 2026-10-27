<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{

    use Auditable;

    public function auditLabel(): string
    {
        return (string) ($this->name ?: 'Product #' . $this->getKey());
    }

    public function auditExclude(): array
    {
        return ['images', 'videos', 'description'];
    }
    protected $fillable = [
        'name',
        'description',
        'price',
        'buying_price',
        'supplier',
        'images',
        'videos',
        'stock',
        'variations',

    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
        'variations' => 'array',
        'price' => 'decimal:2',
        'buying_price' => 'decimal:2',
    ];


    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }


}
