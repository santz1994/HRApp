<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // 1. No
            
            // Personal Identifiers
            $table->string('nik')->unique()->index(); // 2. NIK
            $table->string('no_ktp')->unique()->index(); // 3. No KTP
            $table->string('nama')->index(); // 4. Nama Lengkap
            
            // Organization Information
            $table->string('department')->index(); // 9. Departement
            $table->string('jabatan'); // 10. Jabatan
            $table->string('dept_on_line_awal')->nullable(); // 15. Dept On Line Awal
            $table->string('dept_on_line')->nullable(); // 16. Dept On Line Saat ini
            
            // Personal Information
            $table->string('tempat_lahir')->nullable(); // 6. Tempat Lahir
            $table->date('tanggal_lahir')->nullable(); // 7. Tanggal Lahir
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable(); // 14. Jenis Kelamin
            
            // Perubahan: Detail Keluarga & Pajak
            $table->enum('status_keluarga', ['Lajang', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->default('Lajang');
            $table->integer('jumlah_anak')->default(0);
            $table->string('status_pajak', 5)->nullable(); // Akan diisi otomatis oleh Model/Service
            
            $table->string('pendidikan')->nullable(); // 21. Pendidikan
            
            // Perubahan: Pemisahan Alamat
            $table->text('alamat_ktp')->nullable(); // 5. Alamat Sesuai KTP (Ubah dari 'alamat')
            $table->text('alamat_domisili')->nullable(); // 22. Alamat Domisili (Baru)
            
            // Employment Information
            $table->date('tanggal_masuk')->index(); // 8. Tanggal Masuk Kerja
            $table->enum('status_pkwtt', ['TETAP', 'KONTRAK', 'HARIAN', 'MAGANG'])->default('KONTRAK')->index(); // 17. Status (Diperluas)
            
            // Perubahan: Dokumen & Persiapan AI (Poin 23 & 26)
            $table->json('dokumen_pendukung')->nullable(); // Contoh struktur yang akan masuk: {"foto_ktp": "path.jpg", "foto_kk": "path.jpg", "foto_ijazah": "path.jpg", "foto_selfie": "path.jpg"}
            $table->json('data_kepribadian')->nullable(); // Menyimpan hasil tes MBTI/DISC
            $table->json('ai_metrics')->nullable(); // Menyimpan analitik AI
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['department', 'status_pkwtt']);
            $table->index(['jenis_kelamin']);
            $table->index(['status_pajak']); // Mempercepat query payroll
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
