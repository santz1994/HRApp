<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only HR users can update employees
        return auth()->check() && auth()->user()->role === 'HR';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'nik' => 'sometimes|required|string|max:20|unique:employees,nik,' . $employeeId,
            'no_ktp' => 'sometimes|required|string|max:20|unique:employees,no_ktp,' . $employeeId,
            'nama' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:employees,email,' . $employeeId,
            'department' => 'sometimes|required|string|max:100',
            'jabatan' => 'sometimes|required|string|max:100',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'sometimes|required|date|before:today',
            'tanggal_masuk' => 'sometimes|required|date|before_or_equal:today',
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'dept_on_line' => 'nullable|string|max:100',
            'dept_on_line_awal' => 'nullable|string|max:100',
            'status_pkwtt' => 'sometimes|required|in:TETAP,KONTRAK',
            'status_keluarga' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nik.unique' => 'This NIK already exists',
            'nama.required' => 'Employee name is required',
            'tanggal_lahir.before' => 'Birth date must be in the past',
            'tanggal_masuk.before_or_equal' => 'Join date cannot be in the future',
            'status_pkwtt.in' => 'Employment status must be TETAP or KONTRAK',
            'jenis_kelamin.in' => 'Gender must be L (Male) or P (Female)',
        ];
    }
}
