<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role?->slug, ['hr', 'it', 'admin_department']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('id');

        return [
            'nik_karyawan' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('employees', 'nik_karyawan')->ignore($employeeId),
            ],
            'no_ktp' => [
                'sometimes', 'required', 'string', 'size:16',
                Rule::unique('employees', 'no_ktp')->ignore($employeeId),
            ],
            'nama_lengkap' => 'sometimes|required|string|max:150',
            'department_id' => 'sometimes|required|exists:departments,id',
            'position_id' => 'sometimes|required|exists:positions,id',
            'initial_department_id' => 'nullable|exists:departments,id',
            'current_department_id' => 'nullable|exists:departments,id',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_masuk_kerja' => 'sometimes|required|date',
            'tanggal_lahir' => 'sometimes|required|date',
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'status_pkwtt' => 'sometimes|required|in:TETAP,KONTRAK,HARIAN,MAGANG',
            'status_keluarga' => 'sometimes|required|in:Lajang,Kawin,Cerai Hidup,Cerai Mati',
            'jumlah_anak' => 'integer|min:0',
            'pendidikan' => 'nullable|string',
            'alamat_ktp' => 'nullable|string',
            'alamat_domisili' => 'nullable|string',
            'dokumen_pendukung' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nik_karyawan.unique' => 'NIK Karyawan sudah terdaftar',
            'no_ktp.unique' => 'No. KTP sudah terdaftar',
            'department_id.exists' => 'Department tidak valid',
            'position_id.exists' => 'Jabatan tidak valid',
        ];
    }
}