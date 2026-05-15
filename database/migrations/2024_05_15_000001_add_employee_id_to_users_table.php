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
        Schema::table('users', function (Blueprint $table) {
            // Add employee_id for linking to employee (optional)
            $table->foreignId('employee_id')->nullable()->after('role_id')->constrained('employees')->onDelete('set null');
            $table->string('nik')->nullable()->unique()->after('employee_id')->comment('NIK Karyawan untuk login alternatif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['employee_id']);
            $table->dropColumn(['employee_id', 'nik']);
        });
    }
};
