<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public const ROLE_DIRECTOR = 'director';
    public const ROLE_HR = 'hr';
    public const ROLE_ADMIN_DEPARTMENT = 'admin_department';
    public const ROLE_IT = 'it';
    public const ROLE_EMPLOYEE = 'employee';

    /**
     * Get the users for this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
