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
        $user = auth()->user();
        return $user && in_array($user->role?->slug, ['hr', 'it']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nik_karyawan' => 'required|string|max:50|unique:employees,nik_karyawan',
            'no_ktp' => 'required|string|size:16|unique:employees,no_ktp',
            'nama_lengkap' => 'required|string|max:150',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'initial_department_id' => 'nullable|exists:departments,id',
            'current_department_id' => 'nullable|exists:departments,id',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_masuk_kerja' => 'required|date',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'status_pkwtt' => 'required|in:TETAP,KONTRAK,HARIAN,MAGANG',
            'status_keluarga' => 'required|in:Lajang,Kawin,Cerai Hidup,Cerai Mati',
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
            'nik_karyawan.required' => 'NIK Karyawan wajib diisi',
            'nik_karyawan.unique' => 'NIK Karyawan sudah terdaftar',
            'no_ktp.required' => 'No. KTP wajib diisi',
            'no_ktp.size' => 'No. KTP harus 16 digit',
            'no_ktp.unique' => 'No. KTP sudah terdaftar',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'department_id.required' => 'Department wajib dipilih',
            'department_id.exists' => 'Department tidak valid',
            'position_id.required' => 'Jabatan wajib dipilih',
            'position_id.exists' => 'Jabatan tidak valid',
            'tanggal_masuk_kerja.required' => 'Tanggal masuk kerja wajib diisi',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan)',
            'status_pkwtt.required' => 'Status PKWTT wajib dipilih',
            'status_pkwtt.in' => 'Status PKWTT harus TETAP, KONTRAK, HARIAN, atau MAGANG',
            'status_keluarga.required' => 'Status keluarga wajib dipilih',
        ];
    }
}