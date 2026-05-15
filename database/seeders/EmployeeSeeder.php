<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'nik' => '12345678901234567890',
                'no_ktp' => '3171234567890123',
                'nama' => 'John Doe',
                'department' => 'Finance',
                'jabatan' => 'Manager',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => Carbon::create(1985, 5, 15),
                'tanggal_masuk' => Carbon::create(2015, 3, 1),
                'jenis_kelamin' => 'L',
                'dept_on_line' => 'Finance',
                'dept_on_line_awal' => 'Finance',
                'status_pkwtt' => 'TETAP',
                'status_keluarga' => 'Kawin',
                'jumlah_anak' => 2,
                'pendidikan' => 'S1',
                'alamat_ktp' => 'Jalan Sudirman No. 123, Jakarta',
                'alamat_domisili' => 'Jalan Sudirman No. 123, Jakarta',
            ],
            [
                'nik' => '12345678901234567891',
                'no_ktp' => '3171234567890124',
                'nama' => 'Jane Smith',
                'department' => 'Human Resources',
                'jabatan' => 'HR Manager',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => Carbon::create(1988, 8, 22),
                'tanggal_masuk' => Carbon::create(2016, 6, 15),
                'jenis_kelamin' => 'P',
                'dept_on_line' => 'Human Resources',
                'dept_on_line_awal' => 'Human Resources',
                'status_pkwtt' => 'TETAP',
                'status_keluarga' => 'Kawin',
                'jumlah_anak' => 1,
                'pendidikan' => 'S1',
                'alamat_ktp' => 'Jalan Gatot Subroto No. 456, Jakarta',
                'alamat_domisili' => 'Jalan Gatot Subroto No. 456, Jakarta',
            ],
            [
                'nik' => '12345678901234567892',
                'no_ktp' => '3171234567890125',
                'nama' => 'Bob Wilson',
                'department' => 'IT',
                'jabatan' => 'Developer',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => Carbon::create(1990, 10, 5),
                'tanggal_masuk' => Carbon::create(2018, 1, 15),
                'jenis_kelamin' => 'L',
                'dept_on_line' => 'IT',
                'dept_on_line_awal' => 'IT',
                'status_pkwtt' => 'TETAP',
                'status_keluarga' => 'Lajang',
                'jumlah_anak' => 0,
                'pendidikan' => 'S1',
                'alamat_ktp' => 'Jalan Ahmad Yani No. 789, Surabaya',
                'alamat_domisili' => 'Jalan Ahmad Yani No. 789, Surabaya',
            ],
            [
                'nik' => '12345678901234567893',
                'no_ktp' => '3171234567890126',
                'nama' => 'Alice Brown',
                'department' => 'Marketing',
                'jabatan' => 'Marketing Specialist',
                'tempat_lahir' => 'Medan',
                'tanggal_lahir' => Carbon::create(1992, 3, 20),
                'tanggal_masuk' => Carbon::create(2019, 7, 1),
                'jenis_kelamin' => 'P',
                'dept_on_line' => 'Marketing',
                'dept_on_line_awal' => 'Marketing',
                'status_pkwtt' => 'KONTRAK',
                'status_keluarga' => 'Kawin',
                'jumlah_anak' => 0,
                'pendidikan' => 'D3',
                'alamat_ktp' => 'Jalan Merdeka No. 456, Medan',
                'alamat_domisili' => 'Jalan Merdeka No. 456, Medan',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
