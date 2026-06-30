<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\FilamentAccessManagement\Concerns\FilamentUserHelpers;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, FilamentUserHelpers, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // IMPORTANT: Added this so you can save 'admin' or 'customer'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Relationship: A user can have many orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Ensure this returns true for your admin user
        return str_ends_with($this->email, '@gmail.com');
    }
}
