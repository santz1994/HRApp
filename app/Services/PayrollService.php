<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

/**
 * PayrollService
 * 
 * Handles payroll calculations, compensation management, and financial projections
 * This service is designed to handle complex business logic for compensation and benefits
 * following the Single Responsibility Principle
 */
class PayrollService
{
    /**
     * Calculate annual compensation and projections for an employee
     *
     * @param Employee $employee
     * @param float $baseSalary (optional - if not set in model)
     * @return array
     */
    public function calculateAnnualCompensation(Employee $employee, ?float $baseSalary = null): array
    {
        $baseSalary = $baseSalary ?? ($employee->base_salary ?? 0);

        if ($baseSalary <= 0) {
            return $this->getEmptyCompensationStructure();
        }

        $masaKerjaTahun = $this->calculateTenureYears($employee->tanggal_masuk);

        return [
            'base_salary' => $baseSalary,
            'thr_bonus' => $this->calculateTHR($baseSalary, $masaKerjaTahun),
            'pemutihan_bonus' => $this->calculatePemutihanBonus($baseSalary, $masaKerjaTahun),
            'projected_next_salary' => $this->calculateProjectedSalary($baseSalary, $masaKerjaTahun),
            'annual_raise_estimate' => $this->calculateAnnualRaise($employee, $baseSalary),
            'total_annual_compensation' => $this->calculateTotalCompensation($baseSalary, $masaKerjaTahun),
            'tenure_years' => $masaKerjaTahun,
            'next_pemutihan_eligible' => $this->getNextPemutihanDate($employee->tanggal_masuk),
        ];
    }

    /**
     * Calculate THR (Tunjangan Hari Raya / Annual Bonus)
     * Standard: 1x monthly salary if tenure >= 1 year
     * Prorated: if tenure < 1 year
     *
     * @param float $baseSalary
     * @param float $masaKerjaTahun
     * @return float
     */
    private function calculateTHR(float $baseSalary, float $masaKerjaTahun): float
    {
        if ($masaKerjaTahun >= 1) {
            return $baseSalary; // Full 1x salary
        }

        // Prorated for less than 1 year
        return $baseSalary * $masaKerjaTahun;
    }

    /**
     * Calculate "Pemutihan" bonus (Dana Pemutihan/Settlement bonus)
     * Triggered every 2 or 3 years: 1.5x monthly salary
     *
     * @param float $baseSalary
     * @param float $masaKerjaTahun
     * @return float
     */
    private function calculatePemutihanBonus(float $baseSalary, float $masaKerjaTahun): float
    {
        if (!$this->isEligibleForPemutihan($masaKerjaTahun)) {
            return 0;
        }

        return $baseSalary * 1.5; // 1.5x monthly salary
    }

    /**
     * Check if employee is eligible for "Pemutihan" (2 or 3 year cycle)
     *
     * @param float $masaKerjaTahun
     * @return bool
     */
    private function isEligibleForPemutihan(float $masaKerjaTahun): bool
    {
        if ($masaKerjaTahun < 2) {
            return false;
        }

        // Check if tenure matches 2-year or 3-year cycle
        $tenureInt = (int)$masaKerjaTahun;
        return ($tenureInt % 2 === 0) || ($tenureInt % 3 === 0);
    }

    /**
     * Get the next date when employee becomes eligible for Pemutihan
     *
     * @param Carbon|string $startDate
     * @return Carbon|null
     */
    private function getNextPemutihanDate($startDate): ?Carbon
    {
        $startDate = Carbon::parse($startDate);
        $currentTenure = $this->calculateTenureYears($startDate);

        if ($currentTenure < 2) {
            return $startDate->addYears(2); // First eligibility at 2 years
        }

        // Find next cycle (2 or 3 years from now)
        $nextTwoYear = $startDate->addYears((int)($currentTenure / 2 + 1) * 2);
        $nextThreeYear = $startDate->addYears((int)($currentTenure / 3 + 1) * 3);

        return $nextTwoYear->isBefore($nextThreeYear) ? $nextTwoYear : $nextThreeYear;
    }

    /**
     * Calculate projected salary with annual raises
     *
     * @param float $baseSalary
     * @param float $masaKerjaTahun
     * @return float
     */
    private function calculateProjectedSalary(float $baseSalary, float $masaKerjaTahun): float
    {
        $annualRaise = $baseSalary * 0.05; // 5% annual raise (configurable)
        $yearsOfRaises = (int)$masaKerjaTahun;

        return $baseSalary + ($annualRaise * $yearsOfRaises);
    }

    /**
     * Calculate annual raise amount (default 5%)
     *
     * @param Employee $employee
     * @param float $baseSalary
     * @return float
     */
    private function calculateAnnualRaise(Employee $employee, float $baseSalary): float
    {
        // Base raise percentage: 5% per year
        $baseRaisePercentage = 0.05;

        // Can be enhanced based on KPI, department, performance ratings, etc.
        // For now, return simple calculation
        return $baseSalary * $baseRaisePercentage;
    }

    /**
     * Calculate total annual compensation
     *
     * @param float $baseSalary
     * @param float $masaKerjaTahun
     * @return float
     */
    private function calculateTotalCompensation(float $baseSalary, float $masaKerjaTahun): float
    {
        $monthlyTotal = $baseSalary * 12;
        $thrBonus = $this->calculateTHR($baseSalary, $masaKerjaTahun);
        $pemutihanBonus = $this->calculatePemutihanBonus($baseSalary, $masaKerjaTahun);

        return $monthlyTotal + $thrBonus + $pemutihanBonus;
    }

    /**
     * Calculate tenure in years as decimal
     *
     * @param Carbon|string $startDate
     * @return float
     */
    private function calculateTenureYears($startDate): float
    {
        $start = Carbon::parse($startDate);
        $now = Carbon::now();

        return $now->diffInMonths($start) / 12; // Return as decimal
    }

    /**
     * Get empty compensation structure
     *
     * @return array
     */
    private function getEmptyCompensationStructure(): array
    {
        return [
            'base_salary' => 0,
            'thr_bonus' => 0,
            'pemutihan_bonus' => 0,
            'projected_next_salary' => 0,
            'annual_raise_estimate' => 0,
            'total_annual_compensation' => 0,
            'tenure_years' => 0,
            'next_pemutihan_eligible' => null,
        ];
    }

    /**
     * Generate payroll summary for a specific month
     *
     * @param Employee $employee
     * @param int $year
     * @param int $month
     * @param float $baseSalary
     * @return array
     */
    public function generateMonthlySummary(Employee $employee, int $year, int $month, float $baseSalary): array
    {
        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->nama,
            'year' => $year,
            'month' => $month,
            'base_salary' => $baseSalary,
            'gross_salary' => $baseSalary,
            'deductions' => 0, // To be calculated based on taxes, insurance, etc.
            'net_salary' => $baseSalary,
            'generated_at' => now(),
        ];
    }
}
