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
        // Pastikan hanya role HR dan IT yang bisa menambah data (Sesuai Blueprint RBAC)
        $userRole = strtolower(auth()->user()->role ?? '');
        return in_array($userRole, ['hr', 'it developer & administrator']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nik' => 'required|string|max:50|unique:employees,nik',
            'no_ktp' => 'required|string|size:16|unique:employees,no_ktp',
            'nama' => 'required|string|max:150',
            'department' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_masuk' => 'required|date',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'dept_on_line_awal' => 'nullable|string',
            'dept_on_line' => 'nullable|string',
            'status_pkwtt' => 'required|in:TETAP,KONTRAK,HARIAN,MAGANG',
            'status_keluarga' => 'required|in:Lajang,Kawin,Cerai Hidup,Cerai Mati',
            'jumlah_anak' => 'integer|min:0',
            'pendidikan' => 'nullable|string',
            'alamat_ktp' => 'nullable|string',
            'alamat_domisili' => 'nullable|string',
            // status_pajak tidak divalidasi karena di-generate otomatis oleh Model
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
