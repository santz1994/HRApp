<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'no_ktp',
        'nama',
        'department',
        'jabatan',
        'tempat_lahir',
        'tanggal_masuk',
        'tanggal_lahir',
        'jenis_kelamin',
        'dept_on_line',
        'dept_on_line_awal',
        'status_pkwtt',
        'status_keluarga',
        'pendidikan',
        'alamat',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_lahir' => 'date',
    ];

    /**
     * Get the current age of the employee.
     * Calculated field - NOT stored in database.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return $this->tanggal_lahir->diffInYears(Carbon::now());
    }

    /**
     * Get the age when employee joined.
     * Calculated field - NOT stored in database.
     */
    public function getAgeOnJoiningAttribute(): ?int
    {
        if (!$this->tanggal_lahir || !$this->tanggal_masuk) {
            return null;
        }
        return $this->tanggal_lahir->diffInYears($this->tanggal_masuk);
    }

    /**
     * Get the tenure (masa kerja) of the employee in years.
     * Calculated field - NOT stored in database.
     */
    public function getTenureYearsAttribute(): ?float
    {
        if (!$this->tanggal_masuk) {
            return null;
        }
        return $this->tanggal_masuk->diffInDays(Carbon::now()) / 365.25;
    }

    /**
     * Get the tenure in formatted string (e.g., "5 years 3 months").
     * Calculated field - NOT stored in database.
     */
    public function getTenureFormattedAttribute(): ?string
    {
        if (!$this->tanggal_masuk) {
            return null;
        }
        
        $now = Carbon::now();
        $diff = $this->tanggal_masuk->diff($now);
        
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . ($diff->y == 1 ? 'year' : 'years');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m == 1 ? 'month' : 'months');
        }
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' ' . ($diff->d == 1 ? 'day' : 'days');
        }
        
        return implode(', ', $parts) ?: '0 days';
    }

    /**
     * Scope: Filter by department.
     */
    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope: Filter by status PKWTT.
     */
    public function scopeByStatusPKWTT($query, string $status)
    {
        return $query->where('status_pkwtt', $status);
    }

    /**
     * Scope: Filter by gender.
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }

    /**
     * Scope: Search by multiple fields.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nik', 'like', "%{$search}%")
              ->orWhere('no_ktp', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%")
              ->orWhere('department', 'like', "%{$search}%");
        });
    }
}
