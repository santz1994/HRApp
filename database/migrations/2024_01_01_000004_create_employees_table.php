<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Personal Identifiers
            $table->string('nik_karyawan')->unique()->index();
            $table->string('no_ktp')->unique()->index();
            $table->string('nama_lengkap');
            
            // Personal Information
            $table->text('alamat_ktp')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_masuk_kerja');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            
            // Organization Information (Foreign Keys)
            $table->foreignId('department_id')->constrained();
            $table->foreignId('position_id')->constrained();
            $table->foreignId('initial_department_id')->nullable()->constrained('departments');
            $table->foreignId('current_department_id')->nullable()->constrained('departments');
            
            // Employment Information
            $table->enum('status_pkwtt', ['TETAP', 'KONTRAK', 'HARIAN', 'MAGANG'])->default('KONTRAK');
            $table->enum('status_keluarga', ['Lajang', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->default('Lajang');
            $table->integer('jumlah_anak')->default(0);
            $table->string('status_pajak', 5)->nullable();
            $table->string('pendidikan')->nullable();
            $table->text('alamat_domisili')->nullable();
            
            // Documents (JSON)
            $table->json('dokumen_pendukung')->nullable();
            
            // AI Fields
            $table->json('data_kepribadian')->nullable();
            $table->json('ai_metrics')->nullable();
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['department_id', 'status_pkwtt']);
            $table->index(['jenis_kelamin']);
            $table->index(['status_pajak']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};