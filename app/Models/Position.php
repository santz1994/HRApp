<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the employees with this position.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}