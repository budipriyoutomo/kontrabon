<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
    ];

    /**
     * Cek apakah user memiliki salah satu peran yang diminta.
     *
     * Admin TIDAK otomatis lolos di sini — pengecekan itu ada di
     * Gate::before dan middleware role, supaya method ini tetap jujur
     * menjawab "peran user ini apa".
     */
    public function hasRole(UserRole|string ...$roles): bool
    {
        $current = $this->role?->value;

        foreach ($roles as $role) {
            if ($current === ($role instanceof UserRole ? $role->value : $role)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function roleLabel(): string
    {
        return $this->role?->label() ?? '-';
    }
}
