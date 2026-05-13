<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToArray, WithHeadingRow
{
    protected $employees = [];

    public function array(array $array)
    {
        $this->employees = $array;
    }

    public function getEmployees(): array
    {
        return $this->employees;
    }
}
