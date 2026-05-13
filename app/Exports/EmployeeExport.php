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
            'NIK',
            'No. KTP',
            'Nama',
            'Department',
            'Jabatan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Tanggal Masuk',
            'Jenis Kelamin',
            'Umur Sekarang',
            'Umur Saat Masuk',
            'Masa Kerja',
            'Status PKWTT',
            'Status Keluarga',
            'Pendidikan',
            'Alamat',
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
