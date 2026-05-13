<?php

namespace App\Enums;

/**
 * Employment Status Enum
 * 
 * Defines valid employment status types for employees
 * Using PHP 8.1+ Enum for type safety and consistency
 */
enum EmploymentStatus: string
{
    case PERMANENT = 'TETAP';
    case CONTRACT = 'KONTRAK';

    /**
     * Get readable label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::PERMANENT => 'Permanent (TETAP)',
            self::CONTRACT => 'Contract (KONTRAK)',
        };
    }

    /**
     * Get all available values for validation
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all cases with labels for dropdown
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }

    /**
     * Check if status is permanent
     */
    public function isPermanent(): bool
    {
        return $this === self::PERMANENT;
    }

    /**
     * Check if status is contract
     */
    public function isContract(): bool
    {
        return $this === self::CONTRACT;
    }
}
