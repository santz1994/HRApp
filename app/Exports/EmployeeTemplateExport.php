<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeTemplateExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
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
            'Tanggal Lahir (YYYY-MM-DD)',
            'Tanggal Masuk (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)',
            'Dept On Line',
            'Dept On Line Awal',
            'Status PKWTT (TETAP/KONTRAK/HARIAN/MAGANG)',
            'Status Keluarga (Lajang/Kawin/Cerai Hidup/Cerai Mati)',
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
