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
        // Only HR and IT users can update employees
        $user = auth()->user();
        return $user && in_array($user->role->slug, ['hr', 'it']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('id') ?? $this->route('employee');

        return [
            'nik' => 'sometimes|required|string|max:50|unique:employees,nik,' . $employeeId,
            'no_ktp' => 'sometimes|required|string|size:16|unique:employees,no_ktp,' . $employeeId,
            'nama' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:100',
            'jabatan' => 'sometimes|required|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'sometimes|required|date|before:today',
            'tanggal_masuk' => 'sometimes|required|date|before_or_equal:today',
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'dept_on_line' => 'nullable|string',
            'dept_on_line_awal' => 'nullable|string',
            'status_pkwtt' => 'sometimes|required|in:TETAP,KONTRAK,HARIAN,MAGANG',
            'status_keluarga' => 'sometimes|required|in:Lajang,Kawin,Cerai Hidup,Cerai Mati',
            'jumlah_anak' => 'sometimes|integer|min:0|max:10',
            'pendidikan' => 'nullable|string',
            'alamat_ktp' => 'nullable|string',
            'alamat_domisili' => 'nullable|string',
            'dokumen_pendukung' => 'nullable|json',
            'data_kepribadian' => 'nullable|json',
            'ai_metrics' => 'nullable|json',
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
