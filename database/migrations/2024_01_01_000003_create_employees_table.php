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
            $table->id();
            
            // Personal Identifiers
            $table->string('nik')->unique()->index();
            $table->string('no_ktp')->unique()->index();
            $table->string('nama')->index();
            
            // Organization Information
            $table->string('department')->index();
            $table->string('jabatan');
            $table->string('dept_on_line')->nullable();
            $table->string('dept_on_line_awal')->nullable();
            
            // Personal Information
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('status_keluarga')->nullable();
            $table->string('pendidikan')->nullable();
            $table->text('alamat')->nullable();
            
            // Employment Information
            $table->date('tanggal_masuk')->index();
            $table->enum('status_pkwtt', ['TETAP', 'KONTRAK'])->default('KONTRAK')->index();
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['department', 'status_pkwtt']);
            $table->index(['jenis_kelamin']);
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
