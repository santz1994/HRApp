<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_masuk_kerja' => 'date',
        'tanggal_lahir' => 'date',
        'dokumen_pendukung' => 'array',
        'data_kepribadian' => 'array',
        'ai_metrics' => 'array',
    ];

    protected $appends = ['usia_saat_ini', 'usia_masuk_bekerja', 'masa_kerja', 'masa_kerja_formatted'];

    protected $fillable = [
        'nik_karyawan',
        'no_ktp',
        'nama_lengkap',
        'alamat_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_masuk_kerja',
        'jenis_kelamin',
        'department_id',
        'position_id',
        'initial_department_id',
        'current_department_id',
        'status_pkwtt',
        'status_keluarga',
        'jumlah_anak',
        'status_pajak',
        'pendidikan',
        'alamat_domisili',
        'dokumen_pendukung',
        'data_kepribadian',
        'ai_metrics',
    ];

    // ============================================================
    // ACCESSORS (Virtual Attributes sesuai Project.md Poin 11-13)
    // ============================================================

    /**
     * Usia saat ini (selisih tanggal_lahir dan Current Date)
     */
    public function getUsiaSaatIniAttribute(): ?int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->diffInYears(Carbon::now()) : null;
    }

    /**
     * Usia saat masuk bekerja (selisih tanggal_masuk_kerja dan tanggal_lahir)
     */
    public function getUsiaMasukBekerjaAttribute(): ?int
    {
        return ($this->tanggal_lahir && $this->tanggal_masuk_kerja)
            ? $this->tanggal_lahir->diffInYears($this->tanggal_masuk_kerja)
            : null;
    }

    /**
     * Masa kerja dalam tahun (desimal)
     */
    public function getMasaKerjaAttribute(): ?float
    {
        return $this->tanggal_masuk_kerja
            ? $this->tanggal_masuk_kerja->diffInDays(Carbon::now()) / 365.25
            : null;
    }

    /**
     * Masa kerja formatted (contoh: "3 Tahun, 5 Bulan")
     */
    public function getMasaKerjaFormattedAttribute(): ?string
    {
        if (!$this->tanggal_masuk_kerja) return null;

        $diff = $this->tanggal_masuk_kerja->diff(Carbon::now());
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' Tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' Bulan';

        return implode(', ', $parts) ?: '0 Hari';
    }

    // ============================================================
    // RELASI (sesuai Project.md Skema Relasional)
    // ============================================================

    /**
     * Department utama karyawan
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Position/Jabatan karyawan
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Department awal (initial)
     */
    public function initialDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'initial_department_id');
    }

    /**
     * Department saat ini (current)
     */
    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    /**
     * Relasi ke absensi
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    /**
     * Relasi ke medical records
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'employee_id');
    }

    // ============================================================
    // QUERY SCOPES
    // ============================================================

    public function scopeSearch($query, string $search)
    {
        return $query->where('nik_karyawan', 'like', "%{$search}%")
                     ->orWhere('no_ktp', 'like', "%{$search}%")
                     ->orWhere('nama_lengkap', 'like', "%{$search}%");
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByStatusPKWTT($query, string $status)
    {
        return $query->where('status_pkwtt', $status);
    }

    public function scopeByGender($query, string $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }

    // ============================================================
    // EVENT BOOTED (Otomatisasi Status Pajak sesuai Project.md)
    // ============================================================

    protected static function booted()
    {
        static::saving(function ($employee) {
            $prefix = ($employee->status_keluarga === 'Kawin') ? 'K' : 'TK';
            $anak = min((int) ($employee->jumlah_anak ?? 0), 3);
            $employee->status_pajak = "{$prefix}/{$anak}";
        });
    }
}