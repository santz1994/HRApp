<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the employees in this department.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    /**
     * Get employees whose initial department is this one.
     */
    public function initialEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'initial_department_id');
    }

    /**
     * Get employees whose current department is this one.
     */
    public function currentEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'current_department_id');
    }
}