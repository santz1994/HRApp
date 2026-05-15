<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromArray, WithHeadings, WithStyles
{
    protected $employees;

    public function __construct(array $employees)
    {
        $this->employees = $employees;
    }

    public function array(): array
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'NIK Karyawan',
            'No. KTP',
            'Nama Lengkap',
            'Department',
            'Jabatan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Tanggal Masuk Kerja',
            'Jenis Kelamin',
            'Usia Saat Ini',
            'Usia Masuk Bekerja',
            'Masa Kerja',
            'Status PKWTT',
            'Status Keluarga',
            'Pendidikan',
            'Alamat KTP',
            'Alamat Domisili',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '366092']],
            ],
        ];
    }
}