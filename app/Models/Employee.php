<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // WAJIB DITAMBAHKAN
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'tanggal_masuk' => 'date', // PERBAIKAN: Sesuaikan dengan nama kolom migration
        'tanggal_lahir' => 'date',
        'dokumen_pendukung' => 'array',
        'data_kepribadian' => 'array',
        'ai_metrics' => 'array',
    ];

    // PERBAIKAN: Sesuaikan dengan nama function Accessor Anda
    protected $appends = ['age', 'age_on_joining', 'tenure_years', 'tenure_formatted'];

    protected $fillable = [
        'nik', 'no_ktp', 'nama', 'department', 'jabatan', 
        'tempat_lahir', 'tanggal_masuk', 'tanggal_lahir', 
        'jenis_kelamin', 'dept_on_line', 'dept_on_line_awal', 
        'status_pkwtt', 'status_keluarga', 'jumlah_anak', 
        'status_pajak', 'pendidikan', 'alamat_ktp', 'alamat_domisili',
        'dokumen_pendukung', 'data_kepribadian', 'ai_metrics'
    ];

    public function getAgeAttribute(): ?int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->diffInYears(Carbon::now()) : null;
    }

    public function getAgeOnJoiningAttribute(): ?int
    {
        return ($this->tanggal_lahir && $this->tanggal_masuk) 
            ? $this->tanggal_lahir->diffInYears($this->tanggal_masuk) 
            : null;
    }

    public function getTenureYearsAttribute(): ?float
    {
        return $this->tanggal_masuk ? $this->tanggal_masuk->diffInDays(Carbon::now()) / 365.25 : null;
    }

    public function getTenureFormattedAttribute(): ?string
    {
        if (!$this->tanggal_masuk) return null;
        
        $diff = $this->tanggal_masuk->diff(Carbon::now());
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' Tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' Bulan';
        
        return implode(', ', $parts) ?: '0 Hari';
    }

    // QUERY SCOPES
    public function scopeSearch($query, string $search)
    {
        return $query->where('nik', 'like', "%{$search}%")
                     ->orWhere('no_ktp', 'like', "%{$search}%")
                     ->orWhere('nama', 'like', "%{$search}%");
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByStatusPKWTT($query, string $status)
    {
        return $query->where('status_pkwtt', $status);
    }

    public function scopeByGender($query, string $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }

    // RELASI
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function medicalLeaves(): HasMany
    {
        return $this->hasMany(MedicalLeave::class, 'employee_id');
    }

    // EVENT BOOTED (Otomatisasi Status Pajak)
    protected static function booted()
    {
        static::saving(function ($employee) {
            $prefix = ($employee->status_keluarga === 'Kawin') ? 'K' : 'TK';
            $anak = min((int)$employee->jumlah_anak, 3);
            $employee->status_pajak = "{$prefix}/{$anak}";
        });
    }
}