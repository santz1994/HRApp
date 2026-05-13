<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only HR users can create employees
        return auth()->check() && auth()->user()->role === 'HR';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nik' => 'required|string|max:20|unique:employees,nik',
            'no_ktp' => 'required|string|max:20|unique:employees,no_ktp',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'department' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'required|date|before:today',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'dept_on_line' => 'nullable|string|max:100',
            'dept_on_line_awal' => 'nullable|string|max:100',
            'status_pkwtt' => 'required|in:TETAP,KONTRAK',
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
            'nik.required' => 'NIK is required',
            'nik.unique' => 'This NIK already exists',
            'nama.required' => 'Employee name is required',
            'tanggal_lahir.before' => 'Birth date must be in the past',
            'tanggal_masuk.before_or_equal' => 'Join date cannot be in the future',
            'status_pkwtt.in' => 'Employment status must be TETAP or KONTRAK',
            'jenis_kelamin.in' => 'Gender must be L (Male) or P (Female)',
        ];
    }
}
