<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
 use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
 // En el modelo User.php
protected $with = ['role'];
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'dni',
        'address',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // 🔥 auto hash
            'is_active' => 'boolean',
        ];
    }

    /**
     * 🔗 RELACIÓN: User pertenece a un Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 🔠 Iniciales del usuario
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * 🔐 Verificar si es admin
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    /**
     * 👨‍💼 Verificar si es empleado
     */
    public function isEmployee(): bool
    {
        return $this->role?->name === 'employee';
    }

    /**
     * 👤 Verificar si es cliente
     */
    public function isClient(): bool
    {
        return $this->role?->name === 'client';
    }
}