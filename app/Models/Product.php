<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'images',
        'stock',
        'variations',

    ];

    protected $casts = [
        'images' => 'array',
        'variations' => 'array',
    ];


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


}
