<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'employee_id',
        'nik',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the employee associated with this user.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role->slug === $role;
    }

    /**
     * Check if user is HR.
     */
    public function isHR(): bool
    {
        return $this->hasRole(Role::ROLE_HR);
    }

    /**
     * Check if user is Director.
     */
    public function isDirector(): bool
    {
        return $this->hasRole(Role::ROLE_DIRECTOR);
    }

    /**
     * Check if user can modify employees.
     */
    public function canModifyEmployees(): bool
    {
        return $this->isHR();
    }

    /**
     * Check if user can view employees.
     */
    public function canViewEmployees(): bool
    {
        return $this->isHR() || $this->isDirector();
    }
}
